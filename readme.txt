=== Vimeo Video CPT ===
Contributors: miszou
Tags: vimeo, video, cpt, custom-post-type
Requires at least: 6.4
Tested up to: 6.9
Stable tag: 0.3
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Registers a Vimeo Video custom post type with Media Tag taxonomy, filterable and searchable via REST API.

== Description ==

Vimeo Video CPT provides a custom post type for managing Vimeo videos in WordPress. It includes:

* Custom post type "Video" (mfvv_video) with archive support
* "Media Tags" taxonomy for organizing and filtering media
* Meta box for storing Vimeo video URLs
* REST API support with filtering by media tags
* Single video template with embedded Vimeo player (works with both block and classic themes)
* Recommended videos slider based on shared media tags
* Auto-fetch Vimeo thumbnails as featured images
* GitHub Updater support for automatic updates

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. A new "Videos" post type will appear in your admin menu

== Frequently Asked Questions ==

= How do I add a Vimeo video? =

1. Go to Videos > Add New
2. Enter a title for your video
3. In the "Vimeo Video URL" meta box, enter your Vimeo URL (e.g., https://vimeo.com/123456789)
4. Publish your video

= Can I use this with the block editor? =

Yes, the plugin supports the block editor and includes REST API exposure.

== Changelog ==

= 0.3 =
* Add classic theme support: single video template now works with non-block themes (e.g. TheGem) via template_include fallback
* Add PHP wrapper template that renders block markup through do_blocks() with get_header/get_footer
* Enqueue wp-block-library styles in classic theme context so core block classes render correctly
* Add CSS custom property fallbacks for spacing so layout works without theme.json
* Add fallback values for all CSS color references (border-color, scrollbar, placeholders)
* Add mfvv-single class to template for plugin-scoped styling alongside theme container

= 0.2 =
* Fix invocation of single-video template

= 0.1 =
* Initial release
* Custom post type "Video" with archive
* Media Tags taxonomy
* Vimeo URL meta box
* Single video template with Vimeo oEmbed player
* Recommended videos slider based on shared media tags
* REST API filtering by media tag
* Auto-fetch Vimeo thumbnail as featured image on URL save
