<?php
/*
Plugin Name: Vimeo Video CPT
Plugin URI: https://github.com/miszou/vimeo-video
GitHub Plugin URI: miszou/vimeo-video
Description: Registers a Vimeo Video custom post type with Media Tag taxonomy, filterable and searchable via REST API.
Version: 0.4.11
Author: miszou
Text Domain: mf-vimeo-video
*/

if (!defined("ABSPATH")) {
    exit();
}

require_once __DIR__ . "/includes/class-mfvv-render.php";
require_once __DIR__ . "/includes/class-mfvv-template.php";
require_once __DIR__ . "/includes/class-mfvv-shortcodes.php";
require_once __DIR__ . "/includes/class-mfvv-blocks.php";

MFVV_Template::init();
MFVV_Shortcodes::init();
MFVV_Blocks::init();

// Flush rewrite rules on activation so video URLs work immediately
function mfvv_activate()
{
    mfvv_register_media_tag_taxonomy();
    mfvv_register_video_cpt();
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, "mfvv_activate");

// Register shared taxonomy (prefixed slug to avoid conflicts)
function mfvv_register_media_tag_taxonomy()
{
    $labels = [
        "name" => __("Media Tags", "mf-vimeo-video"),
        "singular_name" => __("Media Tag", "mf-vimeo-video"),
        "search_items" => __("Search Media Tags", "mf-vimeo-video"),
        "all_items" => __("All Media Tags", "mf-vimeo-video"),
        "edit_item" => __("Edit Media Tag", "mf-vimeo-video"),
        "add_new_item" => __("Add New Media Tag", "mf-vimeo-video"),
    ];

    register_taxonomy(
        "mfvv_media_tag",
        ["post", "mfvv_video"],
        [
            "labels" => $labels,
            "hierarchical" => false,
            "public" => true,
            "rewrite" => ["slug" => "media-tag"],
            "show_admin_column" => true,
            "show_in_rest" => true,
        ],
    );
}
add_action("init", "mfvv_register_media_tag_taxonomy");

// Register Video CPT (prefixed slug to avoid conflicts)
function mfvv_register_video_cpt()
{
    $labels = [
        "name" => __("Videos", "mf-vimeo-video"),
        "singular_name" => __("Video", "mf-vimeo-video"),
        "add_new" => __("Add New Video", "mf-vimeo-video"),
        "add_new_item" => __("Add New Video", "mf-vimeo-video"),
        "edit_item" => __("Edit Video", "mf-vimeo-video"),
        "view_item" => __("View Video", "mf-vimeo-video"),
        "search_items" => __("Search Videos", "mf-vimeo-video"),
        "not_found" => __("No videos found", "mf-vimeo-video"),
    ];

    register_post_type("mfvv_video", [
        "labels" => $labels,
        "public" => true,
        "has_archive" => true,
        "menu_icon" => "dashicons-video-alt3",
        "supports" => [
            "title",
            "editor",
            "thumbnail",
            "excerpt",
            "author",
            "custom-fields",
            "page-attributes",
        ],
        "rewrite" => ["slug" => "videos"],
        "show_in_rest" => true,
        "rest_base" => "videos",
        "taxonomies" => ["mfvv_media_tag"],
    ]);
}
add_action("init", "mfvv_register_video_cpt");

// Register post meta for REST API access (enables filtering/searching)
function mfvv_register_meta()
{
    register_post_meta("mfvv_video", "mfvv_vimeo_url", [
        "show_in_rest" => true,
        "single" => true,
        "type" => "string",
        "auth_callback" => function () {
            return current_user_can("edit_posts");
        },
    ]);
}
add_action("init", "mfvv_register_meta");

// Add meta box for Vimeo URL
function mfvv_video_meta_box()
{
    add_meta_box(
        "mfvv_vimeo_url",
        __("Vimeo Video URL", "mf-vimeo-video"),
        "mfvv_video_meta_box_html",
        "mfvv_video",
        "side",
        "high",
    );
}
add_action("add_meta_boxes", "mfvv_video_meta_box");

