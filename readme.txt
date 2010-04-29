=== TwitterLink Comments ===
Contributors: @commentluv (concept & coding) Gianni (translation and localization help)
Donate link:http://comluv.com/about/donate
Tags: twitter, comment form, follow me
Requires at least: 2.8
Tested up to: 2.8.4
Stable tag: 1.1
	
Allow your commentators to include their Twitter username along with their comment so a follow-me link can be displayed with their details on the list of comments.

== Description ==

This plugin will add an extra field to your comment form so a user can add their Twitter username. A user configured 'follow me' link is then displayed along with their details on all their comments.

The extra field and 'follow me' link can be configured using the settings page without ever needing to modify any template files. (unless you want to)

The blog owner has full control over the classes, HTML, link and message by using the settings page. (All options are passed through KSES using only basic HTML tags for secruity on Mu installs)

== Installation ==

Wordpress : Extract the zip file and just drop the contents in the `wp-content/plugins/` directory of your WordPress installation and then activate the Plugin from Plugins page.

WordpressMu : Same as above (do not place in mu-plugins)

== Frequently Asked Questions ==

= Does this plugin add any database tables? =

Yes. One table called `(yourprefix)_wptwitipid` is added

= How do I uninstall this plugin and the database table it creates? =

Just deactivate it and use the `delete` link on the plugin page to remove the saved options and database table.

= I already use wp-twitip-id, is this compatible? =

Your exisiting db table for wp-twitip-id will be used. You may need to check your settings for automatic setting and adjust your link output changes you made in your template file.

= Will this plugin work with Disqus/Intense Debate/js-kit? =

This will only work with the standard WP comment form

= I want to create the follow link myself, can I get the stored username from the comment via php? =

Yes, you can access the twitter username (if it exists) in $comment within the loop. Use `$username = $comment->twitlinkid` to retreive the username.

= I am having a problem getting it to work =

You can submit a support ticket at http://comluv.com

== Screenshots ==
1. settings page

2. in use

== ChangeLog ==

= 1.1 =
* 21 April 2010 - Allow a class to be used for the link (compatibility with twitter anywhere)
* Remove comment author email from table if comment is spammed manually or by the system. (thanks @travelwriting)
* Removed hard coded line break in input field output (thanks Yuriy)
* added French translation by Didier http://www.wptrads.fr
* added Russian translation by Yuriy Piskun http://yoyurec.in.ua
* added Swedish translation by Stefan Ljungwall http://minablandadeinfall.se/

= 1.0.1 =
* 09 Nov 2009 - Added German translation by http://martinwaiss.com/

= 1.0 =
* 27 Sep 2009 - First version.

== Configuration ==

* Add twitter field automatically -> Choose NO if you wish to add the field to your comments.php template yourself. (use `name="atf_twitter_id"`)

* CSS classes -> The name of the class to use for the DIV and INPUT. Only alphanumeric and hyphen characters allowed

* Field Description -> Enter your chosen HTML for before and after the description (only certain HTML tags will be allowed)

* Twitter link position -> Choose where you want the link to be displayed in each comment within the list of comments

* Link Format -> You can configure how the link is shown here. [username] will be replaced by the Twitter username of the user. Choose to open the link in a new window and if the link uses nofollow or not

* Link Format -> manual : Use this if you want to control the position and format of the follow link. use

* `if($comment->twitlinkid){ // some code that uses $comment->twitlinkid }`

* example :

* `$twitterusername = $comment->twitlinkid; echo '<a href="http://twitter.com/'.$twitterusername.'">@'.$twitterusername.'</a>';` 