=== TwitterLink Comments ===
Contributors: commentluv
Donate link:http://comluv.com/about/donate
Tags: twitter, comment form, follow me, twitterlink, twitlinkid
Requires at least: 3.0
Tested up to: 3.7
Stable tag: 1.32
	
Allow your commentators to include their Twitter username along with their comment so a follow-me link can be displayed with their details on the list of comments.

== Description ==
[Upgrade to TwitterLink Pro](http://www.commentluv.com "Upgrade to TwitterLink Pro")

TwitterLink Pro is part of CommentLuv Premium and has even more amazing features that can bring even more traffic and comments to your blog by giving you the ability to fight spam, add keywords, integrate twitterlink, add a top commentators widget, social enticements and by having it installed on your site, you get advanced backlink features on EVERY CommentLuv blog when you comment (there are 10's of thousands of CommentLuv blogs)

[About](http://www.commentluv.com/buy-commentluv-premium/what-is-twitterlink/ "About") | [Features](http://www.commentluv.com "Features") | [Pricing](http://www.commentluv.com "Pricing")

This plugin will add an extra field to your comment form so a user can add their Twitter username. A user configured 'follow me' link is then displayed along with their details on all their comments.

The extra field and 'follow me' link can be configured using the settings page without ever needing to modify any template files. (unless you want to)

The blog owner has full control over the classes, HTML, link and message by using the settings page. (All options are passed through KSES using only basic HTML tags for secruity on Mu installs)

Here is how the plugin works

[youtube http://www.youtube.com/watch?v=e5u4xQdxgQ8]

big thanks to the following beta testers
[Jon Barry](http://jonbarry.co.uk/)
[Mark Hughes](http://www.funkysocialmedia.com)

== Installation ==

Wordpress : Extract the zip file and just drop the contents in the `wp-content/plugins/` directory of your WordPress installation and then activate the Plugin from Plugins page.

WordpressMu : Same as above (do not place in mu-plugins)

== Frequently Asked Questions ==

= Does this plugin add any database tables? =

Yes. One table called `(yourprefix)_wptwitipid` is added

= How do I uninstall this plugin and the database table it creates? =

Just deactivate it and use the `delete` link on the plugin page to remove the saved options and database table. (please tick the box for table removal in the settings)

= I already use wp-twitip-id, is this compatible? =

Your exisiting db table for wp-twitip-id will be used. You may need to check your settings for automatic setting and adjust your link output changes you made in your template file.

= Will this plugin work with Disqus/Intense Debate/js-kit? =

This will only work with the standard WP comment form
                                                                                                                                                     
= How do I add the extra field in the profile page for the twitter username for registered users? =

see [http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields] (http://justintadlock.com/archives/2009/09/10/adding-and-using-custom-user-profile-fields)

= I am having a problem getting it to work =

You can submit a support ticket at http://comluv.com

== Screenshots ==
1. settings page

2. in use

== Upgrade Notice ==

= 1.33 =

wp 3.8 compatibility

= 1.31 =

wp 3.7 compatibility 

== ChangeLog ==

= 1.33 =

* updated : removed fancybox 

= 1.32 =

* updated : wp3.8 compatibility

= 1.3.1 =

* updated : wp3.7 compatibility

= 1.3 =
* complete rewrite
* added : can use autoinsert of field if using wp3 theme
* updated : improved validation of twitter name on comment submission
* updated : more control over twitter field class

= 1.27 = 
* fixed : use profile value or comment form value had a bug. 

= 1.25 =
* added : <span> with a class is now allowed to html before link in settings page.
This will let you add `<span class="twitip">` to the 'html before link' and `</span>` to html after link for styling the added link better with css.

= 1.2 =
* 11 November 2010 - Added ability to use profile field value for twittername when no adding a twitter field automatically. (thanks @opinionhead)
* added : Belorussion language
* updated : settings page updated

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