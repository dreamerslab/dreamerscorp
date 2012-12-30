<?php
/**
* @package Kifulab HTTP Express
* @author Kifulab
* @version 1.0.2
*/
/*
Plugin Name: HTTP Express
Plugin URI: http://www.kifulab.net/plugins/http_express
Description: Enable a php based HTTP proxy to add correct HTTP headers to images, css and javascripts. Useful if you want to improve cache usage and reduce bandwidth impact. Requires Apache with mod_rewrite enabled to work.
Author: Kifulab
Version: 1.0.1
Author URI: http://www.kifulab.net
*/
	
	load_plugin_textdomain('kifulab_http_express',false,dirname(plugin_basename(__FILE__)) . '/languages');
	define("HTTP_EXPRESS_VERSION","1.0");
	define("HTTP_EXPRESS_URL",plugins_url('',__FILE__));
	define("HTTP_ESPRESS_TEST_IMAGE", HTTP_EXPRESS_URL . '/kifulab_logo.jpg');
	// Activation Menu and config page
	add_action('admin_menu', 'http_express_add_menu');
	register_activation_hook( __FILE__, 'http_express_install_rules');
	register_deactivation_hook( __FILE__, 'http_express_deinstall_rules');
	
	function http_express_add_menu(){
		if(function_exists('add_submenu_page')){
			add_submenu_page('options-general.php', __('HTTP Express','kifulab_http_express'), __('HTTP Express','kifulab_http_express'), 'manage_options', 'http_express_admin', 'http_express_print_admin');
		}
		
	}
	
	
	// Print settings page
	function http_express_print_admin(){
		require_once dirname(__FILE__) . '/http_express_admin.php';
	}
	
	// Install htaccess rules
	function http_express_install_rules($rules){
	
		$rule = '\.(jpeg|jpg|gif|bmp|png|swf|js|css)$';
		$action = './wp-content/plugins/http-express/do_http_header.php?file=%{REQUEST_FILENAME}';
		
		$rules = array();
		$rules[] = "###Begin HTTP Express";
		$rules[] = "<IfModule mod_rewrite.c>";
		$rules[] = "RewriteEngine On";
		$rules[] = "RewriteBase /";
		$rules[] = "RewriteCond %{REQUEST_FILENAME} -f";
		$rules[] = "RewriteRule " . $rule . ' ' . $action . " [L]";
		$rules[] = "</IfModule>";
		$rules[] = "###End HTTP Express";
		
		http_express_save_rules($rules);
	}
	function http_express_deinstall_rules($rules){
		$rules = false;
		http_express_save_rules($rules);
	}
	function http_express_save_rules($rules){
		$htaccess = ABSPATH . '/.htaccess';
		if(file_exists($htaccess)){	
			$existing_rules = file_get_contents($htaccess);
			if(strpos($existing_rules,"###Begin HTTP Express") !== false && strpos($existing_rules,"###End HTTP Express") !== false){
				$replace = "/###Begin HTTP Express[^#]+###End HTTP Express/i";
				$new_rules = preg_replace($replace,"",$existing_rules);
			}
			else{
				$new_rules = implode("\n",(array)$rules) . "\n" . $existing_rules;
			}
			
		}
		if($h = @fopen($htaccess, "w+")){
			@fwrite($h, $new_rules);
			@fclose($h);
		}
		
	}
	
?>
