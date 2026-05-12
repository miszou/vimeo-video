# Vimeo Video CPT

Developer documentation for the Vimeo Video CPT WordPress plugin.

For WordPress user-facing installation, usage, and FAQ information, see [`readme.txt`](readme.txt). Release history lives in [`CHANGELOG.md`](CHANGELOG.md).

## Plugin Overview

This plugin registers a Vimeo-focused custom post type and supporting taxonomy, templates, patterns, assets, metadata, and admin tools.

Core identifiers:

- Custom post type: `mfvv_video`
- Taxonomy: `mfvv_media_tag`
- Vimeo URL meta key: `mfvv_vimeo_url`
- Function prefix: `mfvv_`
- Class prefix: `MFVV_`

## File Structure

```text
mf-vimeo-video.php                 Main plugin file: CPT, taxonomy, meta, save hooks, thumbnail fetch, admin list tools, REST filters
includes/class-mfvv-template.php   Template and pattern registration, template injection, CSS enqueueing
templates/single-mfvv_video.html   Block template markup for single video pages
templates/single-mfvv_video.php    Classic theme PHP wrapper for the block template
patterns/vimeo-player.php          Dynamic Vimeo oEmbed player pattern
patterns/recommended-videos.php    Related videos query and slider pattern
assets/css/single-video.css        Single video player and recommended slider styles
assets/js/admin.js                 Admin thumbnail-fetch button behavior
uninstall.php                      Cleanup on uninstall
```

## Architecture Notes

- Templates and patterns are registered by `MFVV_Template` rather than by the active theme.
- The single video template uses PHP block patterns for dynamic rendering.
- Template injection uses `get_block_templates` and `get_block_file_template` filters for WordPress 6.4+ compatibility.
- Classic themes are supported through a PHP wrapper template that renders the block markup with `do_blocks()` and uses `get_header()` / `get_footer()`.
- Vimeo oEmbed is used for player rendering and thumbnail discovery.
- Vimeo unlisted/hash URLs are supported when Vimeo exposes them through oEmbed.
- Automatic thumbnail fetches depend on Vimeo returning `thumbnail_url` in the oEmbed response.

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
