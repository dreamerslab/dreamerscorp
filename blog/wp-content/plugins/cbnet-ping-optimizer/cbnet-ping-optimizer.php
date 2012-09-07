<?php
/* 
 * Plugin Name:   cbnet Ping Optimizer
 * Plugin URI:    http://www.chipbennett.net/wordpress/plugins/cbnet-ping-optimizer/
 * Description:   Saves your wordpress blog from getting tagged as ping spammer. (Note: this plugin is a fork of the MaxBlogPress Ping Optimizer plugin, with registration/activiation functionality removed.)
 * Version:       2.3.3
 * Author:        chipbennett
 * Author URI:    http://www.chipbennett.net//
 *
 * License:       GNU General Public License, v2 (or newer)
 * License URI:  http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * This program was modified from MaxBlogPress Favicon plugin, version 2.2.5, 
 * Copyright (C) 2007 www.maxblogpress.com, released under the GNU General Public License.
 */

define('cbnetpo_NAME', 'cbnet Ping Optimizer');	// Name of the Plugin
define('cbnetpo_VERSION', '2.3.3');					// Current version of the Plugin
define("cbnetpo_LOG", true);							// Set to 'true' to keep log, else 'false'.

/**
 * cbnetPingOptimizer - cbnet Ping Optimizer Class
 * Holds all the necessary functions and variables
 */
class cbnetPingOptimizer 
{
	var $cbnetpo_ping_option = 0;			// cbnet Ping Optimizer option
	var $cbnetpo_ping_sites = '';			// cbnet Ping Optimizer pinging URLs
	var $cbnetpo_future_pings = array();	// List of future posts to be pinged
	var $cbnetpo_future_ping_time = '';	// Last updated time for future ping
	var $cbnetpo_current_date = '';		// Holds the current date and time
	var $cbnetpo_post_title = "";			// Title of the post
	var $cbnetpo_edited = "";				// Set if post is edited
	var $cbnetpo_pvt_to_pub = "";			// Set if post status changes from private to published
	var $cbnetpo_max_log = 1000;   		// Maximum lines of log data to be stored
	var $cbnetpo_rows_to_show = 35;        // Number of log rows to be displayed in options page
	var $cbnetpo_pinglog_tbl = 'cbnetpo_ping_optimizer'; // Ping log table
    // Pinging action/type
	var $cbnetpo_type =  array(1 => 'none', 2 => 'new', 3 => 'future', 4 => 'forced', 5 => 'edited', 6 => 'disabled', 7 => 'noservices', 8 => 'excessive');
	
