=== ACZ Elements for Elementor ===
Contributors: aczolutions
Tags: elementor, widgets, acf, templates, carousel
Requires at least: 6.4
Tested up to: 6.8
Stable tag: 1.2.6
Requires PHP: 7.4
Requires Plugins: elementor, advanced-custom-fields
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Elementor widgets and template utilities for building ACZ-powered WordPress sites.

== Description ==

ACZ Elements for Elementor adds a collection of Elementor widgets and site-building utilities, including post displays, navigation, carousels, taxonomies, breadcrumbs, media galleries, custom meta output, ACF-powered fields, and global template options.

The plugin requires Elementor and Advanced Custom Fields.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/acz-elements` directory, or install the plugin through the WordPress plugins screen.
2. Activate Elementor.
3. Activate Advanced Custom Fields.
4. Activate ACZ Elements for Elementor.
5. Open Appearance > ACZ Theme Options to configure global template display conditions when needed.

== Frequently Asked Questions ==

= Does this plugin require Elementor? =

Yes. Elementor is required because the widgets extend Elementor's widget system.

= Does this plugin require Advanced Custom Fields? =

Yes. Advanced Custom Fields is required for the plugin's ACF-powered controls and theme options.

= Does the plugin load assets from third-party CDNs? =

No. Frontend JavaScript and CSS assets used by the plugin are bundled locally.

== Third Party Libraries ==

Swiper 12.1.3 is bundled locally under `assets/vendor/swiper/` and is licensed under the MIT License. Human-readable source files are included alongside the minified files used at runtime. Upstream source: https://swiperjs.com/

== Changelog ==

= 1.2.6 =
* Added ACZ Nav Menu widget and responsive menu controls.
* Prepared plugin metadata and bundled assets for WordPress.org review.
