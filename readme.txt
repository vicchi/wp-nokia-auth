=== WP Nokia Auth ===
Contributors: vicchi
Donate Link: http://www.vicchi.org/codeage/donate/
Tags: wp-nokia-auth, nokia, maps, places, location, api
Requires at least: 3.3
Tested up to: 3.3.1
Stable tag: 1.0.1

Easily manage your Nokia Location API credentials across all themes and plugins on a site.

== Description ==

This plugin allows you to manage your registered Nokia Location API credentials in a single place and, via a supplied PHP helper class, easily use them across all themes and plugins that use Nokia Location APIs on a WordPress site.

Settings and options include:

1. Managing and maintaining your registered application ID and application token.
1. Sample code incorporating your authentication tokens ready for copying and pasting into your code.
1. A PHP helper class, `WPNokiaAuthHelper` to allow you to easily gain access to the code you need to authenticate with the Nokia Location APIs and incorporate this into the PHP and/or JavaScript code for your WordPress theme or plugin.

== Installation ==

1. You can install WP Nokia Auth automatically from the WordPress admin panel. From the Dashboard, navigate to the *Plugins / Add New* page and search for *"WP Nokia Auth"* and click on the *"Install Now"* link.
1. Or you can install WP Nokia Auth manually. Download the plugin Zip archive and uncompress it. Copy or upload the `wp-nokia-auth` folder to the `wp-content/plugins` folder on your web server.
1. Activate the plugin. From the Dashboard, navigate to Plugins and click on the *"Activate"* link under the entry for WP Nokia Auth.
1. Configure your credentials; from the Dashboard, navigate to the *Settings / WP Nokia Auth* page or click on the *"Settings"* link from the Plugins page on the Dashboard.
1. Enter your registered Nokia Location API application ID and token; from the Dashboard, navigate to the *Settings / WP Nokia Auth* page or click on the *"Settings"* link from the Plugins page on the Dashboard.
1. Click on the *"Save Changes"* button to preserve your chosen settings and options.

== Frequently Asked Questions ==

= How do I get help or support for this plugin? =

In short, very easily. But before you read any further, take a look at [Asking For WordPress Plugin Help And Support Without Tears](http://www.vicchi.org/2012/03/31/asking-for-wordpress-plugin-help-and-support-without-tears/) before firing off a question. In order of preference, you can ask a question on the [WordPress support forum](http://wordpress.org/tags/wp-nokia-auth?forum_id=10); this is by far the best way so that other users can follow the conversation. You can ask me a question on Twitter; I'm [@vicchi](http://twitter.com/vicchi). Or you can drop me an email instead. I can't promise to answer your question but I do promise to answer and do my best to help.

= Is there a web site for this plugin? =

Absolutely. Go to the [WP Nokia Auth home page](http://www.vicchi.org/codeage/wp-nokia-auth/) for the latest information. There's also the official [WordPress plugin repository page](http://wordpress.org/extend/plugins/wp-nokia-auth/) and the [source for the plugin is on GitHub](http://vicchi.github.com/wp-nokia-auth/) as well.

= Nokia Maps? Really? =

Yes. Really. At the time of writing (April 2012) 196 countries, 75M Places, 2.4M map changes a day. That sort of really. All available through a set of developer friendly APIs.

= OK. Nokia Maps. I get it. But why register? =

The Nokia Location APIs work if you don't register. But they work even better and you can do even more if you do register. Take transactional limits. Unregistered users of the Location APIs get 1 million transactions over a lifetime. 1 million sounds a lot but it soon mounts up. Registered users get 2 million transactions. *Per month*. [Plus a whole lot more](http://www.developer.nokia.com/Develop/Maps/Quota/).

= Why are you so pro Nokia Maps? =

A disclaimer is in order. I work for Nokia's Location & Commerce group, that produces Nokia Maps. I see what goes into the map and what gets displayed. I'm very pro Nokia Maps for just this reason.

= I want to amend/hack/augment this plugin; can I do the same? =

Totally; this plugin is licensed under the GNU General Public License v2 (GPLV2). See http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt for the full license terms.

== Screenshots ==

1. WP Nokia Auth Settings And Options: Clean Installation
1. WP Nokia Auth Settings And Options: Configured Installation

== Changelog ==

The current version is 1.0.1 (2012.04.19)

= 1.0.1 =
Summary: Minor fixes to PHP base class.
Fixed: An issue with an old version of WP_PluginBase, the PHP class which WP Nokia Auth extends.

= 1.0 =
* First version of WP Nokia Auth released

== Upgrade Notice ==

= 1.0.1 =
This is the 2nd version of WP Nokia Auth; fixing an issue with the PHP base class that the code extends.

= 1.0 =
* This is the first version of WP Nokia Auth