	/**
	 * Constructor. Add cbnet Ping Optimizer plugin actions/filters and gets the user defined options.
	 * Also removes the default WordPress pinging services.
	 */
	function cbnetPingOptimizer() {
		global $wp_version, $table_prefix;
		$this->cbnetpo_pinglog_tbl = $table_prefix.$this->cbnetpo_pinglog_tbl;
		$this->cbnetpo_siteurl    = get_bloginfo('wpurl');
		$this->cbnetpo_siteurl    = (strpos($this->cbnetpo_siteurl,'http://') === false) ? get_bloginfo('siteurl') : $this->cbnetpo_siteurl;
		$this->cbnetpo_path       = preg_replace('/^.*wp-content[\\\\\/]plugins[\\\\\/]/', '', __FILE__);
		$this->cbnetpo_path       = str_replace('\\','/',$this->cbnetpo_path);
		$this->cbnetpo_fullpath   = $this->cbnetpo_siteurl.'/wp-content/plugins/'.substr($this->cbnetpo_path,0,strrpos($this->cbnetpo_path,'/')).'/';
		$this->cbnetpo_incpath    = $this->cbnetpo_fullpath.'include/';
		$this->cbnetpo_abspath    = str_replace("\\","/",ABSPATH); 
		$this->img_how         = '<img src="'.$this->cbnetpo_incpath.'images/how.gif" border="0" align="absmiddle">';
		$this->img_comment     = '<img src="'.$this->cbnetpo_incpath.'images/comment.gif" border="0" align="absmiddle">';
		$this->cbnetpo_wp_version = $wp_version;
		add_action('activate_'.$this->cbnetpo_path, array(&$this, 'cbnetpoActivate'));
		add_action('deactivate_'.$this->cbnetpo_path, array(&$this, 'cbnetpoDeactivate'));
		add_action('admin_menu', array(&$this, 'cbnetpoAddMenu'));
		add_filter('plugin_action_links_'.plugin_basename(__FILE__), array($this, 'cbnetpo_actlinks'), 10, 1 ); 
		add_action('wp_head', array(&$this, 'cbnetpoFuturePing'));
		add_filter('title_save_pre', array(&$this, 'cbnetpoGetPostTitle'));
		add_action('edit_post', array(&$this, 'cbnetpoEditPost'));		
		add_action('private_to_published', array(&$this, 'cbnetpoPrivateToPublished'));	
		add_action('save_post', array(&$this, 'cbnetpoPing'));
		add_action("delete_post", array(&$this, 'cbnetpoFuturePingDelete'));
		do_action('cbnetpo_ping', $post_title, $post_type);
		add_action('cbnetpo_ping', array(&$this, 'cbnetpoPingServices'), 5, 2);
		remove_action('do_pings', 'do_all_pings');
		remove_action("publish_post", "generic_ping");

		$this->cbnetpo_current_date = current_time('mysql');
		$this->cbnetpo_ping_sites   = get_option("ping_sites");
		$this->cbnetpo_ping_option  = get_option('cbnetpo_ping_optimizer');
		$this->cbnetpo_future_pings = get_option('cbnetpo_future_pings');
		$this->cbnetpo_options       = get_option('cbnetpo_options');
		if ( $this->cbnetpo_wp_version < 2.1 ) {
			if( !is_array($this->cbnetpo_future_pings) ) {
				$this->cbnetpo_future_pings = array();
			}
			if( !$this->cbnetpo_future_ping_time = get_option('cbnetpo_future_ping_time') ) {
				$this->cbnetpo_future_ping_time = date('Y-m-d-H-i-s');
			}
		}
		// Check if ping limit reached
		if ( $this->cbnetpo_options['limit_ping'] == 1 ) {
			$last_ping_time  = get_option('cbnetpo_last_ping_time');
			$curr_time       = current_time('mysql');
			$limit_time      = $this->cbnetpo_options['limit_time'] * 60;
			$limit_number    = $this->cbnetpo_options['limit_number'];
			$_last_ping_time = intval(strtotime($last_ping_time));
			$_curr_time      = intval(strtotime($curr_time));
			$cbnetpo_ping_num    = get_option('cbnetpo_ping_num');
			if ( $_last_ping_time <= 0 ) $_last_ping_time = $_curr_time;
			if ( ($limit_time >= ($_curr_time - $_last_ping_time)) && ($cbnetpo_ping_num >= $limit_number) ) {
				$this->excessive_pinging = 1;
			} else {
				if ( $cbnetpo_ping_num >= $limit_number ) update_option('cbnetpo_ping_num',0);
				$this->excessive_pinging = 0;
			}
		} else {
			$this->excessive_pinging = 0;
		}
	}
	
	/**
	 * Called when plugin is activated. Adds option_value to the options table.
	 */
	function cbnetpoActivate() {
		$default_options = array('cbnetpo_version' => cbnetpo_VERSION, 'limit_ping' => 0, 'limit_number' => 1, 'limit_time' => 15);
		add_option('cbnetpo_options', $default_options);
		add_option('cbnetpo_ping_optimizer', 1, 'cbnet Ping Optimizer plugin options', 'no');
		if ( $this->cbnetpo_wp_version < 2.1 ) {
			add_option('cbnetpo_future_ping_time', date('Y-m-d-H-i-s'), 'cbnet Ping Optimizer plugin options', 'no');
		}
		$this->cbnetpoCreateLogTable();
		return true;
	}
	
	/**
	 * Called when plugin is deactivated. Deletes cbnet Ping Optimizer option from the options table.
	 */
	function cbnetpoDeactivate() {
		delete_option('cbnetpo_future_ping_time');
		delete_option('cbnetpo_future_pings');
		return true;
	}
	
