=== Plugin Name ===
Contributors: georgestephanis
Donate link: http://www.charitywater.org/donate/
Tags: google, tag manager, tag management, analytics
Requires at least: 2.7
Tested up to: 3.5
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Google Tag Manager plugin adds a field to the existing General Settings page for the ID and outputs the javascript in the front-end footer.

== Description ==

[You can sign up for a Google Tag Manager account here.](https://www.google.com/tagmanager/ "Google Tag Manager")

This plugin makes it even easier to use Google Tag Manager, adding all the code itself -- all you need to do is provide the Account ID!

[youtube http://www.youtube.com/watch?v=KRvbFpeZ11Y]

== Installation ==

1. Upload `google-tag-manager.php` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to `Settings` > `General` and set the ID from your Google Tag Manager account.

== Frequently Asked Questions ==

= Why isn't the output displaying? =

Two possibilities: First, you haven't yet specified the ID in the admin panel, or second, your theme is missing a `<?php wp_footer(); ?>` call.

== Changelog ==

= 1.0 =
* Initial Public Release

== Upgrade Notice ==

= 1.0 =
Initial Public Release
