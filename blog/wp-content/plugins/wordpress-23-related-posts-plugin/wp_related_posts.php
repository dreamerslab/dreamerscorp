<?php
/*
Plugin Name: WordPress Related Posts
Version: 2.2
Plugin URI: http://wordpress.org/extend/plugins/wordpress-23-related-posts-plugin/
Description: Quickly increase your readers' engagement with your posts by adding Related Posts in the footer of your content.
Author: Jure Ham
Author URI: http://wordpress.org/extend/plugins/wordpress-23-related-posts-plugin/
*/

define('WP_RP_VERSION', '2.2');

include_once(dirname(__FILE__) . '/config.php');
include_once(dirname(__FILE__) . '/lib/stemmer.php');

include_once(dirname(__FILE__) . '/admin_notices.php');
include_once(dirname(__FILE__) . '/notifications.php');
include_once(dirname(__FILE__) . '/widget.php');
include_once(dirname(__FILE__) . '/thumbnailer.php');
include_once(dirname(__FILE__) . '/settings.php');
include_once(dirname(__FILE__) . '/recommendations.php');
include_once(dirname(__FILE__) . '/dashboard_widget.php');
include_once(dirname(__FILE__) . '/compatibility.php');

register_activation_hook(__FILE__, 'wp_rp_activate_hook');
register_deactivation_hook(__FILE__, 'wp_rp_deactivate_hook');

add_action('wp_head', 'wp_rp_head_resources');
add_action('wp_before_admin_bar_render', 'wp_rp_extend_adminbar');

function wp_rp_extend_adminbar() {
	global $wp_admin_bar;

	if(!is_super_admin() || !is_admin_bar_showing())
		return;

	$wp_admin_bar->add_menu(array(
		'id' => 'wp_rp_adminbar_menu',
		'title' => __('Related Posts', 'wp_related_posts'),
		'href' => admin_url('admin.php?page=wordpress-related-posts&ref=adminbar')
	));
}

global $wp_rp_output;
$wp_rp_output = array();
function wp_rp_add_related_posts_hook($content) {
	global $wp_rp_output, $post;
	$options = wp_rp_get_options();

	if (($options["on_single_post"] && is_single() && !is_page() && !is_attachment()) || (is_feed() && $options["on_rss"])) {
		if (!isset($wp_rp_output[$post->ID])) {
			$wp_rp_output[$post->ID] = wp_rp_get_related_posts();
		}
		$content = $content . $wp_rp_output[$post->ID];
	}

	return $content;
}
add_filter('the_content', 'wp_rp_add_related_posts_hook', 99);

function wp_rp_append_posts(&$related_posts, $fetch_function_name) {
	$options = wp_rp_get_options();

	$limit = $options['max_related_posts'];

	$len = sizeof($related_posts);
	$num_missing_posts = $limit - $len;
	if ($num_missing_posts > 0) {
		$exclude_ids = array_map(create_function('$p', 'return $p->ID;'), $related_posts);

		$posts = call_user_func($fetch_function_name, $num_missing_posts, $exclude_ids);
		if ($posts) {
			$related_posts = array_merge($related_posts, $posts);
		}
	}
}

function wp_rp_fetch_posts_and_title() {
	$options = wp_rp_get_options();

	$limit = $options['max_related_posts'];
	$title = $options["related_posts_title"];

	$related_posts = array();

	wp_rp_append_posts($related_posts, 'wp_rp_fetch_related_posts_v2');
	wp_rp_append_posts($related_posts, 'wp_rp_fetch_related_posts');
	wp_rp_append_posts($related_posts, 'wp_rp_fetch_random_posts');

	if(function_exists('qtrans_postsFilter')) {
		$related_posts = qtrans_postsFilter($related_posts);
	}

	return array(
		"posts" => $related_posts,
		"title" => $title
	);
}

