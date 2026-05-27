# Changelog

## Unreleased

- Preserve TheGem's single-page wrapper for default video pages so TheGem Page Options, including Content Layout settings, can control the surrounding layout.

## 0.4.10

- List published TheGem Template Builder templates in the Vimeo Video Template selector.
- Render selected TheGem templates on individual video pages.
- Add Videos list bulk actions for applying or clearing video page templates across multiple videos.

## 0.4.9

- Automated release.

## 0.4.8

- Add page attributes support to video posts so the editor can show the Template selector.
- Allow classic-theme video posts to use selected theme/page templates instead of always forcing the plugin fallback template.
- Expose the plugin video layout as an explicit selectable template option.
- Resolve selected theme/page templates correctly on video detail pages.

## 0.4.7

- Fix single video pages in classic themes such as TheGem by rendering the Vimeo player and recommendations with dynamic blocks instead of dynamic PHP block patterns.
- Improve responsive player styling for the dynamic video block on single video pages.

## 0.4.6

- Automated release.

## 0.4.5

- Automated release.

## Unreleased

- Add shortcode support for single videos, galleries, and related videos.
- Add dynamic Gutenberg blocks for Single Vimeo Video, Vimeo Video Gallery, and Related Vimeo Videos.
- Add width, height, and stretch controls to the Single Vimeo Video block.

## 0.4.4

- Add Videos admin list management improvements: thumbnail and Vimeo URL columns, Media Tag and author filters, sortable Vimeo URL ordering, and a bulk Vimeo thumbnail fetch action.

## 0.4.3

- Fix the bulk thumbnail notice format string so it renders without PHP warnings or fatal errors.

## 0.4.2

- Improve thumbnail fetch messages for private, restricted, and missing-thumbnail Vimeo videos.
- Fix manual thumbnail refresh incorrectly clearing the featured image in the block editor.
- Fix automatic thumbnail fetch when saving the Vimeo URL.
- Return more detailed thumbnail fetch errors.

## 0.4.1

- Fix the Vimeo URL field so the fetch thumbnail button reads the correct URL.

## 0.4

- Add a Fetch Vimeo Thumbnail button to manually retrieve and replace the featured image.
- Support thumbnail fetching without saving the post first.

## 0.3

- Add classic theme support for single video pages.
- Improve single video layout styling and theme compatibility.

## 0.2

- Fix single video template loading.

## 0.1

- Initial release.
