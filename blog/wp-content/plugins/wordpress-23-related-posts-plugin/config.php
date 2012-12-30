<?php

define('WP_RP_STATIC_BASE_URL', 'http://dtmvdvtzf8rz0.cloudfront.net/static/');
define('WP_RP_STATIC_THEMES_PATH', 'css-text/');
define('WP_RP_STATIC_THEMES_THUMBS_PATH', 'css-img/');
define('WP_RP_STATIC_JSON_PATH', 'json/');

define("WP_RP_DEFAULT_CUSTOM_CSS",
".related_post_title {
}
ul.related_post {
}
ul.related_post li {
}
ul.related_post li a {
}
ul.related_post li img {
}");

define('WP_RP_THUMBNAILS_WIDTH', 150);
define('WP_RP_THUMBNAILS_HEIGHT', 150);
define('WP_RP_THUMBNAILS_DEFAULTS_COUNT', 31);

define("WP_RP_CTR_DASHBOARD_URL", "http://d.related-posts.com/");
define("WP_RP_CTR_REPORT_URL", "http://t.related-posts.com/pageview/?");
define("WP_RP_STATIC_CTR_PAGEVIEW_FILE", "js/pageview.js");

define("WP_RP_STATIC_RECOMMENDATIONS_JS_FILE", "js/recommendations.js");
define("WP_RP_STATIC_RECOMMENDATIONS_CSS_FILE", "css-img/recommendations.css");


define("WP_RP_RECOMMENDATIONS_AUTO_TAGS_MAX_WORDS", 200);
define("WP_RP_RECOMMENDATIONS_AUTO_TAGS_MAX_TAGS", 15);

define("WP_RP_RECOMMENDATIONS_AUTO_TAGS_SCORE", 2);
define("WP_RP_RECOMMENDATIONS_TAGS_SCORE", 10);
define("WP_RP_RECOMMENDATIONS_CATEGORIES_SCORE", 5);

define("WP_RP_RECOMMENDATIONS_NUM_PREGENERATED_POSTS", 50);

define("WP_RP_THUMBNAILS_NUM_PREGENERATED_POSTS", 50);

global $wp_rp_options, $wp_rp_meta;
$wp_rp_options = false;
$wp_rp_meta = false;

function wp_rp_get_options() {
	global $wp_rp_options, $wp_rp_meta;
	if($wp_rp_options) {
		return $wp_rp_options;
	}

	$wp_rp_meta = get_option('wp_rp_meta', false);
	if(!$wp_rp_meta || $wp_rp_meta['version'] !== WP_RP_VERSION) {
		wp_rp_upgrade();
		$wp_rp_meta = get_option('wp_rp_meta');
	}
	$wp_rp_meta = new ArrayObject($wp_rp_meta);

	$wp_rp_options = new ArrayObject(get_option('wp_rp_options'));

	return $wp_rp_options;
}

function wp_rp_get_meta() {
	global $wp_rp_meta;

	if (!$wp_rp_meta) {
		wp_rp_get_options();
	}

	return $wp_rp_meta;
}

function wp_rp_update_meta($new_meta) {
	global $wp_rp_meta;

	$new_meta = (array) $new_meta;

	$r = update_option('wp_rp_meta', $new_meta);

	if($r && $wp_rp_meta !== false) {
		$wp_rp_meta->exchangeArray($new_meta);
	}

	return $r;
}

function wp_rp_update_options($new_options) {
	global $wp_rp_options;

	$new_options = (array) $new_options;

	$r = update_option('wp_rp_options', $new_options);

	if($r && $wp_rp_options !== false) {
		$wp_rp_options->exchangeArray($new_options);
	}

	return $r;
}

function wp_rp_activate_hook() {
	wp_rp_get_options();
	wp_rp_schedule_notifications_cron();
}

function wp_rp_deactivate_hook() {
	wp_rp_unschedule_notifications_cron();
}

function wp_rp_upgrade() {
	$wp_rp_meta = get_option('wp_rp_meta', false);
	$version = false;

	if($wp_rp_meta) {
		$version = $wp_rp_meta['version'];
	} else {
		$wp_rp_old_options = get_option('wp_rp', false);
		if($wp_rp_old_options) {
			$version = '1.4';
		}
	}

	if($version) {
		if(version_compare($version, WP_RP_VERSION, '<')) {
			call_user_func('wp_rp_migrate_' . str_replace('.', '_', $version));
			wp_rp_upgrade();
		}
	} else {
		wp_rp_install();
	}
}

