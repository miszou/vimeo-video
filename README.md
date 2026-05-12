# Vimeo Video WordPress Plugin

This plugin gives your WordPress site a simple Videos area for sharing Vimeo videos.

Instead of mixing videos into regular posts or pages, you can add each Vimeo video as its own item, give it a title and description, add a featured image, organize it with Media Tags, and show related videos on your site.

## What you can do

- Add Vimeo videos from the WordPress admin.
- Show each video on its own page with a responsive Vimeo player.
- Organize videos with Media Tags, such as topics, series, or lessons.
- Show recommended videos that share the same Media Tags.
- Fetch a thumbnail from Vimeo and use it as the featured image when Vimeo allows it.
- Choose or replace a featured image yourself at any time.
- Display videos in posts, pages, widgets, or page builders.
- Add a single video, a video gallery, or related videos in the block editor.
- Manage larger video libraries with helpful columns and filters in the Videos list.

## Adding a video

1. In your WordPress admin, go to **Videos**.
2. Choose **Add New**.
3. Add the video title.
4. Paste the Vimeo link into the Vimeo Video URL box.
5. Add a description, Media Tags, a featured image, and an author if needed.
6. Publish the video.

After publishing, WordPress creates a video page for that item.

## Organizing videos

Use **Media Tags** to group videos by topic, course, event, speaker, campaign, or any other category that makes sense for your site.

Media Tags also help the plugin choose related videos, so visitors can easily keep watching similar content.

## Thumbnails

The plugin can try to fetch a thumbnail from Vimeo and set it as the featured image.

This works when Vimeo makes a thumbnail available. If Vimeo does not provide one, you can still upload or choose your own featured image in WordPress.

You can fetch thumbnails one video at a time, or select several videos from the Videos list and fetch thumbnails in bulk.

## Using videos on your site

In the block editor, you can add blocks for:

- A single Vimeo video
- A Vimeo video gallery
- Related Vimeo videos

The single video block includes simple display controls for the player size.

If you use classic content, widgets, or a page builder, you can also paste these into your content:

```text
<<<<<<< HEAD
mf-vimeo-video.php                 Main plugin file: CPT, taxonomy, meta, save hooks, thumbnail fetch, admin list tools, REST filters
includes/class-mfvv-template.php   Template and pattern registration, template injection, CSS enqueueing
templates/single-mfvv_video.html   Block template markup for single video pages
templates/single-mfvv_video.php    Classic theme PHP wrapper for the block template
patterns/vimeo-player.php          Dynamic Vimeo oEmbed player pattern
patterns/recommended-videos.php    Related videos query and slider pattern
assets/css/single-video.css        Single video player and recommended slider styles
assets/js/admin.js                 Admin thumbnail-fetch button behavior
uninstall.php                      Cleanup on uninstall
=======
[mfvv_video id="123"]
[mfvv_gallery tag="training" posts_per_page="6" columns="3"]
[mfvv_related_videos id="123"]
>>>>>>> eb810c5 (feat(core): add shortcodes and gutenberg blocks)
```

Replace the example number or tag with the video or Media Tag you want to show.

## Good to know

<<<<<<< HEAD
## REST API Behavior

The plugin exposes video posts through the WordPress REST API because the custom post type is registered with `show_in_rest`.

The Vimeo URL meta field is registered for REST access:

```php
register_post_meta('mfvv_video', 'mfvv_vimeo_url', ...)
```

The video collection supports filtering by media tag slug using the custom query parameter:

```text
/wp-json/wp/v2/videos?mfvv_media_tag[]=example-tag
```

## Admin Video Management

The Videos list table includes management columns and filters for larger libraries:

- Thumbnail preview column
- Vimeo URL column with outbound links and sortable meta-value ordering
- Media Tag admin column and Media Tag filter
- Author filter
- Bulk action to fetch Vimeo thumbnails for selected videos

The bulk thumbnail action uses the same `mfvv_fetch_vimeo_thumbnail()` helper as the editor button and reports updated, failed, and skipped counts after it completes.

## Thumbnail Handling

Thumbnail fetching is implemented in `mfvv_fetch_vimeo_thumbnail()`.

Behavior:

1. Request Vimeo oEmbed JSON for the stored Vimeo URL.
2. Read `thumbnail_url` from the response.
3. Download the image with `download_url()`.
4. Sideload it into the media library with `media_handle_sideload()`.
5. Set it as the featured image.

Thumbnail fetch failures return detailed `WP_Error` messages and are logged when `WP_DEBUG` is enabled.

## Development Workflow

- Keep all public functions/classes prefixed with `mfvv_` / `MFVV_`.
- Update `readme.txt` for user-facing feature changes and changelog entries.
- Run PHP linting when PHP is available:

```bash
php -l mf-vimeo-video.php
```

## Release Workflow

Releases are automated by `.github/workflows/release.yml`.

On a push or merge to `main`, the workflow:

1. Checks out the repository with full tag history.
2. Sets up PHP 8.2 and runs `parallel-lint` across the plugin.
3. Verifies the plugin header version and `readme.txt` stable tag currently match.
4. Bumps the patch version automatically, unless a specific version is provided through manual `workflow_dispatch`.
5. Updates the plugin header `Version`, `readme.txt` `Stable tag`, and adds a `CHANGELOG.md` placeholder if needed.
6. Commits the version bump back to `main`.
7. Creates and pushes an annotated `vX.Y.Z` tag.
8. Builds a distributable plugin zip.
9. Creates a GitHub release with generated release notes and the zip asset.

Manual releases can be started from the GitHub Actions UI with an explicit `MAJOR.MINOR.PATCH` version.

When preparing changes for release:

- Keep the current plugin header version and `readme.txt` stable tag in sync.
- Add meaningful user-facing changelog notes to `CHANGELOG.md` before merging to `main` when possible.
- Confirm user-facing documentation only remains in `readme.txt`.
- Keep developer-only implementation notes in this `README.md`.
=======
- The plugin works with both block themes and classic themes.
- Videos can be assigned to different WordPress authors.
- Private, password-protected, deleted, or restricted Vimeo videos may not show a thumbnail or player correctly.
- If Vimeo does not allow a thumbnail to be fetched, set the featured image manually.
>>>>>>> eb810c5 (feat(core): add shortcodes and gutenberg blocks)
