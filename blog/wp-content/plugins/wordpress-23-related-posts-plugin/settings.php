<?php

/**
* Tooltips
**/

function wp_rp_display_tooltips() {
	$meta = wp_rp_get_meta();

	if ($meta['show_upgrade_tooltip']) {
		$meta['show_upgrade_tooltip'] = false;
		wp_rp_update_meta($meta);

		add_action('admin_enqueue_scripts', 'wp_rp_load_upgrade_tooltip');
	} else if ($meta['show_install_tooltip']) {
		$meta['show_install_tooltip'] = false;
		wp_rp_update_meta($meta);

		add_action('admin_enqueue_scripts', 'wp_rp_load_install_tooltip');
	}
}
function wp_rp_load_upgrade_tooltip() {
	if (version_compare(get_bloginfo('version'), '3.3', '<')) {
		return;
	}

    wp_enqueue_style('wp-pointer');
    wp_enqueue_script('wp-pointer');
    add_action('admin_print_footer_scripts', 'wp_rp_print_upgrade_tooltip');
}
function wp_rp_print_upgrade_tooltip() {
	$content = "<h3>Thanks for updating Related Posts plugin!</h3><p>We've added some new stuff to the Settings, go check them out. Let us know what you think.</p>";
	wp_rp_print_tooltip($content);
}

function wp_rp_load_install_tooltip() {
	if (version_compare(get_bloginfo('version'), '3.3', '<')) {
		return;
	}

    wp_enqueue_style('wp-pointer');
    wp_enqueue_script('wp-pointer');
    add_action('admin_print_footer_scripts', 'wp_rp_print_install_tooltip');
}
function wp_rp_print_install_tooltip() {
	$content = "<h3>Thanks for installing Related Posts plugin!</h3><p>To experience the full power of Related Posts, go to settings and turn on advanced features!</p>";
	wp_rp_print_tooltip($content);
}

function wp_rp_print_tooltip($content) {
	?>
	<script type="text/javascript">
		jQuery(function ($) {
			var body = $(document.body),
				collapse = $('#collapse-menu'),
				target = $("#toplevel_page_wordpress-related-posts"),
				collapse_handler = function (e) {
					body.pointer('reposition');
				},
				options = {
					content: "<?php echo $content; ?>",
					position: {
						edge: 'left',
						align: 'center',
						of: target
					},
					open: function () {
						collapse.bind('click', collapse_handler);
					},
					close: function() {
						collapse.unbind('click', collapse_handler);
					}
				};

			if (target.length) {
				body.pointer(options).pointer('open');
			}
		});
	</script>
	<?php
}

/**
* Place menu icons at admin head
**/
add_action('admin_head', 'wp_rp_admin_head');
function wp_rp_admin_head() {
	$menu_icon = plugins_url('static/img/menu_icon.png', __FILE__);
	$menu_icon_retina = plugins_url('static/img/menu_icon_2x.png', __FILE__);
?>
<style type="text/css">
#toplevel_page_wordpress-related-posts .wp-menu-image {
	background: url('<?php echo $menu_icon; ?>') 7px 6px no-repeat;
}
@media only screen and (-webkit-min-device-pixel-ratio: 1.5) {
	#toplevel_page_wordpress-related-posts .wp-menu-image {
		background-image: url('<?php echo $menu_icon_retina; ?>');
		background-size: 16px 16px;
	}
}
</style>
<?php
}

/**
* Settings
**/

add_action('admin_menu', 'wp_rp_settings_admin_menu');

function wp_rp_settings_admin_menu() {
	if (!current_user_can('delete_users')) {
		return;
	}

	$title = __('Related Posts', 'wp_related_posts');
	$count = wp_rp_number_of_available_notifications();

	if($count) {
		$title .= ' <span class="update-plugins count-' . $count . '"><span class="plugin-count">' . $count . '</span></span>';
	}
	
	$page = add_menu_page(__('Related Posts', 'wp_related_posts'), $title, 
						'manage_options', 'wordpress-related-posts', 'wp_rp_settings_page', 'div');

	add_action('admin_print_styles-' . $page, 'wp_rp_settings_styles');
	add_action('admin_print_scripts-' . $page, 'wp_rp_settings_scripts');

	wp_rp_display_tooltips();
}