function mfvv_video_meta_box_html($post)
{
    $vimeo_url = get_post_meta($post->ID, "mfvv_vimeo_url", true);
    wp_nonce_field("mfvv_save_vimeo_url", "mfvv_vimeo_url_nonce");
    echo '<label for="mfvv_vimeo_url_input">' .
        esc_html__("Vimeo Video URL", "mf-vimeo-video") .
        "</label>";
    echo '<input type="url" id="mfvv_vimeo_url_input" name="mfvv_vimeo_url_input" value="' .
        esc_url($vimeo_url) .
        '" style="width:100%;margin-bottom:8px" placeholder="https://vimeo.com/123456789" />';
    echo '<button type="button" id="mfvv-fetch-thumb" class="button button-small">' .
        esc_html__("Fetch Vimeo Thumbnail", "mf-vimeo-video") .
        "</button>";
    echo '<span id="mfvv-fetch-status" style="display:inline-block;margin-left:8px"></span>';
}

function mfvv_save_vimeo_url($post_id)
{
    if (
        !isset($_POST["mfvv_vimeo_url_nonce"]) ||
        !wp_verify_nonce($_POST["mfvv_vimeo_url_nonce"], "mfvv_save_vimeo_url")
    ) {
        return;
    }
    if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can("edit_post", $post_id)) {
        return;
    }

    if (isset($_POST["mfvv_vimeo_url_input"])) {
        $new_url = esc_url_raw(wp_unslash($_POST["mfvv_vimeo_url_input"]));
        $old_url = get_post_meta($post_id, "mfvv_vimeo_url", true);

        update_post_meta($post_id, "mfvv_vimeo_url", $new_url);

        // Auto-fetch Vimeo thumbnail when URL changes and no featured image is set.
        if (
            $new_url &&
            $new_url !== $old_url &&
            !has_post_thumbnail($post_id)
        ) {
            $thumbnail_result = mfvv_fetch_vimeo_thumbnail($post_id, $new_url);
            if (is_wp_error($thumbnail_result)) {
                mfvv_log_thumbnail_error($post_id, $thumbnail_result);
            }
        }
    }
}
add_action("save_post_mfvv_video", "mfvv_save_vimeo_url");

// Enqueue inline script for the "Fetch Vimeo Thumbnail" button
function mfvv_admin_enqueue($hook)
{
    if (!in_array($hook, ["post.php", "post-new.php"], true)) {
        return;
    }
    if (get_post_type() !== "mfvv_video") {
        return;
    }
    wp_enqueue_script(
        "mfvv-admin",
        plugins_url("assets/js/admin.js", __FILE__),
        ["wp-data"],
        "0.4.10",
        true,
    );
    wp_localize_script("mfvv-admin", "mfvvAdmin", [
        "ajaxUrl" => admin_url("admin-ajax.php"),
        "nonce" => wp_create_nonce("mfvv_fetch_thumbnail"),
        "postId" => get_the_ID(),
    ]);
}
add_action("admin_enqueue_scripts", "mfvv_admin_enqueue");

// Customize the Videos admin list table with management columns.
function mfvv_video_admin_columns($columns)
{
    $new_columns = [];

    if (isset($columns["cb"])) {
        $new_columns["cb"] = $columns["cb"];
    }

    $new_columns["mfvv_thumbnail"] = __("Thumbnail", "mf-vimeo-video");

    if (isset($columns["title"])) {
        $new_columns["title"] = $columns["title"];
    }

    $new_columns["mfvv_vimeo_url"] = __("Vimeo URL", "mf-vimeo-video");

    if (isset($columns["taxonomy-mfvv_media_tag"])) {
        $new_columns["taxonomy-mfvv_media_tag"] =
            $columns["taxonomy-mfvv_media_tag"];
    }

    if (isset($columns["author"])) {
        $new_columns["author"] = $columns["author"];
    }

    if (isset($columns["date"])) {
        $new_columns["date"] = $columns["date"];
    }

    foreach ($columns as $key => $label) {
        if (!isset($new_columns[$key])) {
            $new_columns[$key] = $label;
        }
    }

    return $new_columns;
}
add_filter("manage_mfvv_video_posts_columns", "mfvv_video_admin_columns");