	/**
	 * Creates ping log table
	 * @access public 
	 */
	function cbnetpoCreateLogTable() {
		global $wpdb;
		if ( $wpdb->get_var("show tables like '$this->cbnetpo_pinglog_tbl'") != $this->cbnetpo_pinglog_tbl ) {
			if ( file_exists(ABSPATH . 'wp-admin/includes/upgrade.php') ) {
				require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
			} else { // Wordpress <= 2.2
				require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
			}
			dbDelta("CREATE TABLE `{$this->cbnetpo_pinglog_tbl}` (
					`id` int(11) NOT NULL auto_increment,
					`date_time` datetime NOT NULL,
					`post_title` text, 
					`log_data` text,
					`type` tinyint(4) DEFAULT 1, 
					PRIMARY KEY (`id`)
					);
				");
		}
	}

	/**
	 * Adds "cbnet Ping Optimizer" link to admin Options menu
	 */
	function cbnetpoAddMenu()	{
		add_options_page(cbnetpo_NAME, 'cbnet Ping Optimizer', 'manage_options', $this->cbnetpo_path, array(&$this, 'cbnetpoOptionsPg'));
	}

	/**
	 * Adds "Settings" link to Plugin Action links on Manage Plugins page
	 */
	function cbnetpo_actlinks( $links ) {
		$cbnetpo_settings_link = '<a href="options-general.php?page=cbnet-ping-optimizer/cbnet-ping-optimizer.php">Settings</a>'; 
		$links[] = $cbnetpo_settings_link;
		return $links; 
	}
	
	/**
	 * Page Header
	 */
	function cbnetpoHeader() {
		echo '<h2>'.cbnetpo_NAME.' '.cbnetpo_VERSION.'</h2>';
	}
	
	/**
	 * Page Footer
	 */
	function cbnetpoFooter() {
		echo '<p style="text-align:center;margin-top:3em;"><strong>'.cbnetpo_NAME.' '.cbnetpo_VERSION.' by <a href="http://www.chipbennett.net/" target="_blank" >Chip Bennett</a></strong></p>';
	}
	
	/**
	 * Displays the options page
	 * Carries out all the operations in options page
	 */
	function cbnetpoOptionsPg() {
		global $wpdb;
		$this->cbnetpo_request = $_REQUEST['cbnetpo'];
		$msg ='';
		
			if ( $this->cbnetpo_request['pingnow'] ) {
				if ( $this->cbnetpo_wp_version >= 2.1 ) {
					if ( $this->cbnetpo_ping_sites == "" ) { 
						$this->cbnetpoLog("NOT Pinging (no ping sites in services lists)", 7);
					} else if ( $this->cbnetpo_ping_option != 1 ) {
						$this->cbnetpoLog("NOT Pinging (disabled by administrator)", 6);
					} else {
						// schedule ping for now (forced ping)
						wp_schedule_single_event(time(), 'cbnetpo_ping', array('',4));
					}
				} else {
					$this->cbnetpoPingServices('',4);
				}
			} else if ( $this->cbnetpo_request['save'] ) {
				$this->cbnetpo_ping_sites  = $this->cbnetpo_request['uris'];
				$this->cbnetpo_ping_option = 0;
				if ( $this->cbnetpo_request['ping'] == 1 ) {
					$this->cbnetpo_ping_option = 1;
				}
				update_option("cbnetpo_ping_optimizer", $this->cbnetpo_ping_option);
				update_option("ping_sites", $this->cbnetpo_ping_sites);
				$this->cbnetpo_options = array('limit_ping' => $this->cbnetpo_request['limit_ping'], 'limit_number' => $this->cbnetpo_request['limit_number'], 'limit_time' => $this->cbnetpo_request['limit_time']);
				update_option('cbnetpo_options', $this->cbnetpo_options);
				$msg = 'Options saved.';
			} else if ( $_GET['d'] == 'yes' ) {
				$wpdb->query("DELETE FROM $this->cbnetpo_pinglog_tbl");
				$msg = 'Ping Log Deleted.';
			}
			
			if ( $this->cbnetpo_options['limit_ping'] == 1 ) {
				$limit_ping_chk = 'checked';
				$limit_ping_display = 'block ';
			} else {
				$limit_ping_chk = '';
				$limit_ping_display = 'none ';
			}
			if ( $this->cbnetpo_ping_option == 1 ) $ping_enable_chk = 'checked';
			else $ping_enable_chk = '';
			if ( $wpdb->get_var("show tables like '$this->cbnetpo_pinglog_tbl'") != $this->cbnetpo_pinglog_tbl ) {
				if ( $msg != '' ) $msg .= "<br>";
				$msg .= "<font color='#ff0000'>Plugin NOT upgraded properly. Please reactivate the plugin.</font>";
			}
			if ( trim($msg) != '' ) {
				echo '<div id="message" class="updated fade"><p><strong>'.$msg.'</strong></p></div>';
			}
			require_once('include/options-pg.php');
		}
	
