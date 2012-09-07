=== HTTP Express ===
Contributors: kifulab, fulippo, kilotto
Tags: HTTP, headers, optimization
Requires at least: 2.8
Tested up to: 2.9

HTTP Express enables a php based HTTP proxy to add correct HTTP headers to images, css and javascripts. Useful if you want to improve cache usage and reduce bandwidth impact. Requires Apache with mod_rewrite enabled to work.

== Description ==

HTTP Express is a HTTP proxy that catches all images, css, javascript and flash movies on the fly to add correct HTTP headers. It adds the following headers:

* Expires
* Cache-control
* Pragma
* Last-modified

All files under your wordpress installation will be automatically processed to be served with the correct HTTP headers.

HTTP Express need Apache with mod_rewrite

== Installation ==

Upload the HTTP Express plugin to your blog, Activate it. Remember that if your .htaccess file has no writing permission you should add some code lines manually (check the admin page of the plugin for details)


== Changelog ==

= 1.0.2 =

* Fixed issue with translations

= 1.0.1 =

* Minor bug fixed

= 1.0 =

* First Release
