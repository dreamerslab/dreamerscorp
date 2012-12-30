<?php
/*
 * @package WordPress
 * @subpackage Dreamerscorp
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
    <link rel="SHORTCUT ICON" href="http://dreamerscorp.com/favicon.ico" />
    <title><?php wp_title('&laquo;', true, 'right'); ?> <?php bloginfo('name'); ?></title>
    <link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> RSS Feed" href="<?php bloginfo('rss2_url'); ?>" />
    <link rel="alternate" type="application/atom+xml" title="<?php bloginfo('name'); ?> Atom Feed" href="<?php bloginfo('atom_url'); ?>" />
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />
    <link rel="stylesheet" href="/blog/wp-content/themes/dreamerscorp/style.css?v=20121230" type="text/css" media="screen" />
    <style type="text/css">
    @import url("http://dreamerscorp.com/css/overlay.css");
    </style>
    <!--[if lt IE 9]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
    <?php if ( is_singular() ) wp_enqueue_script( 'comment-reply' ); ?>
    <?php wp_head(); ?>
  </head>
<body>
  <div id="page">
    <div id="header">
      <header>
        <h1 id="headerimg-wrap"><a href="<?php echo get_option('home'); ?>" title="Dreamers Corp.- web portal, SNS apps, and blog widgets." class="headerimg"><?php bloginfo('name'); ?> <?php bloginfo('description'); ?></a></h1>
      </header>
      <div class="content-block-header">
        <h3 class="content-block-header-logo">Blog</h3>
        <nav>
          <a href="http://dreamerscorp.com/blog/" class="nav-selected">
            Blog
          </a>
          <a href="http://dreamerscorp.com/#contact" class="nav-link">
            Contact
          </a>
          <a href="http://dreamerscorp.com/#recruit" class="nav-link">
            Recruit
          </a>
          <a href="http://dreamerscorp.com/" class="nav-link">
            About
          </a>
        </nav>
      </div> <!-- .content_block_header -->
    </div> <!-- #header -->
<hr />