function mfvv_video_admin_column_content($column, $post_id)
{
    if ("mfvv_thumbnail" === $column) {
        if (has_post_thumbnail($post_id)) {
            echo '<a href="' . esc_url(get_edit_post_link($post_id)) . '">';
            echo get_the_post_thumbnail($post_id, [64, 64], [
                "style" => "width:64px;height:64px;object-fit:cover;",
            ]);
            echo "</a>";
        } else {
            echo esc_html_x("—", "empty admin table column", "mf-vimeo-video");
        }
        return;
    }

    if ("mfvv_vimeo_url" === $column) {
        $vimeo_url = get_post_meta($post_id, "mfvv_vimeo_url", true);
        if ($vimeo_url) {
            echo '<a href="' . esc_url($vimeo_url) . '" target="_blank" rel="noopener noreferrer">' .
                esc_html(wp_html_excerpt($vimeo_url, 60, "…")) .
                "</a>";
        } else {
            echo esc_html_x("—", "empty admin table column", "mf-vimeo-video");
        }
    }
}
add_action(
    "manage_mfvv_video_posts_custom_column",
    "mfvv_video_admin_column_content",
    10,
    2,
);

function mfvv_video_sortable_admin_columns($columns)
{
    $columns["mfvv_vimeo_url"] = "mfvv_vimeo_url";
    return $columns;
}
add_filter(
    "manage_edit-mfvv_video_sortable_columns",
    "mfvv_video_sortable_admin_columns",
);

function mfvv_video_admin_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ("mfvv_video" !== $query->get("post_type")) {
        return;
    }

    if ("mfvv_vimeo_url" === $query->get("orderby")) {
        $query->set("meta_key", "mfvv_vimeo_url");
        $query->set("orderby", "meta_value");
    }
}
add_action("pre_get_posts", "mfvv_video_admin_orderby");

function mfvv_video_admin_filters($post_type)
{
    if ("mfvv_video" !== $post_type) {
        return;
    }

    $selected_tag = isset($_GET["mfvv_media_tag_filter"])
        ? sanitize_text_field(wp_unslash($_GET["mfvv_media_tag_filter"]))
        : "";

    wp_dropdown_categories([
        "show_option_all" => __("All Media Tags", "mf-vimeo-video"),
        "taxonomy" => "mfvv_media_tag",
        "name" => "mfvv_media_tag_filter",
        "orderby" => "name",
        "selected" => $selected_tag,
        "hierarchical" => false,
        "depth" => 1,
        "show_count" => false,
        "hide_empty" => false,
        "value_field" => "slug",
    ]);

    $selected_author = isset($_GET["mfvv_author_filter"])
        ? absint($_GET["mfvv_author_filter"])
        : 0;

    wp_dropdown_users([
        "show_option_all" => __("All Authors", "mf-vimeo-video"),
        "name" => "mfvv_author_filter",
        "selected" => $selected_author,
        "include_selected" => true,
        "who" => "authors",
    ]);
}
add_action("restrict_manage_posts", "mfvv_video_admin_filters");

function mfvv_video_admin_filter_query($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ("mfvv_video" !== $query->get("post_type")) {
        return;
    }

    if (!empty($_GET["mfvv_media_tag_filter"])) {
        $query->set("tax_query", [
            [
                "taxonomy" => "mfvv_media_tag",
                "field" => "slug",
                "terms" => sanitize_text_field(
                    wp_unslash($_GET["mfvv_media_tag_filter"]),
                ),
            ],
        ]);
    }

    if (!empty($_GET["mfvv_author_filter"])) {
        $query->set("author", absint($_GET["mfvv_author_filter"]));
    }
}
add_action("pre_get_posts", "mfvv_video_admin_filter_query");

function mfvv_video_admin_column_styles()
{
    $screen = get_current_screen();
    if (
        !$screen ||
        "edit-mfvv_video" !== $screen->id ||
        "mfvv_video" !== $screen->post_type
    ) {
        return;
    }

    echo '<style>.fixed .column-mfvv_thumbnail{width:88px}.fixed .column-mfvv_vimeo_url{width:22%}</style>';
}
add_action("admin_head", "mfvv_video_admin_column_styles");

function mfvv_get_video_template_bulk_actions()
{
    $actions = [
        "mfvv_set_template_default" => __(
            "Set template: Default",
            "mf-vimeo-video",
        ),
    ];

    $templates = wp_get_theme()->get_page_templates(null, "mfvv_video");

    foreach ($templates as $template_slug => $template_label) {
        $action = "mfvv_set_template_" . substr(md5($template_slug), 0, 12);
        $actions[$action] = sprintf(
            /* translators: %s: Template name. */
            __("Set template: %s", "mf-vimeo-video"),
            $template_label,
        );
    }

    return $actions;
}

