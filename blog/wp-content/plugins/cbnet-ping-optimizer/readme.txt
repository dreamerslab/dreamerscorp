=== cbnet Ping Optimizer ===
Contributors: chipbennett
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=QP3N9HUSYJPK6
Tags: cbnet, Ping Optimizer
Requires at least: 2.9
Tested up to: 3.5
Stable tag: 3.0

Doesn't do anything. Isn't needed. Core WordPress handling of Pings is oh-so-fine!

== Description ==

Hello there! You may be wondering why you're reading this, instead of looking at Plugin settings. Well, the reason that you're not looking at Plugin settings is because there aren't any Plugin settings anymore. In fact, this Plugin doesn't have any functionality anymore.

Why? Because this Plugin's functionality is no longer needed. In fact, this Plugin's functionality may never have been needed in the first place. But regardless of that, it certainly isn't needed now. 

= How Pings Work in WordPress =

Let me explain why, starting with a walkthrough of how pings work in WordPress, courtesy of @Otto42:

* When a post gets created, updated, inserted, modified, etc., eventually, it always goes through wp_insert_post().
* wp_insert_post() calls wp_transition_post_status().
* wp_transition_post_status() does various actions, but importantly it does this:
* do_action("{$new_status}_{$post->post_type}", $post->ID, $post);
* So the action of new-status and new-post-type is called. In this case, we're interested in the publish status on the post post-type, so publish_post is the action hook.
* In wp-includes/default-filters.php, we have this:
** add_action( 'publish_post', '_publish_post_hook', 5, 1 );
** So that causes publish_post to call _publish_post_hook().
* _publish_post_hook(), among other things, adds the '_pingme' post meta to the post, and schedules the do_pings action for a one-time cron call, which will happen on the next page load (because it's hooked to time() for the schedule).
* Again in wp-includes/default-filters.php, we have this:
** add_action( 'do_pings', 'do_all_pings' );
* The do_all_pings() function does all the pings for all the posts that are marked as needing them done at the moment.

Regarding draft, scheduled (future), and edited posts:

* Draft posts have a post_status of draft, meaning that wp_transition_post_status() calls do_action() on draft_post rather than on publish_post. Scheduled posts have a post_status of future, meaning that wp_transition_post_status() calls do_action() on future_post rather than on publish_post. Thus, neither draft nor scheduled posts get pings sent for them until they actually transistion to publish, since that is when the publish_post action will get fired.
* Editing posts: editing an existing post will send pings for the post, but do_all_pings() is smart. It calls the pingback() function to send pingbacks to blogs, and that in turn calls the get_pung() function to get the URLs in the post that have already been successfully pinged once before. This is very old code - so old, in fact, that the list of pinged blogs is actually a primary column (pinged) in the wp_posts table.

= Why the Ping Optimizer Plugin Isn't Needed =

So consider again the Plugin description:

> Do you know your WordPress blog pings unnecessarily every time you edit a post? Think how many times you click on "Save and Continue Editing" or "Save" button. Your blog will ping unnecessarily that many times you click on those buttons.
> 
> Save your blog from getting tagged as ping spammer by installing this plugin.
> 
> After you install cbnet Ping Optimizer:
> 
> * When you create a new post, your blog will ping and notify all the ping services that it has been updated. This encourages search engines and different blog directories/services to index your updated blog properly.
> * When you edit an existing post, it won't send any unnecessary ping to ping services and saves your blog from getting banned by such services.
> * When you post a future post by editing the time stamp, it will ping only when your post appears in future. It won't unnecessarily ping many times when you schedule posts as WordPress does by default.

It should be obvious that most of that just isn't true anymore.

> Think how many times you click on "Save and Continue Editing" or "Save" button. Your blog will ping unnecessarily that many times you click on those buttons.

As you see above, this isn't true. Saving a draft post does not fire the publish_post action; therefore, no pings are triggered.

> When you create a new post, your blog will ping and notify all the ping services that it has been updated.

WordPress core functionality does this already.

> When you edit an existing post, it won't send any unnecessary ping to ping services and saves your blog from getting banned by such services.

As you see above, WordPress intelligently fires pings when a post is edited, by keeping track of previous successful pings and not re-pinging them.

> When you post a future post by editing the time stamp, it will ping only when your post appears in future. It won't unnecessarily ping many times when you schedule posts as WordPress does by default.

As you see above, scheduling a post fires the future_post action, and not the publish_post action. Thus, until a scheduled post actually transitions to publish, thereby firing the publish_post action, no pings are triggered.

So, none of this Plugin's functionality is needed. You can safely deactivate and uninstall it, with the understanding of why the core WordPress handling of pings works just fine.

== Installation ==

Don't install this Plugin. You don't need it

== Frequently Asked Questions ==

= Why publish a Plugin that isn't needed? =

Originally, I forked this Plugin from another Plugin. That other Plugin, while released under GPL, required users to register and sign up for an email newsletter just to be able to use the Plugin. So, I forked it and removed all of the registration/email subscription code. Essentially, I forked the Plugin to prove a point, rather than to improve the code. 

I did the same thing for a few other Plugins, for the same reason. When I originally released them, I just ripped out the registration/email subscription code, and re-released them. Recently, I have completely re-written all of those Plugins, except for this one. I was able to improve upon the code and functionality for the other Plugins, but not for this one. So, I'm simply updating this Plugin, and releasing it without functionality, and an explanation for why it is not needed.


== Changelog ==

= 3.0 =
* Major update
* Plugin functionality not needed, and therefore removed and replaced with an explanation of why it is not needed.
= 2.3.3 =
* Bugfix update. 
* Fixed the Settings page link in the plugin description, and moved to Plugin Actions list (next to Deactivate|Edit links).
* On settings page, moved Clear Log link to the top of the ping log, and added a record count.
= 2.3.2 =
* Readme.txt update. 
* Updated Donate Link in readme.txt.
= 2.3.1 =
* Fixes bug in which options page settings do not save
= 2.3 =
* Initial Release
* Forked from cbnet Ping Optimizer plugin version 2.2.5


== Upgrade Notice ==

= 3.0 =
Major update. Please visit the Plugin Settings page after updating.
= 2.3.3 =
Bugfix update. Fixed/moved Settings page link. Moved Clear Log link to top of ping log, and added record count. 
= 2.3.2 =
Readme.txt update. Updated Donate Link in readme.txt.
= 2.3.1 =
Bugfix release. Fixes bug in which options page settings do not save.
= 2.3 =
Initial Release. Forked from cbnet Ping Optimizer plugin version 2.2.5.
