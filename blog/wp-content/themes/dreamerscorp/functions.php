<?php
/*
 * @package WordPress
 * @subpackage Dreamerscorp
 */
if ( function_exists('register_sidebar') )
	register_sidebar(array(
		'before_widget' => '<div id="%1$s" class="trans-bg1 sidebar-custom-widget %2$s"><div class="tabs-panel-wrap">',
		'after_widget' => '</div></div>',
		'before_title' => '<h2>',
		'after_title' => '</h2><div class="sidebar-logos" ></div>',
	));

?>
