<?php
/*
Plugin Name: Ping List Checker
Plugin URI: http://www.spunkyjones.com/wordpress/ping-list-checker-plugin/
Description: A WordPress plugin which lets you check your ping list. It can be used to verify all domains in the ping list are unique and can also be used to test which sites are up and which are down.
Version: 1.1
Author: Naif Amoodi
Author URI: http://www.naif.in
*/

define('PLC_PLUGIN_URL', $_SERVER['PHP_SELF'] . '?page=' . plugin_basename(__FILE__));

function plc_duplicate_checker($list) {
	$list = explode("\n", $list);
	foreach($list as &$item) {
		plc_clean_list($item);
	}
	$count_values = array_count_values($list);
	foreach($count_values as $value => $count) if($count >= 2) $duplicate[$value] = $count;
	if($duplicate) {
		echo '<h3>Duplicate List</h3>';
	?>
	<table class="widefat">
		<thead>
			<tr>
				<th>Domain</th>
				<th>Count</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($duplicate as $domain => $count) {
			echo '<tr>';
			echo "<td>$domain</td>";
			echo "<td>$count</td>";
			echo '</tr>';
		}
		?>
		</tbody>
	</table>
	<?php
	}
	else echo 'No duplicates found.';
}

function plc_alive_checker($list) {
	$list = explode("\n", $list);
	error_reporting( E_ALL );
	require 'ping.php';
	$ping = Net_Ping::factory();
	if(PEAR::isError($ping)) echo $ping->getMessage();
	else {
	?>
	<table class="widefat">
		<thead>
			<tr>
				<th>Domain</th>
				<th>Status</th>
			</tr>
		</thead>
		<tbody>
		<?php
		foreach($list as $domain) {
			echo '<tr>';
			echo "<td>$domain</td>";
			plc_clean_list($domain, array('http://', 'https://', '/'));
			$result = $ping->ping($domain);
			if(isset($result->_received) && $result->_received >= 1) echo "<td>Up</td>";
			else echo "<td>Down</td>";
			echo '</tr>';
		}
		?>
		</tbody>
	</table>
	<?php
	}
}

function plc_clean_list(&$item, $search = array('http://', 'https://', 'www.', '/')) {
	$item = trim($item);
	$item = str_replace( $search, '', $item );
}

function plc_main() {
	?>
	<div class="wrap">
		<h2>Ping List Checker</h2>

		<p class="submit">
			<a class="button" href="<?php echo PLC_PLUGIN_URL; ?>&duplicate=1" title="Verifies if whether all domains in the ping list are unique or not">Duplicate Checker</a> | 
			<a class="button" href="<?php echo PLC_PLUGIN_URL; ?>&alive=1" title="Verifies if whether all domains are alive or not">Alive Checker</a>
		</p>
		<?php
			if($_GET['duplicate'] == 1 xor $_GET['alive'] == 1) {
				$list = get_option('ping_sites');
				if($list) {
					if($_GET['duplicate'] == 1) plc_duplicate_checker($list);
					elseif($_GET['alive'] == 1) plc_alive_checker($list);
				}
				else echo 'Ping list is empty.';
			}
		?>
	</div>
	<?php
}

function plc_admin_menu() {
	add_options_page('Ping List Checker', 'Ping List Checker', 8, __FILE__, 'plc_main');
}

if( is_admin() ) {
	add_action('admin_menu', 'plc_admin_menu');
}
?>