function wp_rp_settings_scripts() {
	wp_enqueue_script('wp_rp_themes_script', plugins_url('static/js/themes.js', __FILE__), array('jquery'));
	wp_enqueue_script("wp_rp_dashboard_script", plugins_url('static/js/dashboard.js', __FILE__), array('jquery') );
}
function wp_rp_settings_styles() {
	wp_enqueue_style("wp_rp_dashaboard_style", plugins_url("static/css/dashboard.css", __FILE__));
}

function wp_rp_register_blog() {
	$meta = wp_rp_get_meta();

	$req_options = array(
		'timeout' => 30
	);

	$response = wp_remote_get(WP_RP_CTR_DASHBOARD_URL . 'register/?blog_url=' . get_bloginfo('wpurl') .
			($meta['new_user'] ? '&new' : '') .
			($meta['turn_on_button_pressed'] ? ('&turn_on=' . $meta['turn_on_button_pressed']) : ''),
		$req_options);

	if (wp_remote_retrieve_response_code($response) == 200) {
		$body = wp_remote_retrieve_body($response);
		if ($body) {
			$doc = json_decode($body);

			if ($doc && $doc->status === 'ok') {
				$meta['blog_id'] = $doc->data->blog_id;
				$meta['auth_key'] = $doc->data->auth_key;
				$meta['new_user'] = false;
				wp_rp_update_meta($meta);

				return true;
			}
		}
	}

	return false;
}

function wp_rp_ajax_blogger_network_submit_callback() {
	$postdata = stripslashes_deep($_POST);

	$meta = wp_rp_get_meta();

	$meta['show_blogger_network_form'] = false;
	if(isset($postdata['join'])) {
		$meta['remote_recommendations'] = true;
	}
	else {
		$blog_id = $meta['blog_id'];
		$auth_key = $meta['auth_key'];
		$req_options = array(
			'timeout' => 5
		);
		$url = WP_RP_CTR_DASHBOARD_URL . "notifications/dismiss/?blog_id=$blog_id&auth_key=$auth_key&msg_id=blogger_network_form";
		$response = wp_remote_get($url, $req_options);
	}

	wp_rp_update_meta($meta);

	die('ok');
}
add_action('wp_ajax_blogger_network_submit', 'wp_rp_ajax_blogger_network_submit_callback');

function wp_rp_ajax_dismiss_notification_callback() {	
	if(isset($_REQUEST['id'])) {
		wp_rp_dismiss_notification((int)$_REQUEST['id']);
	}
	if(isset($_REQUEST['noredirect'])) {
		die('ok');
	}
	wp_redirect(admin_url('admin.php?page=wordpress-related-posts'));
}

add_action('wp_ajax_rp_dismiss_notification', 'wp_rp_ajax_dismiss_notification_callback');

function wp_rp_ajax_hide_show_statistics() {
	$meta = wp_rp_get_meta();
	$postdata = stripslashes_deep($_POST);

	if(isset($postdata['show'])) {
		$meta['show_statistics'] = true;
	}
	if(isset($postdata['hide'])) {
		$meta['show_statistics'] = false;
	}

	wp_rp_update_meta($meta);

	die('ok');
}

add_action('wp_ajax_rp_show_hide_statistics', 'wp_rp_ajax_hide_show_statistics');

