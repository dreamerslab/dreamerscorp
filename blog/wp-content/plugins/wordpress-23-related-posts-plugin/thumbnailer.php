<?php

function wp_rp_upload_default_thumbnail_file() {
	if (!empty($_FILES['wp_rp_default_thumbnail'])) {
		$file = $_FILES['wp_rp_default_thumbnail'];
		if(isset($file['error']) && $file['error'] === UPLOAD_ERR_NO_FILE) {
			return false;
		}

		$upload = wp_handle_upload($file, array('test_form' => false));
		if(isset($upload['error'])) {
			return new WP_Error('upload_error', $upload['error']);
		} else if(isset($upload['file'])) {
			$upload_dir = wp_upload_dir();

			if (class_exists('WP_Image_Editor')) { // WP 3.5+
				$image = WP_Image_Editor::get_instance($upload['file']);

				$suffix = WP_RP_THUMBNAILS_WIDTH . 'x' . WP_RP_THUMBNAILS_HEIGHT;
				$resized_img_path = $image->generate_filename($suffix, $upload_dir['path'], 'jpg');

				$image->resize(WP_RP_THUMBNAILS_WIDTH, WP_RP_THUMBNAILS_HEIGHT, true);
				$image->save($resized_img_path, 'image/jpeg');

				return $upload_dir['url'] . '/' . urlencode(wp_basename($resized_img_path));
			} else {
				$path = image_resize($upload['file'], WP_RP_THUMBNAILS_WIDTH, WP_RP_THUMBNAILS_HEIGHT, true);
				if (!is_wp_error($path)) {
					return $upload_dir['url'] . '/' . wp_basename($path);
				} else if (array_key_exists('error_getting_dimensions', $path->errors)) {
					return $upload['url'];
				}
				return $path;
			}
		}
	}
	return false;
}

function wp_rp_get_default_thumbnail_url($seed = false) {
	$options = wp_rp_get_options();
	$upload_dir = wp_upload_dir();

	if ($options['default_thumbnail_path']) {
		return $options['default_thumbnail_path'];
	} else {
		if ($seed) {
			$next_seed = rand();
			srand($seed);
		}
		$file = rand(0, WP_RP_THUMBNAILS_DEFAULTS_COUNT - 1) . '.jpg';
		if ($seed) {
			srand($next_seed);
		}
		return plugins_url('/static/thumbs/' . $file, __FILE__);
	}
}

function wp_rp_extract_post_image($post_id) {
	// We don't have an image stored for this post yet - find the first uploaded image and save it
	$args = array(
			'post_type' => 'attachment',
			'numberposts' => 1,
			'post_status' => null,
			'post_parent' => $post_id,
			'orderby' => 'id',
			'order' => 'ASC',
		);

	$attachments = get_posts($args);
	$image_id = '-1';
	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$img = wp_get_attachment_image($attachment->ID, 'thumbnail');
			if($img) {
				$image_id = $attachment->ID;
				break;
			}
		}
	}
	return $image_id;
}

function wp_rp_direct_filesystem_method() {
	return 'direct';
}

function wp_rp_actually_extract_images_from_post_html($post) {
	$content = $post->post_content;
	preg_match_all('/<img (?:[^>]+ )?src="([^"]+)"/', $content, $matches);
	$urls = $matches[1];

	$img_url = false;

	if(count($urls) == 0) {
		return $img_url;
	}
	array_splice($urls, 10);

	$upload_dir = wp_upload_dir();
	if($upload_dir['error'] !== false) {
		return $img_url;
	}
	require_once(ABSPATH . 'wp-admin/includes/file.php');

	global $wp_filesystem;
	add_filter('filesystem_method', 'wp_rp_direct_filesystem_method');
	WP_Filesystem();

	foreach ($urls as $url) {
		$url = html_entity_decode($url);

		$http_response = wp_remote_get($url, array('timeout' => 10));
		if(is_wp_error($http_response)) {
			continue;
		}
		$img_data = wp_remote_retrieve_body($http_response);

		$img_name = wp_unique_filename($upload_dir['path'], wp_basename(parse_url($url, PHP_URL_PATH)));
		$img_path = $upload_dir['path'] . '/' . $img_name;

		if(!$wp_filesystem->put_contents($img_path, $img_data, FS_CHMOD_FILE)) {
			continue;
		}

		if (class_exists('WP_Image_Editor')) { // WP 3.5+
			$image = WP_Image_Editor::get_instance($img_path);

			$suffix = WP_RP_THUMBNAILS_WIDTH . 'x' . WP_RP_THUMBNAILS_HEIGHT;
			$resized_img_path = $image->generate_filename($suffix, $upload_dir['path'], 'jpg');

			$image->resize(WP_RP_THUMBNAILS_WIDTH, WP_RP_THUMBNAILS_HEIGHT, true);
			$image->save($resized_img_path, 'image/jpeg');
		} else {
			$resized_img_path = image_resize($img_path, WP_RP_THUMBNAILS_WIDTH, WP_RP_THUMBNAILS_HEIGHT, true);
			if (is_wp_error($resized_img_path) && array_key_exists('error_getting_dimensions', $resized_img_path->errors)) {
				$resized_img_path = $img_path;
			}
		}

		if(is_wp_error($resized_img_path)) {
			continue;
		}

		$img_url = $upload_dir['url'] . '/' . urlencode(wp_basename($resized_img_path));

		break;
	}

	remove_filter('filesystem_method', 'wp_rp_direct_filesystem_method');

	return $img_url;
}

