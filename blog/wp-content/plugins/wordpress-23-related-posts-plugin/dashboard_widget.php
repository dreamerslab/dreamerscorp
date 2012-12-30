<?php
//
// Dashboard widget
//

add_action('wp_dashboard_setup', 'wp_rp_dashboard_setup');

function wp_rp_dashboard_setup() {
	if (!current_user_can('delete_users')) {
		return;
	}

	$options = wp_rp_get_options();
	$meta = wp_rp_get_meta();

	if ($options['ctr_dashboard_enabled'] && $meta['blog_id'] && $meta['auth_key']) {
		wp_add_dashboard_widget('wp_rp_dashboard_widget', 'Related Posts', 'wp_rp_display_dashboard_widget');
		add_action('admin_enqueue_scripts', 'wp_rp_dashboard_scripts');
	}
}

function wp_rp_display_dashboard_widget() {
	$options = wp_rp_get_options();
	$meta = wp_rp_get_meta();
?>
	<input type="hidden" id="wp_rp_dashboard_url" value="<?php esc_attr_e(WP_RP_CTR_DASHBOARD_URL); ?>" />
	<input type="hidden" id="wp_rp_static_base_url" value="<?php esc_attr_e(WP_RP_STATIC_BASE_URL); ?>" />
	<input type="hidden" id="wp_rp_blog_id" value="<?php esc_attr_e($meta['blog_id']); ?>" />
	<input type="hidden" id="wp_rp_auth_key" value="<?php esc_attr_e($meta['auth_key']); ?>" />
	<?php if($meta['show_traffic_exchange'] && $options['traffic_exchange_enabled']): ?>
	<input type="hidden" id="wp_rp_show_traffic_exchange_statistics" value="1" />
	<?php endif; ?>

	<div id="wp_rp_wrap" class="wp_rp_dashboard">
		<?php wp_rp_print_notifications(); ?>
		<div id="wp_rp_statistics_wrap"></div>
	</div>
<?php
}

function wp_rp_dashboard_scripts($hook) {
	if($hook === 'index.php') {
		wp_enqueue_script('wp_rp_dashboard_script', plugins_url('static/js/dashboard.js', __FILE__), array('jquery'));
		wp_enqueue_style('wp_rp_dashaboard_style', plugins_url('static/css/dashboard.css', __FILE__));
	}
}