	/**
	 * Sets flag if post is edited
	 * @param integer $id The Post ID
	 */
	function cbnetpoEditPost($id = 0) {
		if ( $id == 0 ) return;
		$this->cbnetpo_edited = 1;
		return $id;
	}
	
	/**
	 * Sets flag if post status changes from private to published
	 * @param integer $id The Post ID
	 */
	function cbnetpoPrivateToPublished($id = 0) {
		if ( $id == 0 ) return;
		$this->cbnetpo_pvt_to_pub = 1;
		return $id;
	}
	
	/**
	 * Gets the post title before publishing
	 * @param string $title
	 * @return string $title
	 */
	function cbnetpoGetPostTitle($title) {
		$this->cbnetpo_post_title = $title;
		return $title;
	}
	
	/**
	 * Formats the date to mm-dd-yyyy format
	 * @param datetime $datetime
	 * @return datetime $datetime. Formatted Date
	 */
	function cbnetpoFormatDate($datetime) {
		if ( $datetime != '' ) {
			$datetime_parts = explode(' ',$datetime);
			$date_parts     = explode('-',$datetime_parts[0]);
			$datetime       = $date_parts[1].'-'.$date_parts[2].'-'.$date_parts[0].' '.$datetime_parts[1];
		}
		return $datetime;
	}
	
	/**
	 * Copy of WP's "generic_ping".
	 * Uses another function to send the actual XML-RPC messages.
	 * @param string $post_title Title of the post
	 * @param integer $post_type Future post or current post
	 */
	function cbnetpoPingServices($post_title, $post_type) {
		global $wpdb;
		
		$this->already_pinged = array();
		$this->_post_type = $post_type;
		if ( strpos($post_title,'~#') !== false ) {
			$post_id_title = explode('~#',$post_title);
			$this->_post_title = $post_id_title[1];
			$this->_post_url = get_permalink($post_id_title[0]);
		} else {
			$this->_post_title = $post_title;
			$this->_post_url = '';
		}

		if ( $this->cbnetpo_wp_version >= 2.1 ) {
			// Do pingbacks
			while ($ping = $wpdb->get_row("SELECT * FROM {$wpdb->posts}, {$wpdb->postmeta} WHERE {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key = '_pingme' LIMIT 1")) {
				$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id = {$ping->ID} AND meta_key = '_pingme';");
				pingback($ping->post_content, $ping->ID);
			}
			// Do Enclosures
			while ($enclosure = $wpdb->get_row("SELECT * FROM {$wpdb->posts}, {$wpdb->postmeta} WHERE {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key = '_encloseme' LIMIT 1")) {
				$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE post_id = {$enclosure->ID} AND meta_key = '_encloseme';");
				do_enclose($enclosure->post_content, $enclosure->ID);
			}
			// Do Trackbacks
			$trackbacks = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE CHAR_LENGTH(TRIM(to_ping)) > 7 AND post_status = 'publish'");
			if ( is_array($trackbacks) ) {
				foreach ( $trackbacks as $trackback )
					do_trackbacks($trackback->ID);
			}
		}
		$services = get_settings('ping_sites');
		$services = preg_replace("|(\s)+|", '$1', $services);
		$services = trim($services);
		if ( '' != $services ) {
			set_time_limit(300);
			$services = explode("\n", $services);
			foreach ($services as $service) {
				$this->cbnetpoSendXmlrpc($service);
			}
		}
		unset($this->already_pinged);
		set_time_limit(60);
	}
	
