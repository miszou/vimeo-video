<?php

if (!defined("ABSPATH")) {
    exit();
}

class MFVV_Template
{
    const PLUGIN_TEMPLATE_SLUG = "mfvv-plugin-video-template.php";

    private static $instance = null;

    public static function init()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        add_action("init", [$this, "register_patterns"]);
        add_action("wp_enqueue_scripts", [$this, "enqueue_styles"]);
        add_filter("get_block_templates", [$this, "inject_template"], 10, 3);
        add_filter(
            "get_block_file_template",
            [$this, "resolve_template"],
            10,
            3,
        );
        add_filter(
            "theme_mfvv_video_templates",
            [$this, "add_video_template_options"],
            10,
            4,
        );

        // Classic theme fallback: use template_include to load PHP wrapper.
        add_filter("template_include", [$this, "classic_template_fallback"]);
    }

    /**
     * Build a WP_Block_Template object from the plugin's HTML file.
     */
    private function build_template_object()
    {
        $template_file =
            plugin_dir_path(__DIR__) . "templates/single-mfvv_video.html";

        if (!file_exists($template_file)) {
            return null;
        }

        $template = new WP_Block_Template();
        $template->id = "mfvv-vimeo-video//single-mfvv_video";
        $template->theme = get_stylesheet();
        $template->slug = "single-mfvv_video";
        $template->source = "plugin";
        $template->type = "wp_template";
        $template->title = __("Single Video", "mf-vimeo-video");
        $template->description = __(
            "Displays a single video with Vimeo player and recommended videos.",
            "mf-vimeo-video",
        );
        $template->status = "publish";
        $template->has_theme_file = true;
        $template->is_custom = false;
        $template->post_types = ["mfvv_video"];
        $template->content = file_get_contents($template_file);

        return $template;
    }

    /**
     * Inject the template into listing queries (e.g. Site Editor).
     */
    public function inject_template($query_result, $query, $template_type)
    {
        if ("wp_template" !== $template_type) {
            return $query_result;
        }

        // Check if a user-customized version already exists in the DB
        foreach ($query_result as $t) {
            if ("single-mfvv_video" === $t->slug) {
                return $query_result;
            }
        }

        // Filter by post_type if the query specifies one
        if (
            !empty($query["post_type"]) &&
            "mfvv_video" !== $query["post_type"]
        ) {
            return $query_result;
        }

        // Filter by slug if the query specifies one
        if (
            !empty($query["slug__in"]) &&
            !in_array("single-mfvv_video", $query["slug__in"], true)
        ) {
            return $query_result;
        }

        $template = $this->build_template_object();

        if ($template) {
            $query_result[] = $template;
        }

        return $query_result;
    }

    /**
     * Resolve the template when WordPress looks for single-mfvv_video
     * via the template hierarchy.
     */
    public function resolve_template($block_template, $id, $template_type)
    {
        if ("wp_template" !== $template_type) {
            return $block_template;
        }

        // Match both possible ID formats
        $target_slugs = [
            get_stylesheet() . "//single-mfvv_video",
            "single-mfvv_video",
        ];

        if (!in_array($id, $target_slugs, true)) {
            return $block_template;
        }

        // If WordPress already found a template (theme or DB), don't override
        if ($block_template) {
            return $block_template;
        }

        return $this->build_template_object();
    }

    /**
     * Add selectable templates for video posts in classic themes.
     *
     * WordPress only shows the Template selector for custom post types when
     * they support page attributes and have at least one available template.
     * This exposes the plugin fallback as an explicit option and also allows
     * regular theme page templates to be selected for video posts.
     */
    public function add_video_template_options(
        $post_templates,
        $theme,
        $post,
        $post_type,
    ) {
        if ("mfvv_video" !== $post_type) {
            return $post_templates;
        }

        $post_templates[self::PLUGIN_TEMPLATE_SLUG] = __(
            "Vimeo Video Template",
            "mf-vimeo-video",
        );

        if ($theme instanceof WP_Theme) {
            $theme_templates = $theme->get_post_templates();
            $page_templates = isset($theme_templates["page"])
                ? $theme_templates["page"]
                : [];
            $video_templates = isset($theme_templates["mfvv_video"])
                ? $theme_templates["mfvv_video"]
                : [];

            foreach (
                array_merge($page_templates, $video_templates)
                as $file => $label
            ) {
                if (!isset($post_templates[$file])) {
                    $post_templates[$file] = $label;
                }
            }
        }

        return $post_templates;
    }

    /**
     * For classic (non-block) themes, use the plugin PHP wrapper only when no
     * custom video template has been selected and the theme does not provide a
     * single-mfvv_video.php template.
     */
    public function classic_template_fallback($template)
    {
        if (!is_singular("mfvv_video")) {
            return $template;
        }

        // Block themes already use the block template system — skip.
        if (wp_is_block_theme()) {
            return $template;
        }

        $selected_template = get_page_template_slug(get_queried_object_id());
        $plugin_template =
            plugin_dir_path(__DIR__) . "templates/single-mfvv_video.php";

        if (self::PLUGIN_TEMPLATE_SLUG === $selected_template) {
            return file_exists($plugin_template) ? $plugin_template : $template;
        }

        if ($selected_template && "default" !== $selected_template) {
            $theme_template = locate_template([$selected_template]);
            return $theme_template ? $theme_template : $template;
        }

        $theme_single_template = locate_template(["single-mfvv_video.php"]);
        if (
            $theme_single_template &&
            realpath($theme_single_template) === realpath($template)
        ) {
            return $template;
        }

        if (file_exists($plugin_template)) {
            return $plugin_template;
        }

        return $template;
    }

    public function register_patterns()
    {
        $patterns_dir = plugin_dir_path(__DIR__) . "patterns/";

        register_block_pattern("mfvv/vimeo-player", [
            "title" => __("Vimeo Player", "mf-vimeo-video"),
            "filePath" => $patterns_dir . "vimeo-player.php",
            "inserter" => false,
        ]);

        register_block_pattern("mfvv/recommended-videos", [
            "title" => __("Recommended Videos", "mf-vimeo-video"),
            "filePath" => $patterns_dir . "recommended-videos.php",
            "inserter" => false,
        ]);
    }

    public function enqueue_styles()
    {
        if (!is_singular("mfvv_video")) {
            return;
        }

        wp_enqueue_style(
            "mfvv-single-video",
            plugins_url("assets/css/single-video.css", __DIR__),
            [],
            "0.4.8",
        );
    }
}