function wp_rp_related_posts_db_table_install() {
	global $wpdb;

	$tags_table_name = $wpdb->prefix . "wp_rp_tags";
	$sql_tags = "CREATE TABLE $tags_table_name (
	  post_id mediumint(9),
	  time timestamp DEFAULT CURRENT_TIMESTAMP,
	  label VARCHAR(32) NOT NULL,
	  weight float,
	  INDEX post_id (post_id),
	  INDEX label (label)
	 );";

	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql_tags);

	$latest_posts = get_posts(array('numberposts' => WP_RP_RECOMMENDATIONS_NUM_PREGENERATED_POSTS));
	foreach ($latest_posts as $post) {
		wp_rp_generate_tags($post);
	}
}

function wp_rp_install() {
	$wp_rp_meta = array(
		'blog_id' => false,
		'auth_key' => false,
		'version' => WP_RP_VERSION,
		'first_version' => WP_RP_VERSION,
		'new_user' => true,
		'show_upgrade_tooltip' => false,
		'show_install_tooltip' => true,
		'remote_recommendations' => true,		# WARNING: TODO: Turn this off at the end of this experiment!
		'show_turn_on_button' => true,
		'name' => '',
		'email' => '',
		'show_blogger_network_form' => false,
		'remote_notifications' => array(),
		'turn_on_button_pressed' => false,
		'show_statistics' => false,
		'show_traffic_exchange' => false
	);

	$wp_rp_options = array(
		'related_posts_title'			=> __('Related Posts', 'wp_related_posts'),
		'related_posts_title_tag'		=> 'h3',
		'display_excerpt'			=> false,
		'excerpt_max_length'			=> 200,
		'max_related_posts'			=> 5,
		'exclude_categories'			=> '',
		'on_single_post'			=> true,
		'on_rss'				=> false,
		'display_comment_count'			=> false,
		'display_publish_date'			=> false,
		'display_thumbnail'			=> false,
		'thumbnail_display_title'		=> true,
		'thumbnail_custom_field'		=> false,
		'thumbnail_use_attached'		=> true,
		'thumbnail_use_custom'			=> false,
		'default_thumbnail_path'		=> false,
		'theme_name' 				=> 'vertical-m.css',
		'theme_custom_css'			=> WP_RP_DEFAULT_CUSTOM_CSS,
		'ctr_dashboard_enabled'		=> false,
		'promoted_content_enabled'	=> false,
		'enable_themes'				=> false,
		'show_RP_in_posts' => true,
		'custom_theme_enabled' => false,
		'show_santa_hat' => true,
		'traffic_exchange_enabled' => false
	);

	update_option('wp_rp_meta', $wp_rp_meta);
	update_option('wp_rp_options', $wp_rp_options);

	wp_rp_related_posts_db_table_install();
}

function wp_rp_migrate_2_1() {
	$wp_rp_meta = get_option('wp_rp_meta');
	$wp_rp_options = get_option('wp_rp_options');

	$wp_rp_meta['version'] = '2.2';

	$wp_rp_options['custom_theme_enabled'] = $wp_rp_options['theme_name'] == 'custom.css';
	if ($wp_rp_options['custom_theme_enabled']) {
		$wp_rp_options['theme_name'] = 'plain.css';
	}
	$wp_rp_options['show_santa_hat'] = false;

	$wp_rp_options['show_RP_in_posts'] = false;

	$wp_rp_options['traffic_exchange_enabled'] = false;
	$wp_rp_meta['show_traffic_exchange'] = false;

	update_option('wp_rp_options', $wp_rp_options);
	update_option('wp_rp_meta', $wp_rp_meta);
}

function wp_rp_migrate_2_0() {
	$wp_rp_meta = get_option('wp_rp_meta');
	$wp_rp_options = get_option('wp_rp_options');

	$wp_rp_meta['version'] = '2.1';

	if ($wp_rp_options['default_thumbnail_path']) {
		$upload_dir = wp_upload_dir();
		$wp_rp_options['default_thumbnail_path'] = $upload_dir['baseurl'] . $wp_rp_options['default_thumbnail_path'];
	}

	update_option('wp_rp_options', $wp_rp_options);
	update_option('wp_rp_meta', $wp_rp_meta);

	if($wp_rp_options['display_thumbnail'] && $wp_rp_options['thumbnail_use_attached']) {
		wp_rp_process_latest_post_thumbnails();
	}
}