	/**
	 * A modified version of WP's ping functionality "weblog_ping" in functions.php
	 * Uses correct extended Ping format and logs response from service.
	 * @param string $server
	 * @param string $path
	 */
	function cbnetpoSendXmlrpc($server = '', $path = '') {
		include_once (ABSPATH . WPINC . '/class-IXR.php');
		
		// using a timeout of 3 seconds should be enough to cover slow servers
		$client = new IXR_Client($server, ((!strlen(trim($path)) || ('/' == $path)) ? false : $path));
		$client->timeout = 3;
		$client->useragent .= ' -- WordPress/'.$this->cbnetpo_wp_version;
	
		// when set to true, this outputs debug messages by itself
		$client->debug = false;
		$home = trailingslashit(get_option('home'));
		$check_url = ($this->_post_url != '') ? $this->_post_url : get_bloginfo('rss2_url');
				
		if ( !in_array($server,$this->already_pinged) ) {
			$this->already_pinged[] = $server;
			///$this->_post_title = $this->_post_title.'###'.$check_url;///
			// the extendedPing format should be "blog name", "blog url", "check url" (post url), and "feed url",
			// but it would seem as if the standard has been mixed up. It's therefore good to repeat the feed url.
			// $this->_post_type = 2 if new post and 3 if future post
			if ( $client->query('weblogUpdates.extendedPing', get_settings('blogname'), $home, $check_url, get_bloginfo('rss2_url')) ) { 
				$this->cbnetpoLog($server." was successfully pinged (extended format)", $this->_post_type, $this->_post_title);
			} else {
				if ( $client->query('weblogUpdates.ping', get_settings('blogname'), $home) ) {
					$this->cbnetpoLog($server." was successfully pinged", $this->_post_type, $this->_post_title);
				} else {
					$this->cbnetpoLog($server." could not be pinged. Error message: \"".$client->error->message."\"", $this->_post_type, $this->_post_title);
				}
			}
		}
	}
	
