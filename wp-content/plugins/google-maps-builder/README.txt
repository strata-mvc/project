=== Google Maps Builder ===
Contributors: dlocc, wordimpress
Donate link: http://wordimpress.com/
Tags: google maps, google map, google map widget, google map shortcode, maps, map, wp map, wp google maps, google maps directions, google maps builder, google maps plugin, google places, google places api, google maps api, google places reviews
Requires at least: 3.6
Tested up to: 3.9.1
Stable tag: 1.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

One Google Maps plugin to rule them all. Google Maps Builder is intuitive, sleek, powerful and easy to use. Forget the rest, use the best.

== Description ==

Google Maps Builder isn't just another Google Maps plugin. It's built from the ground up to be the easiest, most intuitive and fastest Google Maps plugin for WordPress. Visually build powerful customized Google Maps to use on your WordPress site quickly and easily without ever having to touch a bit of code.

= Plugin Highlights: =

* **Google Places API integration** - Display nearby business locations and points of interest complete with ratings, custom marker icon
* **Snazzy Maps integration** - Create truly unique Google Map themes that look great with any design powered by [Snazzy Maps](http://snazzymaps.com/).
* **Unique Marker Icons** - The only plugin with [Map Icons](map-icons.com) integration; set icon and marker colors for truly unique markers
* **Intuitive UI** that seamlessly integrates with WordPress' - no eye sores or outdated interfaces here
* **Small Footprint** - GMB does not create any new database tables, not even one
* **Optimized** - All scripts and styles are optimized and packaged with Grunt
* **No notices or warnings** We developed this plugins in debug mode. This results in high quality plugins with no errors, warnings or notices.

= Marker Creation =

Google Maps builder features a simple **"Point and Click" marker creation system**. As well, you can add markers using an intuitive Google autocomplete search field. As well, **Bulk edit marker data ** using meta fields attached to each marker's content.

= Map Themes =

Want to add some pazazz to your maps? [Snazzy Maps](http://snazzymaps.com/) themes are baked right in to Google Map Builder. This means your maps can stand out, fit into any design, and look unique and intriguing.

= Granular Map Control =

Fine tune your Google Maps with full control over settings for street view, zooming, panning, dragging, and more. Set defaults for each controls so each new map you create is just the way you like it.

= Actively Developed and Supported =

This plugin is currently being actively developed, which means consistent improvements and enhancements. As well, we won't shy away from bug fixes and further code refractoring and optimization.

== Installation ==

This section describes how to install the plugin and get it working.

= Using The WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'Google Maps Builder'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard

= Uploading in WordPress Dashboard =

1. Navigate to the 'Add New' in the plugins dashboard
2. Navigate to the 'Upload' area
3. Select `google-maps-builder.zip` from your computer
4. Click 'Install Now'
5. Activate the plugin in the Plugin dashboard

= Using FTP =

1. Download `google-maps-builder.zip`
2. Extract the `google-maps-builder` directory to your computer
3. Upload the `google-maps-builder` directory to the `/wp-content/plugins/` directory
4. Activate the plugin in the Plugin dashboard

== Frequently Asked Questions ==

= What sets this plugin apart from all the other Google Maps plugins for WordPress? =

There are a number features and functionality that set Google Maps Builder apart from the many WordPress Google Maps plugins. For starters, we promise this plugin will always have a light footprint. No extra tables or unnecessary overhead. Furthermore, the plugin is built from the ground up to be both easy and fun to use.

We have placed extra care and attention on the map creation process and are always looking to improve the UI with enhancements. It's our goal to integrate the plugin with the native WordPress admin UI without adding any distracting visuals. Finally, there are a number of additional features such as built in integration with Google Places API, Maps Icons and Snazzy Maps.

= Do I need a Google Places API Key to use this plugin? =

No. You do not need a Google Places API plugin to use this plugin.

= Does this plugin create any new database tables? =

Unlike many other Google Maps plugins, Google Maps Builder does not create a single new table in your WordPress database. There is no added database overhead or foreign MySQL queries. It's our guarantee that this plugin will never leave an orphaned table in your WordPress database.

= Where can I find the shortcodes for each map I create? =

You can find the shortcodes for each map on the post listing screen, within the post itself in the shortcode metabox (coming soon). Also coming soon: Map widget and TinyMCE button to include shortcode.

= What the heck is a shortcode and how do I use it? =

Google Maps Builder works by creating a plugin specific [WordPress shortcode](http://codex.wordpress.org/Shortcode). Basically, you can copy the shortcode for a specific map and enter in into a widget or directly within content. As well, you can use the WordPress [do_shortcode()](http://codex.wordpress.org/Function_Reference/do_shortcode) function to use it within your theme template files and even plugins.

= Does this plugin include a widget for displaying maps? =

Soon! For now, you can use the shortcode in the text widget. Soon there will be a Google Maps Builder Widget.

= How do I report a bug? =

We always welcome your feedback for improvements or if you have uncovered a bug. To report a bug please use the WordPress.org support forum.

= Who is behind this plugin? =

The main developer of this plugin is Devin Walker. He lives in San Diego, California and loves all things WordPress. To find out more information about him please visit [his website.](http://imdev.in)

== Roadmap ==

Here is what we are working on for future releases:

* Maps Widget - Build in a widget to output Google Maps
* Custom Map Marker Images - Ability to upload your own map marker images
* Map Icons: Fix maps icon overlap issue when they are bunched in an area (zIndex issue)
* Admin JS i18n - Please contact us at info@wordimpress.com to volunteer for translating!
* Public JS i18n - Please contact us at info@wordimpress.com to volunteer for translating!
* Google Places - Add the ability to remove place information
* Google Places - Pagination for markers, the ability to set number of markers
* Google Places - Customize the individual place markers
* Markers - Add the ability to drag markers to new positions (lat, lng) in builder.
* Info Window - Add a WYSIWYG editor? Possible complications with this.

Bug Fixes

* Info Window - FOUC: Investigate why sometimes pointer tip of info window flashes before it opens (mainly Chrome)
* Chrome - Look into while map tiles have strange lines in between
* Firefox - Clicking on a marker to open the same info window creates content overflow

== Screenshots ==

1. **Google Map Builder** - A view of the single map view in the WordPress admin panel. Notice the autocomplete search field and "Drop a Marker" button.

2. **Editable Marker** - Customize the content of the map markers directly in the builder. Built to mock Google's own Maps Engine.

3. **Custom Markers** - Configure a marker to fit your location. Easily adjust the marker, icon and color.

4. **Frontend View** - A view the a map on the frontend of a WordPress site using the TwentyTwelve theme. This map displays various Google Places.

5. **Settings Panel** - Adjust the various plugin settings using a UI that is built using WordPress' own styles.

== Changelog ==

= 1.0.1 =
* New: Added a custom meta field to the Google Map single post screen that outputs the post's shortcode so it's more easily accessible. Before you could only access the shortcode via the Google Maps post listing page.
* Updated readme.txt file with more information about the plugin, fixed several formatting errors and typos.
* Fixed: Activation error "PHP Strict Standards:  call_user_func_array() expects parameter 1 to be a valid callback, non-static method Google_Maps_Builder::activate() should not be called statically in ..." - Thanks Jon Brown!

= 1.0.0 =
* Plugin released. Yay!
