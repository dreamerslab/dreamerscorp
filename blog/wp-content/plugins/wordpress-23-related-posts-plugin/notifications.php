<?php
//
// Notifications system
//

add_action('wp_rp_load_notifications', 'wp_rp_load_remote_notifications');

function wp_rp_dismiss_notification($id) {
	$meta = wp_rp_get_meta();
	$messages_ref =& $meta['remote_notifications'];

	if(array_key_exists($id, $messages_ref)) {
		unset($messages_ref[$id]);
		wp_rp_update_meta($meta);

		$blog_id = $meta['blog_id'];
		$auth_key = $meta['auth_key'];
		$req_options = array(
			'timeout' => 5
		);
		$url = WP_RP_CTR_DASHBOARD_URL . "notifications/dismiss/?blog_id=$blog_id&auth_key=$auth_key&msg_id=$id";
		$response = wp_remote_get($url, $req_options);

		return true;
	}
	return false;
}

function wp_rp_number_of_available_notifications() {
	$meta = wp_rp_get_meta();
	
	return sizeof($meta['remote_notifications']);
}

function wp_rp_print_notifications() {
	$meta = wp_rp_get_meta();
	$messages = $meta['remote_notifications'];

	foreach($messages as $id => $text) {
		echo '<div class="wp_rp_notification">
			<a href="' . admin_url('admin-ajax.php?action=rp_dismiss_notification&id=' . $id) . '" class="close">x</a>
			<p>' . $text . '</p>
		</div>';
	}
}

function wp_rp_schedule_notifications_cron() {
	if(!wp_next_scheduled('wp_rp_load_notifications')) {
		wp_schedule_event(time(), 'hourly', 'wp_rp_load_notifications');
	}
}

function wp_rp_unschedule_notifications_cron() {
	wp_clear_scheduled_hook('wp_rp_load_notifications');
}

// Notifications cron job hourly callback
function wp_rp_load_remote_notifications() {
	$meta = wp_rp_get_meta();
	$options = wp_rp_get_options();

	$blog_id = $meta['blog_id'];
	$auth_key = $meta['auth_key'];

	$req_options = array(
		'timeout' => 5
	);

	if(empty($blog_id) || empty($auth_key) || !$options['ctr_dashboard_enabled']) return;

	// receive remote recommendations
	$url = WP_RP_CTR_DASHBOARD_URL . "notifications/?blog_id=$blog_id&auth_key=$auth_key";
	$response = wp_remote_get($url, $req_options);

	if (wp_remote_retrieve_response_code($response) == 200) {
		$body = wp_remote_retrieve_body($response);

		if ($body) {
			$json = json_decode($body);

			if ($json && isset($json->status) && $json->status === 'ok' && isset($json->data) && is_object($json->data)) 
			{
				$messages_ref =& $meta['remote_notifications'];
				$data = $json->data;

				if(isset($data->msgs) && is_array($data->msgs)) {
					// add new messages from server and update old ones
					foreach($data->msgs as $msg) {
						$messages_ref[$msg->msg_id] = $msg->text;
					}

					// sort messages by identifier
					ksort($messages_ref);
				}

				if(isset($data->delete_msgs) && is_array($data->delete_msgs)) {
					foreach($data->delete_msgs as $msg_id) {
						if(array_key_exists($msg_id, $messages_ref)) {
							unset($messages_ref[$msg_id]);
						}
					}
				}

				if(isset($data->turn_on_remote_recommendations) && $data->turn_on_remote_recommendations) {
					$meta['remote_recommendations'] = true;
				} else if(isset($data->turn_off_remote_recommendations) && $data->turn_off_remote_recommendations) {
					$meta['remote_recommendations'] = false;
				}

				if(isset($data->show_blogger_network_form) && $data->show_blogger_network_form) {
					$meta['show_blogger_network_form'] = true;
				} else if(isset($data->hide_blogger_network_form) && $data->hide_blogger_network_form) {
					$meta['show_blogger_network_form'] = false;
				}

				if(isset($data->show_RP_in_posts) && $data->show_RP_in_posts) {
					$options['show_RP_in_posts'] = true;
				} else if(isset($data->hide_RP_in_posts) && $data->hide_RP_in_posts) {
					$options['show_RP_in_posts'] = false;
				}

				if(isset($data->show_traffic_exchange) && $data->show_traffic_exchange) {
					$meta['show_traffic_exchange'] = true;
					$options['traffic_exchange_enabled'] = true;
				} else if(isset($data->hide_traffic_exchange) && $data->hide_traffic_exchange) {
					$meta['show_traffic_exchange'] = false;
					$options['traffic_exchange_enabled'] = false;
				}

				wp_rp_update_meta($meta);
				wp_rp_update_options($options);
			}
		}
	}
}