function wp_rp_migrate_1_7() {
	$wp_rp_meta = get_option('wp_rp_meta');
	$wp_rp_options = get_option('wp_rp_options');

	$wp_rp_meta['version'] = '2.0';

	$wp_rp_options['promoted_content_enabled'] = $wp_rp_options['ctr_dashboard_enabled'];
	$wp_rp_options['exclude_categories'] = $wp_rp_options['not_on_categories'];

	$wp_rp_meta['show_statistics'] = $wp_rp_options['ctr_dashboard_enabled'];

	// Commented out since we don't want to lose this info for users that will downgrade the plugin because of the change
	//unset($wp_rp_options['missing_rp_algorithm']);
	//unset($wp_rp_options['missing_rp_title']);
	//unset($wp_rp_options['not_on_categories']);

	// Forgot to unset this the last time.
	unset($wp_rp_meta['show_invite_friends_form']);

	update_option('wp_rp_options', $wp_rp_options);
	update_option('wp_rp_meta', $wp_rp_meta);

	wp_rp_schedule_notifications_cron();
	wp_rp_related_posts_db_table_install();
}

function wp_rp_migrate_1_6() {
	$wp_rp_meta = get_option('wp_rp_meta');
	$wp_rp_options = get_option('wp_rp_options');

	$wp_rp_meta['version'] = '1.7';

	unset($wp_rp_options['scroll_up_related_posts']);
	unset($wp_rp_options['include_promotionail_link']);
	unset($wp_rp_options['show_invite_friends_form']);

	$wp_rp_meta['show_blogger_network_form'] = false;
	$wp_rp_meta['remote_notifications'] = array();

	$wp_rp_meta['turn_on_button_pressed'] = false;

	update_option('wp_rp_options', $wp_rp_options);
	update_option('wp_rp_meta', $wp_rp_meta);
}

function wp_rp_migrate_1_5_2_1() { # This was a silent release, but WP_RP_VERSION was not properly updated, so we don't know exactly what happened...
	$wp_rp_meta = get_option('wp_rp_meta');

	$wp_rp_meta['version'] = '1.5.2';

	update_option('wp_rp_meta', $wp_rp_meta);
}

function wp_rp_migrate_1_5_2() {
	$wp_rp_meta = get_option('wp_rp_meta');
	$wp_rp_options = get_option('wp_rp_options');

	$wp_rp_meta['version'] = '1.6';

	$wp_rp_meta['show_install_tooltip'] = false;
	$wp_rp_meta['remote_recommendations'] = false;
	$wp_rp_meta['show_turn_on_button'] = !($wp_rp_options['ctr_dashboard_enabled'] && $wp_rp_options['display_thumbnail']);
	$wp_rp_meta['name'] = '';
	$wp_rp_meta['email'] = '';
	$wp_rp_meta['show_invite_friends_form'] = false;

	unset($wp_rp_meta['show_ctr_banner']);
	unset($wp_rp_meta['show_blogger_network']);

	$wp_rp_options['scroll_up_related_posts'] = false;

	update_option('wp_rp_meta', $wp_rp_meta);
	update_option('wp_rp_options', $wp_rp_options);
}
function wp_rp_migrate_1_5_1() {
	$wp_rp_options = get_option('wp_rp_options');
	$wp_rp_meta = get_option('wp_rp_meta');

	$wp_rp_options['enable_themes'] = true;
	$wp_rp_meta['version'] = '1.5.2';

	update_option('wp_rp_options', $wp_rp_options);
	update_option('wp_rp_meta', $wp_rp_meta);
}
function wp_rp_migrate_1_5() {
	$wp_rp_options = get_option('wp_rp_options');
	$wp_rp_meta = get_option('wp_rp_meta');

	$wp_rp_meta['show_blogger_network'] = false;
	$wp_rp_meta['version'] = '1.5.1';

	$wp_rp_options['include_promotionail_link'] = false;
	$wp_rp_options['ctr_dashboard_enabled'] = !!$wp_rp_options['ctr_dashboard_enabled'];

	update_option('wp_rp_options', $wp_rp_options);
	update_option('wp_rp_meta', $wp_rp_meta);
}