	/**
	 * Pings if the post is published.
	 * Doesn't ping if the post is edited or if future post
	 * @param integer $id The Post ID
	 */
	function cbnetpoPing($id) {
		global $wpdb;
	
		$row = $wpdb->get_row("SELECT ID,post_date,post_date_gmt,post_modified,post_status FROM $wpdb->posts WHERE id=$id", ARRAY_A);	
		
		if ( $this->cbnetpo_ping_sites != "" ) { 
			if ( $this->cbnetpo_ping_option == 1 ) {
				// if post is edited and not turned from private/draft to published
				if ( $this->cbnetpo_edited == 1 && $this->cbnetpo_pvt_to_pub == 0 && $row['post_status'] != 'draft' && $this->cbnetpo_post_title != '' && ($row['post_date'] <= current_time('mysql')) ) {
					$this->cbnetpoLog("NOT Pinging (%title% was edited)", 5, $this->cbnetpo_post_title);
				} else if ( $row['post_status'] != 'draft' ) { 
					$post_id_title = $row['ID'].'~#'.$this->cbnetpo_post_title;
					// if post_date is greater than current time/date then its a future post (don't ping it)			
					if ( ($row['post_date'] > current_time('mysql')) ) {
						if ( $this->excessive_pinging != 1 ) {
							if ( $this->cbnetpo_wp_version >= 2.1 ) {
								// schedule ping for future post
								wp_schedule_single_event(strtotime($row['post_date_gmt'].' GMT'), 'cbnetpo_ping', array($post_id_title,3));
							} else {
								$this->cbnetpo_future_pings[$id] = $id; 
								update_option('cbnetpo_future_pings', $this->cbnetpo_future_pings);
							}
							$this->cbnetpoLog("NOT Pinging (future post: %title%). Will ping after ".$this->cbnetpoFormatDate($row['post_date']), 1, $this->cbnetpo_post_title);
							update_option('cbnetpo_last_ping_time',current_time('mysql'));
							update_option('cbnetpo_ping_num', get_option('cbnetpo_ping_num')+1);
						} else {
							$this->cbnetpoLog("NOT Pinging (Excessive Pinging Limit Reached)", 8);
						}		
					} else if ( ($this->cbnetpo_pvt_to_pub == 1 || $row["post_status"] == 'publish') && $this->cbnetpo_post_title != '' ) {	
						if ( $this->excessive_pinging != 1 ) {
							if ( $this->cbnetpo_wp_version >= 2.1 ) {
								// schedule ping for new post
								wp_schedule_single_event(time(), 'cbnetpo_ping', array($post_id_title,2));
							} else {
								$this->cbnetpoPingServices($post_id_title, 2);
							}	
							update_option('cbnetpo_last_ping_time',current_time('mysql'));
							update_option('cbnetpo_ping_num', get_option('cbnetpo_ping_num')+1);
						} else {
							$this->cbnetpoLog("NOT Pinging (Excessive Pinging Limit Reached)", 8);
						}		
					}
				}
			} else {
				if ( $row['post_status'] != 'draft' ) {
					if ( $this->cbnetpo_edited == 1 && $this->cbnetpo_pvt_to_pub == 0 && $row['post_status'] != 'draft' && $this->cbnetpo_post_title != '' && ($row['post_date'] <= current_time('mysql')) ) {
						$extra_msg = "It would NOT have pinged even if the ping was enabled.<br><br>Reason: %title% was edited";
					} else if ( ($row['post_date'] > current_time('mysql')) ) {
						$extra_msg = "It would have been scheduled for future ping if the ping was enabled.<br><br>Reason: future post %title%";
					} else if ( ($this->cbnetpo_pvt_to_pub == 1 || $row["post_status"] == 'publish') && $this->cbnetpo_post_title != '' ) {	
						$extra_msg = "It would have pinged if the ping was enabled.<br><br>Reason: new post %title%";
					}
					$this->cbnetpoLog("NOT Pinging (disabled by administrator)~^$extra_msg", 6, $this->cbnetpo_post_title);
				}
			}
		} else {
			if ( $row['post_status'] != 'draft' ) {
				if ( $this->cbnetpo_edited == 1 && $this->cbnetpo_pvt_to_pub == 0 && $row['post_status'] != 'draft' && $this->cbnetpo_post_title != '' && ($row['post_date'] <= current_time('mysql')) ) {
					$extra_msg = "It would NOT have pinged even if ping sites were available.<br><br>Reason: %title% was edited";
				} else if ( ($row['post_date'] > current_time('mysql')) ) {
					$extra_msg = "It would have been scheduled for future ping if ping sites were available.<br><br>Reason: future post %title%";
				} else if ( ($this->cbnetpo_pvt_to_pub == 1 || $row["post_status"] == 'publish') && $this->cbnetpo_post_title != '' ) {	
					$extra_msg = "It would have pinged if ping sites were available.<br><br>Reason: new post %title%";
				}
				$this->cbnetpoLog("NOT Pinging (no ping sites in services lists)~^$extra_msg", 7, $this->cbnetpo_post_title);
			}
		}
	}
	