function mfvv_get_template_slug_for_bulk_action($action)
{
    if ("mfvv_set_template_default" === $action) {
        return "default";
    }

    $templates = wp_get_theme()->get_page_templates(null, "mfvv_video");

    foreach ($templates as $slug => $label) {
        $template_action = "mfvv_set_template_" . substr(md5($slug), 0, 12);

        if ($template_action === $action) {
            return $slug;
        }
    }

    return false;
}

function mfvv_video_bulk_actions($bulk_actions)
{
    $bulk_actions["mfvv_fetch_thumbnails"] = __(
        "Fetch Vimeo thumbnails",
        "mf-vimeo-video",
    );

    foreach (mfvv_get_video_template_bulk_actions() as $action => $label) {
        $bulk_actions[$action] = $label;
    }

    return $bulk_actions;
}
add_filter("bulk_actions-edit-mfvv_video", "mfvv_video_bulk_actions");

function mfvv_video_handle_bulk_actions($redirect_url, $action, $post_ids)
{
    if ("mfvv_fetch_thumbnails" === $action) {
        $updated = 0;
        $failed = 0;
        $skipped = 0;

        foreach ((array) $post_ids as $post_id) {
            $post_id = absint($post_id);
            if (!$post_id || !current_user_can("edit_post", $post_id)) {
                $skipped++;
                continue;
            }

            $vimeo_url = get_post_meta($post_id, "mfvv_vimeo_url", true);
            if (!$vimeo_url) {
                $skipped++;
                continue;
            }

            $attachment_id = mfvv_fetch_vimeo_thumbnail($post_id, $vimeo_url);
            if (is_wp_error($attachment_id)) {
                mfvv_log_thumbnail_error($post_id, $attachment_id);
                $failed++;
                continue;
            }

            $updated++;
        }

        return add_query_arg(
            [
                "mfvv_bulk_thumbnails" => 1,
                "mfvv_updated" => $updated,
                "mfvv_failed" => $failed,
                "mfvv_skipped" => $skipped,
            ],
            remove_query_arg(
                [
                    "mfvv_bulk_thumbnails",
                    "mfvv_bulk_template",
                    "mfvv_updated",
                    "mfvv_failed",
                    "mfvv_skipped",
                ],
                $redirect_url,
            ),
        );
    }

    $template_slug = mfvv_get_template_slug_for_bulk_action($action);

    if (false === $template_slug) {
        return $redirect_url;
    }

    $updated = 0;
    $skipped = 0;

    foreach ((array) $post_ids as $post_id) {
        $post_id = absint($post_id);

        if (!$post_id || !current_user_can("edit_post", $post_id)) {
            $skipped++;
            continue;
        }

        if ("default" === $template_slug) {
            delete_post_meta($post_id, "_wp_page_template");
        } else {
            update_post_meta($post_id, "_wp_page_template", $template_slug);
        }

        $updated++;
    }

    return add_query_arg(
        [
            "mfvv_bulk_template" => 1,
            "mfvv_updated" => $updated,
            "mfvv_skipped" => $skipped,
        ],
        remove_query_arg(
            [
                "mfvv_bulk_template",
                "mfvv_bulk_thumbnails",
                "mfvv_updated",
                "mfvv_failed",
                "mfvv_skipped",
            ],
            $redirect_url,
        ),
    );
}
add_filter(
    "handle_bulk_actions-edit-mfvv_video",
    "mfvv_video_handle_bulk_actions",
    10,
    3,
);

function mfvv_video_bulk_action_notice()
{
    if (
        empty($_GET["mfvv_bulk_thumbnails"]) &&
        empty($_GET["mfvv_bulk_template"])
    ) {
        return;
    }

    $screen = get_current_screen();
    if (!$screen || "edit-mfvv_video" !== $screen->id) {
        return;
    }

    $updated = isset($_GET["mfvv_updated"])
        ? absint($_GET["mfvv_updated"])
        : 0;
    $failed = isset($_GET["mfvv_failed"]) ? absint($_GET["mfvv_failed"]) : 0;
    $skipped = isset($_GET["mfvv_skipped"])
        ? absint($_GET["mfvv_skipped"])
        : 0;

    if (!empty($_GET["mfvv_bulk_template"])) {
        $message = sprintf(
            __(
                "Video template bulk action complete. Updated: %1$d. Skipped: %2$d.",
                "mf-vimeo-video",
            ),
            $updated,
            $skipped,
        );
    } else {
        $message = sprintf(
            __(
                "Vimeo thumbnail bulk action complete. Updated: %1$d. Failed: %2$d. Skipped: %3$d.",
                "mf-vimeo-video",
            ),
            $updated,
            $failed,
            $skipped,
        );
    }

    printf(
        '<div class="notice notice-info is-dismissible"><p>%s</p></div>',
        esc_html($message),
    );
}
add_action("admin_notices", "mfvv_video_bulk_action_notice");