function wp_rp_generate_related_posts_list_items($related_posts) {
	$options = wp_rp_get_options();
	$output = "";
	$i = 0;

	$santa_position = ($options['enable_themes'] && $options['show_santa_hat']) ? rand(1, count($related_posts)) : -1;

	foreach ($related_posts as $related_post ) {
		$output .= '<li position="' . $i++ . '">';

		$img = wp_rp_get_post_thumbnail_img($related_post);
		if ($img) {

			if ($i === $santa_position) {
				$img .= '<img class="wp_rp_santa_hat" style="position: absolute; right: -15px; top: -18px; width: 37px !important; height: 32px !important; box-shadow: none !important; z-index: 1; border: 0 !important;" src="' . WP_RP_STATIC_BASE_URL . 'img/themes/santa.png">';
			}

			$output .=  '<a href="' . get_permalink($related_post->ID) . '" class="wp_rp_thumbnail">' . $img . '</a>';
		}

		if (!$options["display_thumbnail"] || ($options["display_thumbnail"] && ($options["thumbnail_display_title"] || !$img))) {
			if ($options["display_publish_date"]){
				$dateformat = get_option('date_format');
				$output .= mysql2date($dateformat, $related_post->post_date) . " -- ";
			}

			$output .= '<a href="' . get_permalink($related_post->ID) . '" class="wp_rp_title">' . wptexturize($related_post->post_title) . '</a>';

			if ($options["display_comment_count"]){
				$output .=  " (" . $related_post->comment_count . ")";
			}

			if ($options["display_excerpt"]){
				$excerpt_max_length = $options["excerpt_max_length"];
				if($related_post->post_excerpt){
					$output .= '<br /><small>' . (mb_substr(strip_shortcodes(strip_tags($related_post->post_excerpt)), 0, $excerpt_max_length)) . '...</small>';
				} else {
					$output .= '<br /><small>' . (mb_substr(strip_shortcodes(strip_tags($related_post->post_content)), 0, $excerpt_max_length)) . '...</small>';
				}
			}
		}
		$output .=  '</li>';
	}

	return $output;
}

function wp_rp_should_exclude() {
	global $wpdb, $post;

	$options = wp_rp_get_options();

	if($options['exclude_categories'] === '') { return false; }

	$q = 'SELECT COUNT(tt.term_id) FROM '. $wpdb->term_taxonomy.' tt, ' . $wpdb->term_relationships.' tr WHERE tt.taxonomy = \'category\' AND tt.term_taxonomy_id = tr.term_taxonomy_id AND tr.object_id = '.$post->ID . ' AND tt.term_id IN (' . $options['exclude_categories'] . ')';

	$result = $wpdb->get_col($q);

	$count = (int) $result[0];

	return $count > 0;
}

function wp_rp_ajax_blogger_network_blacklist_callback() {
	if (!current_user_can('delete_users')) {
		die();
	}

	$sourcefeed = (int) $_GET['sourcefeed'];

	$meta = wp_rp_get_meta();

	$blog_id = $meta['blog_id'];
	$auth_key = $meta['auth_key'];
	$req_options = array(
		'timeout' => 5
	);
	$url = WP_RP_CTR_DASHBOARD_URL . "blacklist/?blog_id=$blog_id&auth_key=$auth_key&sfid=$sourcefeed";
	$response = wp_remote_get($url, $req_options);

	if (wp_remote_retrieve_response_code($response) == 200) {
		$body = wp_remote_retrieve_body($response);
		if ($body) {
			$doc = json_decode($body);
			if ($doc && $doc->status === 'ok') {
				header_remove();
				header('Content-Type: text/javascript');
				echo "if(window['_wp_rp_blacklist_callback$sourcefeed']) window._wp_rp_blacklist_callback$sourcefeed();";
			}
		}
	}
	die();
}

add_action('wp_ajax_rp_blogger_network_blacklist', 'wp_rp_ajax_blogger_network_blacklist_callback');

