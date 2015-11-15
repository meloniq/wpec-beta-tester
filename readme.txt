=== WP eCommerce Beta Tester ===
Contributors: meloniq, JustinSainton, garyc40
Donate link: http://blog.meloniq.net/donate/
Plugin URI: https://wordpress.org/plugins/wp-e-commerce/
Tags: WP e-Commerce, wpec, wpsc, dev, github, beta, beta tester
Requires at least: 4.1
Tested up to: 4.3.1
Stable tag: 1.1

Easily run the bleeding edge version of WP eCommerce right from GitHub.

== Description ==

**This plugin is meant for testing and development purposes only. You should under no circumstances run this on a production website.**

Easily run the bleeding edge version of [WP eCommerce](https://wordpress.org/plugins/wp-e-commerce/) right from GitHub.

= Special thanks to: =

* Joachim Kudish & Radish Concepts for the [WordPress GitHub Plugin Updater](https://github.com/radishconcepts/WordPress-GitHub-Plugin-Updater "WordPress GitHub Plugin Updater") class

* Community of [WP eCommerce](https://wordpress.org/plugins/wp-e-commerce/) for this awesome plugin.

== Frequently Asked Questions ==

= Why am I not getting update notifications when there were new commits? =

Just like with any plugin, this will not check for updates on every admin page load unless you explicetely tell it to.
You can do this by clicking the "Check Again" button from the WordPress updates screen or you can set the WP_GITHUB_FORCE_UPDATE as true in your wp-config.php file.


== Installation ==

1. Upload the folder 'wpec-beta-tester' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to "Tools->WPeC Beta Testing" menu and fill settings.
4. Then go to "Dashboard->Updates" menu and see if there is new updates available.


== Changelog ==

= 1.1 =

* Updated WP_GitHub_Updater class.
* Minor corrections (docs, urls, code styling).

= 1.0 =

* Initial release.


== Screenshots ==

1. Plugin configuration page
2. Available updates