// AJAX handler: fetch Vimeo thumbnail and set as featured image
function mfvv_ajax_fetch_thumbnail()
{
    check_ajax_referer("mfvv_fetch_thumbnail", "nonce");

    $post_id = isset($_POST["post_id"]) ? absint($_POST["post_id"]) : 0;
    $vimeo_url = isset($_POST["vimeo_url"])
        ? esc_url_raw(wp_unslash($_POST["vimeo_url"]))
        : "";

    if (!$post_id || !$vimeo_url || !current_user_can("edit_post", $post_id)) {
        wp_send_json_error(__("Invalid request.", "mf-vimeo-video"));
    }

    $attachment_id = mfvv_fetch_vimeo_thumbnail($post_id, $vimeo_url);

    if (is_wp_error($attachment_id)) {
        mfvv_log_thumbnail_error($post_id, $attachment_id);
        wp_send_json_error($attachment_id->get_error_message());
    }

    wp_send_json_success([
        "message" => __("Thumbnail updated.", "mf-vimeo-video"),
        "attachment_id" => $attachment_id,
        "thumbnail_url" => wp_get_attachment_image_url(
            $attachment_id,
            "thumbnail",
        ),
    ]);
}
add_action("wp_ajax_mfvv_fetch_thumbnail", "mfvv_ajax_fetch_thumbnail");

// Fetch Vimeo thumbnail via oEmbed and set as featured image.
function mfvv_fetch_vimeo_thumbnail($post_id, $vimeo_url)
{
    $oembed_url =
        "https://vimeo.com/api/oembed.json?url=" . rawurlencode($vimeo_url);
    $response = wp_remote_get($oembed_url, [
        "timeout" => 15,
        "redirection" => 5,
        "user-agent" =>
            "WordPress/" . get_bloginfo("version") . "; " . home_url("/"),
    ]);

    if (is_wp_error($response)) {
        return new WP_Error(
            "mfvv_oembed_request_failed",
            sprintf(
                __("Vimeo oEmbed request failed: %s", "mf-vimeo-video"),
                $response->get_error_message(),
            ),
        );
    }

    $response_code = wp_remote_retrieve_response_code($response);
    $response_body = wp_remote_retrieve_body($response);
    if (200 !== $response_code) {
        return new WP_Error(
            "mfvv_oembed_bad_response",
            mfvv_get_vimeo_oembed_error_message($response_code, $response_body),
        );
    }

    $data = json_decode($response_body, true);

    if (JSON_ERROR_NONE !== json_last_error() || !is_array($data)) {
        return new WP_Error(
            "mfvv_oembed_invalid_json",
            __("Vimeo oEmbed returned invalid JSON.", "mf-vimeo-video"),
        );
    }

    if (empty($data["thumbnail_url"])) {
        return new WP_Error(
            "mfvv_oembed_missing_thumbnail",
            __(
                "Vimeo returned video metadata, but no thumbnail URL. Add or regenerate the video thumbnail/poster in Vimeo, then try again; otherwise set the featured image manually in WordPress.",
                "mf-vimeo-video",
            ),
        );
    }

    // Download and sideload the thumbnail into the media library.
    require_once ABSPATH . "wp-admin/includes/media.php";
    require_once ABSPATH . "wp-admin/includes/file.php";
    require_once ABSPATH . "wp-admin/includes/image.php";

    $tmp_file = download_url(esc_url_raw($data["thumbnail_url"]), 15);

    if (is_wp_error($tmp_file)) {
        return new WP_Error(
            "mfvv_thumbnail_download_failed",
            sprintf(
                __("Thumbnail download failed: %s", "mf-vimeo-video"),
                $tmp_file->get_error_message(),
            ),
        );
    }

    $path = wp_parse_url($data["thumbnail_url"], PHP_URL_PATH);
    $extension = pathinfo((string) $path, PATHINFO_EXTENSION);
    $extension = $extension ? strtolower($extension) : "jpg";

    if (!in_array($extension, ["jpg", "jpeg", "png", "webp"], true)) {
        $extension = "jpg";
    }

    $file_array = [
        "name" => sanitize_file_name(
            get_the_title($post_id) . "-vimeo-thumbnail." . $extension,
        ),
        "tmp_name" => $tmp_file,
    ];

    $attachment_id = media_handle_sideload($file_array, $post_id);

    if (is_wp_error($attachment_id)) {
        @unlink($tmp_file);
        return new WP_Error(
            "mfvv_thumbnail_sideload_failed",
            sprintf(
                __(
                    "Could not add thumbnail to the media library: %s",
                    "mf-vimeo-video",
                ),
                $attachment_id->get_error_message(),
            ),
        );
    }

    if (!set_post_thumbnail($post_id, $attachment_id)) {
        wp_delete_attachment($attachment_id, true);
        return new WP_Error(
            "mfvv_set_thumbnail_failed",
            __(
                "Could not set the downloaded image as the featured image.",
                "mf-vimeo-video",
            ),
        );
    }

    return $attachment_id;
}

