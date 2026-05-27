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
[mfvv_video id="123"]
[mfvv_gallery tag="training" posts_per_page="6" columns="3"]
[mfvv_related_videos id="123"]
```

Replace the example number or tag with the video or Media Tag you want to show.

## Overriding the video page template

Video posts support WordPress template selection in classic themes. Edit a video post and use the **Template** selector to choose a theme/page template instead of the plugin's default video layout.

When TheGem is active, the plugin keeps TheGem's single-page wrapper in control for the default video template, so **TheGem → Page Options → Content Layout** settings such as built-in layout, template-builder layout, content padding, and sidebar options can override the surrounding page layout while the video player/recommendations remain in the content area. Published templates from **TheGem → Templates Builder** are also listed in the video **Template** selector as **TheGem: Template Name** options.

To apply the same template to multiple videos, go to **Videos**, select the videos, choose **Set template: Template Name** from the bulk actions dropdown, and click **Apply**. Use **Set template: Default** to clear the selected template.

Theme developers can also override all video pages by adding `single-mfvv_video.php` to the active theme or child theme.

## Good to know

- The plugin works with both block themes and classic themes, including classic themes such as TheGem.
- Video posts support the editor Template selector, and TheGem Page Options can override the surrounding content layout for the default video template.
- Videos can be assigned to different WordPress authors.
- Private, password-protected, deleted, or restricted Vimeo videos may not show a thumbnail or player correctly.
- If Vimeo does not allow a thumbnail to be fetched, set the featured image manually.
