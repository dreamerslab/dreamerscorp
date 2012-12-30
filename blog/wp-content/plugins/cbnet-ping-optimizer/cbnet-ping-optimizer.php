<?php
/* 
 * Plugin Name:   cbnet Ping Optimizer
 * Plugin URI:    http://www.chipbennett.net/wordpress/plugins/cbnet-ping-optimizer/
 * Description:   Doesn't do anything. Isn't needed. Core WordPress handling of Pings is oh-so-fine!
 * Version:       3.0
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
 * Version 3.0 and later of this Plugin: Copyright (C) 2012 Chip Bennett,
 * Released under the GNU General Public License, version 2.0 (or newer)
 * 
 * Previous versions of this Plugin were derived from MaxBlogPress Ping Optimizer plugin, version 2.2.5, 
 * Copyright (C) 2007 www.maxblogpress.com, released under the GNU General Public License.
 */

/**
 * Add Plugin Settings Page
 */
function cbnetpo_add_settings_page() {
	add_options_page( 'cbnet Ping Optimier', 'cbnet Ping Optimizer', 'manage_options', 'cbnetpo-settings', 'cbnetpo_settings_page' );
}
add_action( 'admin_menu', 'cbnetpo_add_settings_page' );

/** 
 * Define Plugin Settings Page
 */
function cbnetpo_settings_page() {
	?>
<div class="wrap">
<h2>Ping Optimizer</h2>
<p>
Hello there! You may be wondering why you're reading this, instead of looking at Plugin settings. Well, the reason that you're 
not looking at Plugin settings is because <em>there aren't any Plugin settings anymore</em>. In fact, this Plugin doesn't have 
any functionality anymore.
</p>
<p>
Why? Because <em>this Plugin's functionality is no longer needed</em>. In fact, this Plugin's functionality may never have been needed in the first place. But regardless of that, it certainly isn't needed <em>now</em>.</p>

<h3>How Pings Work in WordPress</h3>

<p>
Let me explain why, starting with a walkthrough of how pings work in WordPress, courtesy of @Otto42:
</p>
<ol>
<li>When a post gets created, updated, inserted, modified, etc., eventually, it always goes through <code>wp_insert_post()</code>.</li>
<li><code>wp_insert_post()</code> calls <code>wp_transition_post_status()</code>.</li>
<li><code>wp_transition_post_status()</code> does various actions, but importantly it does this:<br />
<code>do_action("{$new_status}_{$post->post_type}", $post->ID, $post);</code><br />
So the action of <code>new-status</code> and <code>new-post-type</code> is called. In this case,
we're interested in the <em>publish</em> status on the <em>post</em> post-type, so <code>publish_post</code> is the action hook.</li>
<li>In <code>wp-includes/default-filters.php</code>, we have this:<br />
<code>add_action( 'publish_post', '_publish_post_hook', 5, 1 );</code><br />
So that causes <code>publish_post</code> to call <code>_publish_post_hook()</code>.</li>
<li><code>_publish_post_hook()</code>, among other things, adds the <code>'_pingme'</code> post meta to the post, and schedules the <code>do_pings</code> action for a one-time cron call, which will happen on the next page load (because it's hooked to <code>time()</code> for the schedule).</li>
<li>Again in <code>wp-includes/default-filters.php</code>, we have this:<br />
<code>add_action( 'do_pings', 'do_all_pings' );</code><br />
The <code>do_all_pings()</code> function does all the pings for all the posts that are marked as needing them done at the moment.</li>
</ol>
<p>Regarding draft, scheduled (future), and edited posts:</p>
<ol>
<li>Draft posts have a post_status of <code>draft</code>, meaning that <code>wp_transition_post_status()</code> calls <code>do_action()</code> on <code>draft_post</code> rather than on <code>publish_post</code>. Scheduled posts have a post_status of <code>future</code>, meaning that <code>wp_transition_post_status()</code> calls <code>do_action()</code> on <code>future_post</code> rather than on <code>publish_post</code>. Thus, neither draft nor scheduled posts get pings sent for them until
they actually transistion to <code>publish</code>, since that is when the <code>publish_post</code> action will get fired.</li>
<li>Editing posts: editing an existing post <em>will</em> send pings for the post, but <code>do_all_pings()</code> is smart. It calls the <code>pingback()</code> function to send pingbacks to blogs, and that in turn calls the <code>get_pung()</code> function to get the URLs in the post that have already been successfully pinged once before. This is very old code - so old, in fact, that the list of pinged blogs is actually a primary column (<code>pinged</code>) in the <code>wp_posts</code> table.</li>
</ol>

<h3>Why the Ping Optimizer Plugin Isn't Needed</h3>

<p>
So consider again the Plugin description:
</p>
<blockquote><em>
<p>Do you know your WordPress blog pings unnecessarily every time you edit a post? Think how many times you click on "Save and Continue Editing" or "Save" button. Your blog will ping unnecessarily that many times you click on those buttons.</p>

<p>Save your blog from getting tagged as ping spammer by installing this plugin.</p>

<p>After you install cbnet Ping Optimizer:</p>

<ol>
<li>When you create a new post, your blog will ping and notify all the ping services that it has been updated. This encourages search engines and different blog directories/services to index your updated blog properly.</li>

<li>When you edit an existing post, it won't send any unnecessary ping to ping services and saves your blog from getting banned by such services.</li>

<li>When you post a future post by editing the time stamp, it will ping only when your post appears in future. It won't unnecessarily ping many times when you schedule posts as WordPress does by default.</li>
</ol>
</em></blockquote>

<p>It should be obvious that most of that just isn't true anymore.</p>
<ol>
<li>
<blockquote><em>Think how many times you click on "Save and Continue Editing" or "Save" button. Your blog will ping unnecessarily that many times you click on those buttons.</em></blockquote>

<p>As you see above, this isn't true. Saving a draft post does not fire the <code>publish_post</code> action; therefore, no pings are triggered.</p>
</li>
<li>
<blockquote><em>When you create a new post, your blog will ping and notify all the ping services that it has been updated.</em></blockquote>

<p>WordPress core functionality does this already.</p>
</li>
<li>
<blockquote><em>When you edit an existing post, it won't send any unnecessary ping to ping services and saves your blog from getting banned by such services.</em></blockquote>

<p>As you see above, WordPress intelligently fires pings when a post is edited, by keeping track of previous successful pings and not re-pinging them.</p>
</li>
<li>
<blockquote><em>When you post a future post by editing the time stamp, it will ping only when your post appears in future. It won't unnecessarily ping many times when you schedule posts as WordPress does by default.</em></blockquote>

<p>As you see above, scheduling a post fires the <code>future_post</code> action, and <em>not</em> the <code>publish_post</code> action. Thus, until a scheduled post actually transitions to <code>publish</code>, thereby firing the <code>publish_post</code> action, no pings are triggered.</p>
</li>
</ol>

<p>So, none of this Plugin's functionality is needed. You can safely deactivate and uninstall it, with the understanding of why the core WordPress handling of pings works just fine.</p>
</div>
	<?php
}

?>