function wp_rp_migrate_1_4() {
	global $wpdb;

	$wp_rp = get_option('wp_rp');

	$wp_rp_options = array();

	////////////////////////////////

	$wp_rp_options['missing_rp_algorithm'] = (isset($wp_rp['wp_no_rp']) && in_array($wp_rp['wp_no_rp'], array('text', 'random', 'commented', 'popularity'))) ? $wp_rp['wp_no_rp'] : 'random';

	if(isset($wp_rp['wp_no_rp_text']) && $wp_rp['wp_no_rp_text']) {
		$wp_rp_options['missing_rp_title'] = $wp_rp['wp_no_rp_text'];
	} else {
		if($wp_rp_options['missing_rp_algorithm'] === 'text') {
			$wp_rp_options['missing_rp_title'] = __('No Related Posts', 'wp_related_posts');
		} else {
			$wp_rp_options['missing_rp_title'] = __('Random Posts', 'wp_related_posts');
		}
	}

	$wp_rp_options['on_single_post'] = isset($wp_rp['wp_rp_auto']) ? !!$wp_rp['wp_rp_auto'] : true;

	$wp_rp_options['display_comment_count'] = isset($wp_rp['wp_rp_comments']) ? !!$wp_rp['wp_rp_comments'] : false;

	$wp_rp_options['display_publish_date'] = isset($wp_rp['wp_rp_date']) ? !!$wp_rp['wp_rp_date'] : false;

	$wp_rp_options['display_excerpt'] = isset($wp_rp['wp_rp_except']) ? !!$wp_rp['wp_rp_except'] : false;

	if(isset($wp_rp['wp_rp_except_number']) && is_numeric(trim($wp_rp['wp_rp_except_number']))) {
		$wp_rp_options['excerpt_max_length'] = intval(trim($wp_rp['wp_rp_except_number']));
	} else {
		$wp_rp_options['excerpt_max_length'] = 200;
	}

	$wp_rp_options['not_on_categories'] = isset($wp_rp['wp_rp_exclude']) ? $wp_rp['wp_rp_exclude'] : '';

	if(isset($wp_rp['wp_rp_limit']) && is_numeric(trim($wp_rp['wp_rp_limit']))) {
		$wp_rp_options['max_related_posts'] = intval(trim($wp_rp['wp_rp_limit']));
	} else {
		$wp_rp_options['max_related_posts'] = 5;
	}

	$wp_rp_options['on_rss'] = isset($wp_rp['wp_rp_rss']) ? !!$wp_rp['wp_rp_rss'] : false;

	$wp_rp_options['theme_name'] = isset($wp_rp['wp_rp_theme']) ? $wp_rp['wp_rp_theme'] : 'plain.css';

	$wp_rp_options['display_thumbnail'] = isset($wp_rp['wp_rp_thumbnail']) ? !!$wp_rp['wp_rp_thumbnail'] : false;

	$custom_fields = $wpdb->get_col("SELECT meta_key FROM $wpdb->postmeta GROUP BY meta_key HAVING meta_key NOT LIKE '\_%' ORDER BY LOWER(meta_key)");
	if(isset($wp_rp['wp_rp_thumbnail_post_meta']) && in_array($wp_rp['wp_rp_thumbnail_post_meta'], $custom_fields)) {
		$wp_rp_options['thumbnail_custom_field'] = $wp_rp['wp_rp_thumbnail_post_meta'];
	} else {
		$wp_rp_options['thumbnail_custom_field'] = false;
	}

	$wp_rp_options['thumbnail_display_title'] = isset($wp_rp['wp_rp_thumbnail_text']) ? !!$wp_rp['wp_rp_thumbnail_text'] : false;

	$wp_rp_options['related_posts_title'] = isset($wp_rp['wp_rp_title']) ? $wp_rp['wp_rp_title'] : '';

	$wp_rp_options['related_posts_title_tag'] = isset($wp_rp['wp_rp_title_tag']) ? $wp_rp['wp_rp_title_tag'] : 'h3';

	$wp_rp_options['default_thumbnail_path'] = (isset($wp_rp['wp_rp_default_thumbnail_path']) && $wp_rp['wp_rp_default_thumbnail_path']) ? $wp_rp['wp_rp_default_thumbnail_path'] : false;

	$wp_rp_options['thumbnail_use_attached'] = isset($wp_rp["wp_rp_thumbnail_extract"]) && ($wp_rp["wp_rp_thumbnail_extract"] === 'yes');

	$wp_rp_options['thumbnail_use_custom'] = $wp_rp_options['thumbnail_custom_field'] && !(isset($wp_rp['wp_rp_thumbnail_featured']) && $wp_rp['wp_rp_thumbnail_featured'] === 'yes');

	$wp_rp_options['theme_custom_css'] = WP_RP_DEFAULT_CUSTOM_CSS;

	$wp_rp_options['ctr_dashboard_enabled'] = false;

	////////////////////////////////

	$wp_rp_meta = array(
		'blog_id' => false,
		'auth_key' => false,
		'version' => '1.5',
		'first_version' => '1.4',
		'new_user' => false,
		'show_upgrade_tooltip' => true,
		'show_ctr_banner' => true
	);

	update_option('wp_rp_meta', $wp_rp_meta);
	update_option('wp_rp_options', $wp_rp_options);
}