	/**
	 * Checks if time elasped for future post, and if so, removes post form the ping list and pings
	 * For wordpress versions below 2.1
	 */
	function cbnetpoFuturePing() {
		global $wpdb;
		
		// future ping list is empty
		if ( count($this->cbnetpo_future_pings) <= 0 || $this->cbnetpo_wp_version >= 2.1) {
			return true;
		}
		$maxbpddc_data_recent = $this->cbnetpo_future_ping_time;
		
		// Check last updated date and update it if more than 15 min, and ping if any future post's time elasped
		$prev_time   = $this->cbnetpo_future_ping_time;
		$prev_time_parts = explode('-',$prev_time);
		$_prev_time  = mktime((int)$prev_time_parts[3], (int)$prev_time_parts[4], (int)$prev_time_parts[5], (int)$prev_time_parts[1], (int)$prev_time_parts[2], (int)$prev_time_parts[0]);
		$_now_time   = mktime(date("H"), date("i"), date("s"), date("m"), date("d"), date("Y"));
		$elapsed_min = ($_now_time-$_prev_time)/(60);
		$do_ping = 0;
		
		/// if last update/ping time more than 5 minutes
		if ( $elapsed_min > 5 ) {
			if ( is_array($this->cbnetpo_future_pings) ) {
				foreach ( $this->cbnetpo_future_pings as $id ) {
					$sql = "SELECT ID,post_date,post_status,post_title FROM $wpdb->posts WHERE id=$id";
					$row = $wpdb->get_row($sql, ARRAY_A);	
					
					// if future published post later has been changed to draft or other status
					// then delete it from the ping list (It will be automatically be pinged when its status changes to publish)
					if ( $row['post_status'] != 'publish' && $row['post_status'] != 'future' ) {
						unset($this->cbnetpo_future_pings[$id]);  
					}
					if ( $row["post_date"] <= current_time('mysql') && ($row['post_status'] == 'publish' || $row['post_status'] == 'future') ) {		
						unset($this->cbnetpo_future_pings[$id]);
						$do_ping = 1;
						$post_title = $row['post_title'];
					}
				}
			}
			update_option("cbnetpo_future_pings", $this->cbnetpo_future_pings);
			update_option('cbnetpo_future_ping_time', date('Y-m-d-H-i-s'));
			if ( $do_ping == 1 ) {
				$post_id_title = $row['ID'].'~#'.$post_title;
				$this->cbnetpoPingServices($post_id_title,3);
			}
		}
	}
	
	/**
	 * Deletes post from future ping list if deleted
	 * @param integer $id The Post ID
	 * @return integer $id
	 */
	function cbnetpoFuturePingDelete($id) { 
		global $wpdb;
		if ( $this->cbnetpo_wp_version >= 2.1 ) {
			$row = $wpdb->get_row("SELECT ID,post_date_gmt,post_title FROM $wpdb->posts WHERE id=$id", ARRAY_A);	
			$post_id_title = $row['ID'].'~#'.$row['post_title'];
			wp_unschedule_event(strtotime($row['post_date_gmt'].' GMT'), 'cbnetpo_ping', array($post_id_title,2));
			return $id;
		}
		if ( count($this->cbnetpo_future_pings) <= 0 ) {
			return $id;
		}
		unset($this->cbnetpo_future_pings[$id]);
		update_option('cbnetpo_future_pings', $this->cbnetpo_future_pings);
		return $id;
	}
	
	/**
	 * Saves the current plugin action
	 */
	function cbnetpoLog($log_data,$type,$post_title='') {
		global $wpdb;
		$date_time = $this->cbnetpo_current_date;		
		if ( cbnetpo_LOG == true ) {
			$query = "INSERT INTO $this->cbnetpo_pinglog_tbl (date_time, post_title, log_data, type) 
			          VALUES ('$date_time', '$post_title', '$log_data', '$type')";
			$wpdb->query($query);
		}
		return true;
	}
	