function mfvv_get_vimeo_oembed_error_message($response_code, $response_body)
{
    $vimeo_message = "";
    $decoded_body = json_decode($response_body, true);

    if (is_array($decoded_body)) {
        if (!empty($decoded_body["message"])) {
            $vimeo_message = sanitize_text_field($decoded_body["message"]);
        } elseif (!empty($decoded_body["error"])) {
            $vimeo_message = sanitize_text_field($decoded_body["error"]);
        }
    }

    if (in_array((int) $response_code, [401, 403], true)) {
        return sprintf(
            __(
                'Vimeo denied access to this video (HTTP %1$d). The video is likely private, password-protected, domain-restricted, or not allowed to be embedded. Make it public/unlisted with oEmbed access, or set the featured image manually.%2$s',
                "mf-vimeo-video",
            ),
            $response_code,
            $vimeo_message
                ? " " .
                    sprintf(
                        __("Vimeo says: %s", "mf-vimeo-video"),
                        $vimeo_message,
                    )
                : "",
        );
    }

    if (404 === (int) $response_code) {
        return sprintf(
            __(
                "Vimeo could not find or access this video via oEmbed (HTTP 404). If the URL is correct, the video is likely private, deleted, password-protected, domain-restricted, or disabled for embedding. Make it accessible in Vimeo privacy/embed settings, or set the featured image manually.%s",
                "mf-vimeo-video",
            ),
            $vimeo_message
                ? " " .
                    sprintf(
                        __("Vimeo says: %s", "mf-vimeo-video"),
                        $vimeo_message,
                    )
                : "",
        );
    }

    return sprintf(
        __(
            'Vimeo oEmbed returned HTTP %1$d, so the thumbnail could not be retrieved.%2$s',
            "mf-vimeo-video",
        ),
        $response_code,
        $vimeo_message
            ? " " .
                sprintf(__("Vimeo says: %s", "mf-vimeo-video"), $vimeo_message)
            : "",
    );
}

function mfvv_log_thumbnail_error($post_id, WP_Error $error)
{
    if (defined("WP_DEBUG") && WP_DEBUG) {
        error_log(
            sprintf(
                "Vimeo Video thumbnail fetch failed for post %d: [%s] %s",
                $post_id,
                $error->get_error_code(),
                $error->get_error_message(),
            ),
        ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
    }
}

// Enable filtering by media_tag in REST API queries
function mfvv_rest_query_filter($args, $request)
{
    if (!empty($request["mfvv_media_tag"])) {
        $args["tax_query"][] = [
            "taxonomy" => "mfvv_media_tag",
            "field" => "slug",
            "terms" => array_map(
                "sanitize_text_field",
                (array) $request["mfvv_media_tag"],
            ),
        ];
    }
    return $args;
}
add_filter("rest_mfvv_video_query", "mfvv_rest_query_filter", 10, 2);

// Register custom REST query parameter
function mfvv_rest_query_params($params)
{
    $params["mfvv_media_tag"] = [
        "description" => __("Filter by media tag slug.", "mf-vimeo-video"),
        "type" => "array",
        "items" => ["type" => "string"],
    ];
    return $params;
}
add_filter("rest_mfvv_video_collection_params", "mfvv_rest_query_params");