function wp_rp_head_resources() {
	global $post, $wpdb;

	if (wp_rp_should_exclude()) {
		return;
	}

	$meta = wp_rp_get_meta();
	$options = wp_rp_get_options();
	$statistics_enabled = false;
	$remote_recommendations = false;
	$output = '';

	// turn off statistics or recommendations on non-singular posts
	if (is_single() && !is_page() && !is_attachment()) {
		$statistics_enabled = $options['ctr_dashboard_enabled'] && $meta['blog_id'] && $meta['auth_key'];
		$remote_recommendations = $meta['remote_recommendations'] && $statistics_enabled;
	}

	if ($statistics_enabled) {
		$tags = $wpdb->get_col("SELECT label FROM " . $wpdb->prefix . "wp_rp_tags WHERE post_id=$post->ID ORDER BY weight desc;", 0);
		if (!empty($tags)) {
			$post_tags = '[' . implode(', ', array_map(create_function('$v', 'return "\'" . urlencode(substr($v, strpos($v, \'_\') + 1)) . "\'";'), $tags)) . ']';
		} else {
			$post_tags = '[]';
		}

		$output .= "<script type=\"text/javascript\">\n" .
			"\twindow._wp_rp_blog_id = '" . esc_js($meta['blog_id']) . "';\n" .
			"\twindow._wp_rp_ajax_img_src_url = '" . esc_js(WP_RP_CTR_REPORT_URL) . "';\n" .
			"\twindow._wp_rp_post_id = '" . esc_js($post->ID) . "';\n" .
			"\twindow._wp_rp_thumbnails = " . ($options['display_thumbnail'] ? 'true' : 'false') . ";\n" .
			"\twindow._wp_rp_post_title = '" . urlencode($post->post_title) . "';\n" .
			"\twindow._wp_rp_post_tags = {$post_tags};\n" .
			"\twindow._wp_rp_static_base_url = '" . esc_js(WP_RP_STATIC_BASE_URL) . "';\n" .
			"\twindow._wp_rp_promoted_content = " . ($options['promoted_content_enabled'] ? 'true' : 'false') . ";\n" .
			(wp_is_mobile() && $options['show_RP_in_posts'] ? "\twindow._wp_rp_show_rp_in_posts = true;\n" : '') .
			"\twindow._wp_rp_plugin_version = '" . WP_RP_VERSION . "';\n" .
			"\twindow._wp_rp_traffic_exchange = " . ($options['traffic_exchange_enabled'] ? 'true' : 'false') . ";\n" .
			(current_user_can('delete_users') ? "\twindow._wp_rp_admin_ajax_url = '" . admin_url('admin-ajax.php') . "';\n" : '') .
			"</script>\n";
	}

	if ($remote_recommendations) {
		$output .= '<script type="text/javascript" src="' . WP_RP_STATIC_BASE_URL . WP_RP_STATIC_RECOMMENDATIONS_JS_FILE . '?version=' . WP_RP_VERSION . '"></script>' . "\n";
		$output .= '<link rel="stylesheet" href="' . WP_RP_STATIC_BASE_URL . WP_RP_STATIC_RECOMMENDATIONS_CSS_FILE . '?version=' . WP_RP_VERSION . '" />' . "\n";
	}

	if($statistics_enabled) {
		$output .= '<script type="text/javascript" src="' . WP_RP_STATIC_BASE_URL . WP_RP_STATIC_CTR_PAGEVIEW_FILE . '?version=' . WP_RP_VERSION . '" async></script>' . "\n";
	}

	if ($options['enable_themes']) {
		if ($options["display_thumbnail"]) {
			$theme_url = WP_RP_STATIC_BASE_URL . WP_RP_STATIC_THEMES_THUMBS_PATH;
		} else {
			$theme_url = WP_RP_STATIC_BASE_URL . WP_RP_STATIC_THEMES_PATH;
		}

		$output .= '<link rel="stylesheet" href="' . $theme_url . $options['theme_name'] . '?version=' . WP_RP_VERSION . '" />' . "\n";
		if ($options['custom_theme_enabled']) {
			$output .= '<style type="text/css">' . "\n" . $options['theme_custom_css'] . "</style>\n";
		}
	}

	echo $output;
}

function wp_rp_get_related_posts($before_title = '', $after_title = '') {
	if (wp_rp_should_exclude()) {
		return;
	}

	$options = wp_rp_get_options();
	$meta = wp_rp_get_meta();

	$statistics_enabled = $options['ctr_dashboard_enabled'] && $meta['blog_id'] && $meta['auth_key'];
	$remote_recommendations = is_single() && $meta['remote_recommendations'] && $statistics_enabled;

	$output = "";
	$promotional_link = '';

	$posts_and_title = wp_rp_fetch_posts_and_title();

	$related_posts = $posts_and_title['posts'];
	$title = $posts_and_title['title'];

	$css_classes = 'related_post wp_rp';
	if ($options['enable_themes']) {
		$css_classes .= ' ' . str_replace(array('.css', '-'), array('', '_'), esc_attr('wp_rp_' . $options['theme_name']));
	}

	if ($related_posts) {
		$output = wp_rp_generate_related_posts_list_items($related_posts);
		$output = '<ul class="' . $css_classes . '" style="visibility: ' . ($remote_recommendations ? 'hidden' : 'visible') . '">' . $output . '</ul>';
		if($remote_recommendations) {
			$output = $output . '<script type="text/javascript">window._wp_rp_callback_widget_exists && window._wp_rp_callback_widget_exists();</script>';
		}
	}

	if ($title != '') {
		if ($before_title) {
			$output = $before_title . $title . $after_title . $output;
		} else {
			$title_tag = $options["related_posts_title_tag"];
			$output =  '<' . $title_tag . ' class="related_post_title">' . $title . $promotional_link . '</' . $title_tag . '>' . $output;
		}
	}

	return "\n" . $output . "\n";
}