function wp_rp_settings_page() {
	$title_tags = array('h2', 'h3', 'h4', 'p', 'div');

	$options = wp_rp_get_options();
	$meta = wp_rp_get_meta();

	$postdata = stripslashes_deep($_POST);

	// load notifications every time user goes to settings page
	wp_rp_load_remote_notifications();

	if(sizeof($_POST))
	{
		$old_options = $options;
		$new_options = array(
			'on_single_post' => isset($postdata['wp_rp_on_single_post']),
			'display_comment_count' => isset($postdata['wp_rp_display_comment_count']),
			'display_publish_date' => isset($postdata['wp_rp_display_publish_date']),
			'display_excerpt' => isset($postdata['wp_rp_display_excerpt']),
			'excerpt_max_length' => (isset($postdata['wp_rp_excerpt_max_length']) && is_numeric(trim($postdata['wp_rp_excerpt_max_length']))) ? intval(trim($postdata['wp_rp_excerpt_max_length'])) : 200,
			'max_related_posts' => (isset($postdata['wp_rp_max_related_posts']) && is_numeric(trim($postdata['wp_rp_max_related_posts']))) ? intval(trim($postdata['wp_rp_max_related_posts'])) : 5,
			'on_rss' => isset($postdata['wp_rp_on_rss']),
			'display_thumbnail' => isset($postdata['wp_rp_display_thumbnail']),
			'thumbnail_custom_field' => isset($postdata['wp_rp_thumbnail_custom_field']) ? trim($postdata['wp_rp_thumbnail_custom_field']) : false,
			'thumbnail_display_title' => isset($postdata['wp_rp_thumbnail_display_title']),
			'related_posts_title' => isset($postdata['wp_rp_related_posts_title']) ? trim($postdata['wp_rp_related_posts_title']) : '',
			'related_posts_title_tag' => isset($postdata['wp_rp_related_posts_title_tag']) ? $postdata['wp_rp_related_posts_title_tag'] : 'h3',
			'thumbnail_use_attached' => isset($postdata['wp_rp_thumbnail_use_attached']),
			'thumbnail_use_custom' => isset($postdata['wp_rp_thumbnail_use_custom']) && $postdata['wp_rp_thumbnail_use_custom'] === 'yes',
			'ctr_dashboard_enabled' => isset($postdata['wp_rp_ctr_dashboard_enabled']),
			'promoted_content_enabled' => isset($postdata['wp_rp_promoted_content_enabled']),
			'enable_themes' => isset($postdata['wp_rp_enable_themes']),
			'show_RP_in_posts' => isset($postdata['wp_rp_show_RP_in_posts']),
			'custom_theme_enabled' => isset($postdata['wp_rp_custom_theme_enabled']),
			'show_santa_hat' => isset($postdata['wp_rp_show_santa_hat']),
			'traffic_exchange_enabled' => isset($postdata['wp_rp_traffic_exchange_enabled'])
		);

		if(!isset($postdata['wp_rp_exclude_categories'])) {
			$new_options['exclude_categories'] = '';
		} else if(is_array($postdata['wp_rp_exclude_categories'])) {
			$new_options['exclude_categories'] = join(',', $postdata['wp_rp_exclude_categories']);
		} else {
			$new_options['exclude_categories'] = trim($postdata['wp_rp_exclude_categories']);
		}

		$preprocess_thumbnails = $new_options['display_thumbnail'] && $new_options['thumbnail_use_attached'] && (!$old_options['display_thumbnail'] || !$old_options['thumbnail_use_attached']);

		if(isset($postdata['wp_rp_theme_name'])) {		// If this isn't set, maybe the AJAX didn't load...
			$new_options['theme_name'] = trim($postdata['wp_rp_theme_name']);

			if(isset($postdata['wp_rp_theme_custom_css'])) {
				$new_options['theme_custom_css'] = $postdata['wp_rp_theme_custom_css'];
			} else {
				$new_options['theme_custom_css'] = '';
			}
		} else {
			$new_options['theme_name'] = $old_options['theme_name'];
			$new_options['theme_custom_css'] = $old_options['theme_custom_css'];
		}

		if (isset($postdata['wp_rp_turn_on_button_pressed'])) {
			$meta['turn_on_button_pressed'] = $postdata['wp_rp_turn_on_button_pressed'];
		}

		$default_thumbnail_path = wp_rp_upload_default_thumbnail_file();

		if($default_thumbnail_path === false) { // no file uploaded
			if(isset($postdata['wp_rp_default_thumbnail_remove'])) {
				$new_options['default_thumbnail_path'] = false;
			} else {
				$new_options['default_thumbnail_path'] = $old_options['default_thumbnail_path'];
			}
		} else if(is_wp_error($default_thumbnail_path)) { // error while upload
			$new_options['default_thumbnail_path'] = $old_options['default_thumbnail_path'];
			wp_rp_add_admin_notice('error', $default_thumbnail_path->get_error_message());
		} else { // file successfully uploaded
			$new_options['default_thumbnail_path'] = $default_thumbnail_path;
		}

		if (((array) $old_options) != $new_options) {
			if($new_options['ctr_dashboard_enabled'] && !$old_options['ctr_dashboard_enabled']) {
				$meta['show_statistics'] = true;

				if($new_options['display_thumbnail']) {
					$meta['show_turn_on_button'] = false;
				}

				wp_rp_update_meta($meta);
			}

			if(!wp_rp_update_options($new_options)) {
				wp_rp_add_admin_notice('error', __('Failed to save settings.', 'wp_related_posts'));
			} else {
				wp_rp_add_admin_notice('updated', __('Settings saved.', 'wp_related_posts'));
			}

			if($preprocess_thumbnails) {
				wp_rp_process_latest_post_thumbnails();
			}
		} else {
			// I should duplicate success message here
			wp_rp_add_admin_notice('updated', __('Settings saved.', 'wp_related_posts'));
		}
	}

	if($options['ctr_dashboard_enabled'] && (!$meta['blog_id'] || !$meta['auth_key'])) {
		wp_rp_register_blog();
	}

?>

	<div class="wrap" id="wp_rp_wrap">
	<?php
		$related_posts_title_tag = $options['related_posts_title_tag'];
		$theme_name = $options['theme_name'];
		$theme_custom_css = $options['theme_custom_css'];

		include(dirname(__FILE__) . '/static/settings.js.php');
	?>

		<input type="hidden" id="wp_rp_json_url" value="<?php esc_attr_e(WP_RP_STATIC_BASE_URL . WP_RP_STATIC_JSON_PATH); ?>" />
		<input type="hidden" id="wp_rp_version" value="<?php esc_attr_e(WP_RP_VERSION); ?>" />
		<input type="hidden" id="wp_rp_theme_selected" value="<?php esc_attr_e($theme_name); ?>" />
		<input type="hidden" id="wp_rp_dashboard_url" value="<?php esc_attr_e(WP_RP_CTR_DASHBOARD_URL); ?>" />
		<input type="hidden" id="wp_rp_static_base_url" value="<?php esc_attr_e(WP_RP_STATIC_BASE_URL); ?>" />

		<?php if ($options['ctr_dashboard_enabled']):?>
		<input type="hidden" id="wp_rp_blog_id" value="<?php esc_attr_e($meta['blog_id']); ?>" />
		<input type="hidden" id="wp_rp_auth_key" value="<?php esc_attr_e($meta['auth_key']); ?>" />
		<?php endif; ?>

		<?php if($meta['show_traffic_exchange'] && $options['traffic_exchange_enabled']): ?>
		<input type="hidden" id="wp_rp_show_traffic_exchange_statistics" value="1" />
		<?php endif; ?>

		<div class="header">
			<div class="support">
				<h4><?php _e("Awesome support", 'wp_related_posts'); ?></h4>
				<p>
					<?php _e("If you have any questions please contact us at",'wp_related_posts');?> <a target="_blank" href="mailto:relatedpostsplugin@gmail.com"><?php _e("support", 'wp_related_posts');?></a>.
				</p>
			</div>
			<h2 class="title"><?php _e("Related Posts",'wp_related_posts');?></h2>
		</div>
		<div id="wp-rp-survey" class="updated highlight" style="display:none;"><p><?php _e("Please fill out",'wp_related_posts');?> <a class="link" target="_blank" href="http://wprelatedposts.polldaddy.com/s/quick-survey"><?php _e("a quick survey", 'wp_related_posts');?></a>.<a href="#" class="close" style="float: right;">x</a></p></div>

		<?php wp_rp_print_notifications(); ?>

		<?php if($meta['show_turn_on_button']): ?>
		<div id="wp_rp_turn_on_statistics">
			<table cellspacing="0" cellpadding="0"><tbody><tr>
				<td>
					<h2>
						Turn on Advanced Features
					</h2>
					<ul>
						<li>Real-time Analytics*</li>
						<li>Thumbnail Support</li>
						<li>Promoted Content</li>
					</ul>
				</td><td>
					<a class="turn-on" href="#">Turn on</a>
				</td>
			</tr></tbody></table>
		</div>
		<?php endif; ?>

		<?php if ($meta['show_blogger_network_form'] and $meta['blog_id'] and !$meta['show_turn_on_button']): ?>
		<form action="https://docs.google.com/a/zemanta.com/spreadsheet/formResponse?formkey=dDEyTlhraEd0dnRwVVFMX19LRW8wbWc6MQ&amp;ifq" method="POST" class="wp_rp_message_form" id="wp_rp_blogger_network_form" target="wp_rp_blogger_network_hidden_iframe">
			<input type="hidden" name="pageNumber" value="0" />
			<input type="hidden" name="backupCache" />
			<input type="hidden" name="entry.2.single" value="<?php echo get_bloginfo('wpurl'); ?>" />
			<input type="hidden" name="entry.3.single" value="<?php echo $meta['blog_id']; ?>" />
			<a href="#" class="dismiss"><img width="12" src="<?php echo plugins_url("static/img/close.png", __FILE__); ?>" /></a>
			<h2>Blogger networks</h2>
			<p>Easily link out to similar bloggers to exchange traffic with them. One click out, one click in.</p>
			<table class="form-table"><tbody>
				<tr valign="top">
					<th scope="row"><label for="wp_rp_blogger_network_kind">I want to exchange traffic with</label></th>
					<td width="1%">
						<select name="entry.0.group" id="wp_rp_blogger_network_kind">
							<option value="Automotive" />Automotive bloggers</option>
							<option value="Beauty &amp; Style" />Beauty &amp; Style bloggers</option>
							<option value="Business" />Business bloggers</option>
							<option value="Consumer Tech" />Consumer Tech bloggers</option>
							<option value="Enterprise Tech" />Enterprise Tech bloggers</option>
							<option value="Entertainment" />Entertainment bloggers</option>
							<option value="Family &amp; Parenting" />Family &amp; Parenting bloggers</option>
							<option value="Food &amp; Drink" />Food &amp; Drink bloggers</option>
							<option value="Graphic Arts" />Graphic Arts bloggers</option>
							<option value="Healthy Living" />Healthy Living bloggers</option>
							<option value="Home &amp; Shelter" />Home &amp; Shelter bloggers</option>
							<option value="Lifestyle &amp; Hobby" />Lifestyle &amp; Hobby bloggers</option>
							<option value="Men's Lifestyle" />Men's Lifestyle bloggers</option>
							<option value="Personal Finance" />Personal Finance bloggers</option>
							<option value="Women's Lifestyle" />Women's Lifestyle bloggers</option>
						</select>
					</td>
					<td rowspan="2" valign="middle"><div id="wp_rp_blogger_network_thankyou" class="thankyou"><img src="<?php echo plugins_url("static/img/check.png", __FILE__); ?>" width="30" height="22" />Thanks for showing interest.</div></td>
				</tr>
				<tr valign="top">
					<th scope="row"><label for="wp_rp_blogger_network_email">My email is:</label></th>
					<td><input type="email" name="entry.1.single" value="" id="wp_rp_blogger_network_email" required="required" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"></th>
					<td><input type="submit" name="submit" value="Submit" class="submit" id="wp_rp_blogger_network_submit" /></td>
			</tbody></table>
			<script type="text/javascript">
jQuery(function($) {
	var submit = $('#wp_rp_blogger_network_submit');
	$('#wp_rp_blogger_network_form')
		.submit(function(event) {
			submit.addClass('disabled');
			setTimeout(function() { submit.attr('disabled', true); }, 0);
			$('#wp_rp_blogger_network_hidden_iframe').load(function() {
				submit.attr('disabled', false).removeClass('disabled');
				$('#wp_rp_blogger_network_thankyou').fadeIn('slow');
				$.post(ajaxurl, {action: 'blogger_network_submit', 'join': true});
			});
		})
		.find('a.dismiss').click(function () {
			$.post(ajaxurl, {action: 'blogger_network_submit'});
			$('#wp_rp_blogger_network_form').slideUp();
		});
});
			</script>
		</form>
		<iframe id="wp_rp_blogger_network_hidden_iframe" name="wp_rp_blogger_network_hidden_iframe" style="display: none"></iframe>
		<?php endif; ?>

		<form method="post" enctype="multipart/form-data" action="" id="wp_rp_settings_form">
			<?php if ($options['ctr_dashboard_enabled']): ?>
			<div id="wp_rp_statistics_holder">
				<div id="wp_rp_statistics_collapsible" block="statistics" class="collapsible<?php if(!$meta['show_statistics']) { echo " collapsed"; } ?>">
					<a href="#" class="collapse-handle">Collapse</a>
					<h2><?php _e('Statistics', 'wp_related_posts'); ?></h2>
					<div class="container" <?php if(!$meta['show_statistics']) { echo ' style="display: none;" '; } ?>>
						<div id="wp_rp_statistics_wrap">
							<div class="message unavailable"><?php _e("Statistics currently unavailable",'wp_related_posts'); ?></div>
						</div>
					</div>
				</div>
			</div>
			<?php endif; ?>

			<div>
				<h2><?php _e("Settings",'wp_related_posts');?></h2>

				<?php do_action('wp_rp_admin_notices'); ?>

				<div class="container">
					<h3><?php _e("Basic Settings",'wp_related_posts');?></h3>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Related Posts Title:', 'wp_related_posts'); ?></th>
							<td>
							  <input name="wp_rp_related_posts_title" type="text" id="wp_rp_related_posts_title" value="<?php esc_attr_e($options['related_posts_title']); ?>" class="regular-text" />
							</td>
						</tr>
						<tr valign="top">
							<th scope="row"><?php _e('Number of Posts:', 'wp_related_posts');?></th>
							<td>
							  <input name="wp_rp_max_related_posts" type="number" step="1" id="wp_rp_max_related_posts" class="small-text" min="1" value="<?php esc_attr_e($options['max_related_posts']); ?>" />
							</td>
						</tr>
					</table>

					<h3>Theme Settings</h3>
					<table class="form-table">
						<tr id="wp_rp_theme_options_wrap">
							<th scope="row">Select Theme:</th>
							<td>
								<label>
									<input name="wp_rp_enable_themes" type="checkbox" id="wp_rp_enable_themes" value="yes"<?php checked($options["enable_themes"]); ?> />
									<?php _e("Enable Themes",'wp_related_posts'); ?>*
								</label>
								<div id="wp_rp_theme_area" style="display: none;">
									<div class="theme-list"></div>
									<div class="theme-screenshot"></div>
									<div class="theme-extra-options">
										<label>
											<input name="wp_rp_show_santa_hat" type="checkbox" id="wp_rp_show_santa_hat" value="yes" <?php checked($options["show_santa_hat"]); ?>>
											<?php _e("Christmas Surprise",'wp_related_posts');?>
										</label><br />
										<label>
											<input type="checkbox" id="wp_rp_custom_theme_enabled" name="wp_rp_custom_theme_enabled" value="yes"<?php checked($options['custom_theme_enabled']); ?> />
											Customize
										</label>
									</div>
								</div>
							</td>
						</tr>
						<tr id="wp_rp_theme_custom_css_wrap" style="display: none; ">
							<th scope="row"></th>
							<td>
								<p class="notice">To override default rules please add "!important" after every declaration.</p>
								<textarea style="width: 300px; height: 215px;" name="wp_rp_theme_custom_css" class="custom-css"><?php echo htmlspecialchars($theme_custom_css, ENT_QUOTES); ?></textarea>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e("Thumbnail Options:",'wp_related_posts'); ?></th>
							<td>
								<label>
									<input name="wp_rp_display_thumbnail" type="checkbox" id="wp_rp_display_thumbnail" value="yes"<?php checked($options["display_thumbnail"]); ?> onclick="wp_rp_display_thumbnail_onclick();" />
									<?php _e("Display Thumbnails For Related Posts",'wp_related_posts');?>
								</label>
								<br />
								<span id="wp_rp_thumbnail_span" style="<?php echo $options["display_thumbnail"] ? '' : 'display:none;'; ?>">
								<label>
									<input name="wp_rp_thumbnail_display_title" type="checkbox" id="wp_rp_thumbnail_display_title" value="yes"<?php checked($options["thumbnail_display_title"]); ?> />
									<?php _e('Display Post Titles', 'wp_related_posts');?>
								</label>
								<br />

								<?php
								global $wpdb;

								$custom_fields = $wpdb->get_col( "SELECT meta_key FROM $wpdb->postmeta GROUP BY meta_key HAVING meta_key NOT LIKE '\_%' ORDER BY LOWER(meta_key)" );

								if($custom_fields) :
								?>
								<label><input name="wp_rp_thumbnail_use_custom" type="radio" value="no" <?php checked(!$options['thumbnail_use_custom']); ?>> Use featured image</label>&nbsp;&nbsp;&nbsp;&nbsp;
								<label><input name="wp_rp_thumbnail_use_custom" type="radio" value="yes" <?php checked($options['thumbnail_use_custom']); ?>> Use custom field</label>

								<select name="wp_rp_thumbnail_custom_field" id="wp_rp_thumbnail_custom_field"  class="postform">
								
								<?php foreach ( $custom_fields as $custom_field ) : ?>
									<option value="<?php esc_attr_e($custom_field); ?>"<?php selected($options["thumbnail_custom_field"], $custom_field); ?>><?php esc_html_e($custom_field);?></option>
								<?php endforeach; ?>

								</select>
								<br />
								<?php endif; ?>

								<label>
									<input name="wp_rp_thumbnail_use_attached" type="checkbox" value="yes" <?php checked($options["thumbnail_use_attached"]); ?>>
									<?php _e("If featured image is missing, show an image from the post",'wp_related_posts');?>
								</label>
								<br />


								<br />
								<label>
									<?php _e('For posts without images, a default image will be shown.<br/>
									You can upload your own default image here','wp_related_posts');?>
									<input type="file" name="wp_rp_default_thumbnail" />
								</label>
								<?php if($options['default_thumbnail_path']) : ?>
									<span style="display: inline-block; vertical-align: top; *display: inline; zoom: 1;">
										<img style="padding: 3px; border: 1px solid #DFDFDF; border-radius: 3px;" valign="top" width="80" height="80" src="<?php esc_attr_e(wp_rp_get_default_thumbnail_url()); ?>" alt="selected thumbnail" />
										<br />
										<label>
											<input type="checkbox" name="wp_rp_default_thumbnail_remove" value="yes" />
											<?php _e("Remove selected",'wp_related_posts');?>
										</label>
									</span>
								<?php endif; ?>
								</span>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php _e("Display Options:",'wp_related_posts'); ?></th>
							<td>
								<label>
									<input name="wp_rp_display_comment_count" type="checkbox" id="wp_rp_display_comment_count" value="yes" <?php checked($options["display_comment_count"]); ?>>
									<?php _e("Display Number of Comments",'wp_related_posts');?>
								</label><br />
								<label>
									<input name="wp_rp_display_publish_date" type="checkbox" id="wp_rp_display_publish_date" value="yes" <?php checked($options["display_publish_date"]); ?>>
									<?php _e("Display Publish Date",'wp_related_posts');?>
								</label><br />
								<label>
									<input name="wp_rp_display_excerpt" type="checkbox" id="wp_rp_display_excerpt" value="yes"<?php checked($options["display_excerpt"]); ?> onclick="wp_rp_display_excerpt_onclick();" >
									<?php _e("Display Post Excerpt",'wp_related_posts');?>
								</label>
								<label id="wp_rp_excerpt_max_length_label"<?php echo $options["display_excerpt"] ? '' : ' style="display: none;"'; ?>>
									<input name="wp_rp_excerpt_max_length" type="text" id="wp_rp_excerpt_max_length" class="small-text" value="<?php esc_attr_e($options["excerpt_max_length"]); ?>" /> <span class="description"><?php _e('Maximum Number of Characters.', 'wp_related_posts'); ?></span>
								</label><br/>
								<label for="wp_rp_related_posts_title_tag">
									<?php _e('Related Posts Title Tag', 'wp_related_posts'); ?>
									<select name="wp_rp_related_posts_title_tag" id="wp_rp_related_posts_title_tag" class="postform">
									<?php
									foreach ($title_tags as $tag) :
									?>
										<option value="<?php esc_attr_e($tag); ?>"<?php selected($related_posts_title_tag, $tag); ?>>&lt;<?php esc_html_e($tag); ?>&gt;</option>
									<?php endforeach; ?>
									</select>
								</label>
							</td>
						</tr>
					</table>
					<h3><?php _e("Other Settings:",'wp_related_posts'); ?></h3>
					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e('Exclude these Categories:', 'wp_related_posts'); ?></th>
							<td>
								<div class="excluded-categories">
									<?php
									$exclude = explode(',', $options['exclude_categories']);
									$args = array(
										'orderby' => 'name',
										'order' => 'ASC',
										'hide_empty' => false
										);

									foreach (get_categories($args) as $category) :
									?>
									<label>
										<input name="wp_rp_exclude_categories[]" type="checkbox" id="wp_rp_exclude_categories" value="<?php esc_attr_e($category->cat_ID); ?>"<?php checked(in_array($category->cat_ID, $exclude)); ?> />
										<?php esc_html_e($category->cat_name); ?>
										<br />
									</label>
									<?php endforeach; ?>
								</div>
							</td>
						</tr>
						<tr valign="top">
							<td colspan="2">
								<label>
									<input name="wp_rp_on_single_post" type="checkbox" id="wp_rp_on_single_post" value="yes" <?php checked($options['on_single_post']); ?>>
									<?php _e("Auto Insert Related Posts",'wp_related_posts');?>
								</label>
								(or add <pre style="display: inline">&lt;?php wp_related_posts()?&gt;</pre> to your single post template)
								<br />
								<label>
									<input name="wp_rp_on_rss" type="checkbox" id="wp_rp_on_rss" value="yes"<?php checked($options['on_rss']); ?>>
									<?php _e("Display Related Posts in Feed",'wp_related_posts');?>
								</label>
								<br />
								<label>
									<input name="wp_rp_show_RP_in_posts" type="checkbox" id="wp_rp_show_RP_in_posts" value="yes"<?php checked($options['show_RP_in_posts']); ?>>
									<?php _e("Show related posts in article text",'wp_related_posts');?>
								</label>
					<?php if($meta['show_traffic_exchange']): ?>
								<br />
								<label>
									<input name="wp_rp_traffic_exchange_enabled" type="checkbox" id="wp_rp_traffic_exchange_enabled" value="yes"<?php checked($options['traffic_exchange_enabled']); ?>>
									<?php _e("Enable traffic exchange with blogger networks",'wp_related_posts');?>
								</label>
					<?php endif; ?>
								<br />
								<label>
									<input name="wp_rp_ctr_dashboard_enabled" type="checkbox" id="wp_rp_ctr_dashboard_enabled" value="yes" <?php checked($options['ctr_dashboard_enabled']); ?> />
									<?php _e("Turn statistics on",'wp_related_posts');?>*
								</label>
								<br />
								<label>
									<input name="wp_rp_promoted_content_enabled" type="checkbox" id="wp_rp_promoted_content_enabled" value="yes" <?php checked($options['promoted_content_enabled']); ?> />
									<?php _e('Promoted Content', 'wp_related_posts');?>*
								</label>
							</td>
						</tr>
					</table>
					<p class="submit"><input type="submit" value="<?php _e('Save changes', 'wp_related_posts'); ?>" class="button-primary" /></p>

				</form>
				<div>
					* Provided via <a target="_blank" href="http://related-posts.com/tos/">3rd party service</a>.
				</div>
			</div>
		</div>
	</div>
<?php }