	/**
	 * Gets log data and displays it
	 * @param integer $total_logs Total lines of log data to be shown
	 * @return string
	 */
	function cbnetpoGetLogData() {
		global $wpdb;
		$query = "SELECT * FROM $this->cbnetpo_pinglog_tbl ORDER BY date_time DESC";
		$results = $wpdb->get_results($query,'ARRAY_A');
		$noof_records = count($results);
		
		// Delete old records form log table if max limit exceeds
		if ( $noof_records > $this->cbnetpo_max_log ) {
			$sql = "SELECT date_time FROM $this->cbnetpo_pinglog_tbl ORDER BY date_time DESC, id DESC LIMIT {$this->cbnetpo_max_log},1";
			$date_time = $wpdb->get_var($sql);
			$sql = "DELETE FROM $this->cbnetpo_pinglog_tbl WHERE date_time <= '$date_time'";
			$wpdb->query($sql);
		}
		
		if ( $noof_records <= 0 ) {
			$exists = 0;
			$msg = '<br>No ping log recorded yet.';
		} else {
			$exists = 1;
			$ping_data = array();
			foreach ( (array) $results as $key => $details ) {
				$ping_data[$details[date_time]][] = array($details['post_title'],$details['log_data'],$details['type']);
			}
			$count = 0;
			foreach ( (array) $ping_data as $ping_date => $_ping_data_arr ) {
				$ping_date = $this->cbnetpoFormatDate($ping_date);
				$count++; $cnt = 0;
				$bgcol = ($count%2 == 0) ? '#f8f8f8' : '#ffffff';
				foreach ( $_ping_data_arr as $key => $ping_data_arr ) {
					$ping_data = str_replace('%title%', '"'.$ping_data_arr[0].'"', $ping_data_arr[1]);
					if ( $ping_data_arr[2] == 2 || $ping_data_arr[2] == 3 || $ping_data_arr[2] == 4 ) {	
						if ( $ping_data_arr[2] == 2 ) { // new post ping
							$_data = "Pinging (new post: \"$ping_data_arr[0]\")";
						} else if ( $ping_data_arr[2] == 3 ) { // future post ping
							$_data = "Pinging (future post appeared in your blog: \"$ping_data_arr[0]\")";
						} else if ( $ping_data_arr[2] == 4 ) { // forced ping
							$_data = "Pinging (forced ping)";
						}
						if ( strpos($ping_data,'(extended format)') !== false ) {
							$_ping_data = '<font color="#009207">'.$ping_data.'</font>';
						} else if ( strpos($ping_data,'Error message:') !== false ) {
							$_ping_data = '<font color="#FF0000">'.$ping_data.'</font>';
						} else {
							$_ping_data = '<font color="#0273A8">'.$ping_data.'</font>';
						}
						if ( $cnt == 0 ) $msg .= '<div style="padding:4px;background-color:'.$bgcol.'" id="parent'.$count.'"><b><a style="cursor:hand;cursor:pointer;border-bottom:0px" onclick="cbnetpoShowHide(\'child'.$count.'\',\'img'.$count.'\');"><img src="'.$this->cbnetpo_incpath.'images/arr_green1.gif" id="img'.$count.'" align="absmiddle"> <u>'.$ping_date.'</u></a></b> - '.$_data.'</div><div id="child'.$count.'" style="display:none;">';
						$msg .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &raquo; '.$_ping_data.'<br />';
						$cnt++;
						if ( count($_ping_data_arr) == $cnt ) $msg .= '<br></div>';
					} else {
						if ( strpos($ping_data,'~^') !== false ) {
							$msg_parts = explode('~^', $ping_data);
							$msg_part1 = $msg_parts[0];
							$msg_part2 = htmlspecialchars($msg_parts[1]);
							$msg .= '<div style="padding:4px;background-color:'.$bgcol.'"><img src="'.$this->cbnetpo_incpath.'images/arr_blue1.gif" align="absmiddle"> <b>'.$ping_date.'</b> - '.$msg_part1;
							$msg .= ' <a href="#" onMouseover="tooltip(\''.$msg_part2.'\',200)" onMouseout="hidetooltip()" style="border-bottom:none;"><img src="'.$this->cbnetpo_incpath.'images/help.gif" border="0" align="absmiddle" /></a></div>';
						} else {
							$msg .= '<div style="padding:4px;background-color:'.$bgcol.'"><img src="'.$this->cbnetpo_incpath.'images/arr_blue1.gif" align="absmiddle"> <b>'.$ping_date.'</b> - '.$ping_data.'</div>';
						}
					}
				}
				if ( $count >= $this->cbnetpo_rows_to_show ) return array($msg,$exists,$noof_records);
			}
		}
		return array($msg,$exists,$noof_records);
	}
	
	
} // Eof Class

$cbnetPingOptimizer = new cbnetPingOptimizer();
?>