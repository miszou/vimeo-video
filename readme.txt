=== Vimeo Video ===
Contributors: miszou
Tags: vimeo, video, custom-post-type
Requires at least: 6.4
Tested up to: 6.9
Stable tag: 0.4.8
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create and display Vimeo video posts in WordPress with featured images, media tags, author assignment, and related videos.

== Description ==

Vimeo Video adds a dedicated Videos section to WordPress so you can manage Vimeo videos as their own content type.

Features include:

* Add Vimeo videos from the WordPress admin
* Store a Vimeo video URL for each video post
* Display a responsive Vimeo player on single video pages
* Organize videos with Media Tags
* Show recommended videos based on shared Media Tags
* Fetch Vimeo thumbnails and set them as featured images
* Manage video libraries with thumbnail and Vimeo URL columns, Media Tag and author filters, and a bulk thumbnail fetch action
* Embed videos and galleries with shortcodes in classic content, widgets, and page builders
* Add dynamic Single Vimeo Video, Vimeo Video Gallery, and Related Vimeo Videos blocks in the block editor
* Control Single Vimeo Video block width, height, and stretch-to-container display
* Assign video posts to different authors
* Use with block themes and classic themes
* Receive plugin updates through Git Updater-compatible metadata

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. A new Videos menu item will appear in your WordPress admin.

== Frequently Asked Questions ==

= How do I add a Vimeo video? =

1. Go to Videos > Add New.
2. Enter a title for your video.
3. Add the Vimeo URL in the Vimeo Video URL box.
4. Optionally add Media Tags, a featured image, excerpt, and author.
5. Publish the video.

= Can I change the author of a video? =

Yes. Video posts support WordPress author assignment. You can change the author from the video editor or Quick Edit when your user account has permission to edit authors.

= Can I organize videos by topic? =

Yes. Use Media Tags to organize videos and power the recommended videos section.

= Will the plugin create thumbnails automatically? =

The plugin can fetch a Vimeo thumbnail and set it as the featured image when Vimeo provides a thumbnail for the video. You can also set or replace the featured image manually in WordPress.

= Can I fetch thumbnails for multiple videos at once? =

Yes. On the Videos admin list screen, select videos, choose the Fetch Vimeo thumbnails bulk action, and apply it. Videos without a Vimeo URL are skipped, and Vimeo access errors are counted as failures.

= Why did thumbnail fetching fail for my video? =

Vimeo may not provide a thumbnail when a video is private, password-protected, deleted, restricted from embedding, or missing thumbnail data. In that case, set the featured image manually in WordPress.

= Does this work with the block editor? =

Yes. The plugin works with the WordPress block editor, includes a single video layout for video pages, and provides dynamic blocks for a single Vimeo video, video galleries, and related videos. The Single Vimeo Video block includes width, height, and stretch controls.

= Does this work with classic themes? =

Yes. The single video page also works with classic themes through the plugin's fallback template. The fallback renders the Vimeo player and related videos through dynamic blocks for compatibility with classic themes such as TheGem. Video posts support the editor Template selector, so you can choose a theme/page template instead of the plugin layout when your theme provides one.

= How can I override the single video page layout? =

Edit a video post and use the Template selector to choose a theme/page template. Theme developers can also add `single-mfvv_video.php` to the active theme or child theme to override all video detail pages.

= Which shortcodes are available? =

Use `[mfvv_video id="123"]` to embed one video, `[mfvv_gallery tag="training" posts_per_page="6" columns="3"]` to show a gallery, and `[mfvv_related_videos id="123"]` to show videos with shared Media Tags.