function wp_rp_cron_do_extract_images_from_post_html($post_id) {
	$post_id = (int) $post_id;
	$post = get_post($post_id);

	$img_url = wp_rp_actually_extract_images_from_post_html($post);

	if($img_url) {
		update_post_meta($post_id, '_wp_rp_extracted_image_url', $img_url);
	}
}
add_action('wp_rp_cron_extract_images_from_post_html', 'wp_rp_cron_do_extract_images_from_post_html');

function wp_rp_extract_images_from_post_html($post) {
	update_post_meta($post->ID, '_wp_rp_extracted_image_url', '');
	if(empty($post->post_content)) { return; }

	wp_schedule_single_event(time(), 'wp_rp_cron_extract_images_from_post_html', array($post->ID));
}

function wp_rp_post_save_update_image($post_id) {
	$post = get_post($post_id);

	if(empty($post->post_content) || $post->post_status !== 'publish' || $post->post_type === 'page'  || $post->post_type === 'attachment' || $post->post_type === 'nav_menu_item') {
		return;
	}

	delete_post_meta($post->ID, '_wp_rp_extracted_image_url');

	wp_rp_get_post_thumbnail_img($post);
}
add_action('save_post', 'wp_rp_post_save_update_image');


function wp_rp_get_post_thumbnail_img($related_post) {
	$options = wp_rp_get_options();
	if (!$options["display_thumbnail"]) {
		return false;
	}

	if ($options['thumbnail_use_custom']) {
		$thumbnail_src = get_post_meta($related_post->ID, $options["thumbnail_custom_field"], true);

		if ($thumbnail_src) {
			$img = '<img src="' . esc_attr($thumbnail_src) . '" alt="' . esc_attr(wptexturize($related_post->post_title)) . '" />';
			return $img;
		}
	} else if (has_post_thumbnail($related_post->ID)) {
		$attr = array(
			'alt' => esc_attr(wptexturize($related_post->post_title)),
			'title' => false
		);
		$img = get_the_post_thumbnail($related_post->ID, 'thumbnail', $attr);
		return $img;
	}

	if($options["thumbnail_use_attached"]) {
		$image_url = get_post_meta($related_post->ID, '_wp_rp_extracted_image_url', false);

		if(!empty($image_url) && ($image_url[0] != '')) {
			$img = '<img src="' . esc_attr($image_url[0]) . '" alt="' . esc_attr(wptexturize($related_post->post_title)) . '" />';
			return $img;
		}

		$image_id = wp_rp_extract_post_image($related_post->ID);
		if ($image_id !== '-1') {
			$img = wp_get_attachment_image($image_id, 'thumbnail');
			return $img;
		}

		if(empty($image_url)) {
			wp_rp_extract_images_from_post_html($related_post);
		}
	}

	$img = '<img src="'. esc_attr(wp_rp_get_default_thumbnail_url($related_post->ID)) . '" alt="' . esc_attr(wptexturize($related_post->post_title)) . '" />';
	return $img;
}

function wp_rp_process_latest_post_thumbnails() {
	$latest_posts = get_posts(array('numberposts' => WP_RP_THUMBNAILS_NUM_PREGENERATED_POSTS));
	foreach ($latest_posts as $post) {
		wp_rp_get_post_thumbnail_img($post);
	}
}
