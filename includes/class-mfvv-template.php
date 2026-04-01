<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MFVV_Template {

    private static $instance = null;

    public static function init() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'register_patterns' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
        add_filter( 'get_block_templates', [ $this, 'inject_template' ], 10, 3 );
        add_filter( 'get_block_file_template', [ $this, 'resolve_template' ], 10, 3 );

        // Classic theme fallback: use template_include to load PHP wrapper.
        add_filter( 'template_include', [ $this, 'classic_template_fallback' ] );
    }

    /**
     * Build a WP_Block_Template object from the plugin's HTML file.
     */
    private function build_template_object() {
        $template_file = plugin_dir_path( __DIR__ ) . 'templates/single-mfvv_video.html';

        if ( ! file_exists( $template_file ) ) {
            return null;
        }

        $template              = new WP_Block_Template();
        $template->id          = 'mfvv-vimeo-video//single-mfvv_video';
        $template->theme       = get_stylesheet();
        $template->slug        = 'single-mfvv_video';
        $template->source      = 'plugin';
        $template->type        = 'wp_template';
        $template->title       = __( 'Single Video', 'mf-vimeo-video' );
        $template->description = __( 'Displays a single video with Vimeo player and recommended videos.', 'mf-vimeo-video' );
        $template->status      = 'publish';
        $template->has_theme_file = true;
        $template->is_custom   = false;
        $template->post_types  = [ 'mfvv_video' ];
        $template->content     = file_get_contents( $template_file );

        return $template;
    }

    /**
     * Inject the template into listing queries (e.g. Site Editor).
     */
    public function inject_template( $query_result, $query, $template_type ) {
        if ( 'wp_template' !== $template_type ) {
            return $query_result;
        }

        // Check if a user-customized version already exists in the DB
        foreach ( $query_result as $t ) {
            if ( 'single-mfvv_video' === $t->slug ) {
                return $query_result;
            }
        }

        // Filter by post_type if the query specifies one
        if ( ! empty( $query['post_type'] ) && 'mfvv_video' !== $query['post_type'] ) {
            return $query_result;
        }

        // Filter by slug if the query specifies one
        if ( ! empty( $query['slug__in'] ) && ! in_array( 'single-mfvv_video', $query['slug__in'], true ) ) {
            return $query_result;
        }

        $template = $this->build_template_object();

        if ( $template ) {
            $query_result[] = $template;
        }

        return $query_result;
    }

    /**
     * Resolve the template when WordPress looks for single-mfvv_video
     * via the template hierarchy.
     */
    public function resolve_template( $block_template, $id, $template_type ) {
        if ( 'wp_template' !== $template_type ) {
            return $block_template;
        }

        // Match both possible ID formats
        $target_slugs = [
            get_stylesheet() . '//single-mfvv_video',
            'single-mfvv_video',
        ];

        if ( ! in_array( $id, $target_slugs, true ) ) {
            return $block_template;
        }

        // If WordPress already found a template (theme or DB), don't override
        if ( $block_template ) {
            return $block_template;
        }

        return $this->build_template_object();
    }

    /**
     * For classic (non-block) themes, override the template for single
     * mfvv_video posts with our PHP wrapper that renders the block markup.
     */
    public function classic_template_fallback( $template ) {
        if ( ! is_singular( 'mfvv_video' ) ) {
            return $template;
        }

        // Block themes already use the block template system — skip.
        if ( wp_is_block_theme() ) {
            return $template;
        }

        $plugin_template = plugin_dir_path( __DIR__ ) . 'templates/single-mfvv_video.php';

        if ( file_exists( $plugin_template ) ) {
            return $plugin_template;
        }

        return $template;
    }

    public function register_patterns() {
        $patterns_dir = plugin_dir_path( __DIR__ ) . 'patterns/';

        register_block_pattern( 'mfvv/vimeo-player', [
            'title'    => __( 'Vimeo Player', 'mf-vimeo-video' ),
            'filePath' => $patterns_dir . 'vimeo-player.php',
            'inserter' => false,
        ] );

        register_block_pattern( 'mfvv/recommended-videos', [
            'title'    => __( 'Recommended Videos', 'mf-vimeo-video' ),
            'filePath' => $patterns_dir . 'recommended-videos.php',
            'inserter' => false,
        ] );
    }

    public function enqueue_styles() {
        if ( ! is_singular( 'mfvv_video' ) ) {
            return;
        }

        wp_enqueue_style(
            'mfvv-single-video',
            plugins_url( 'assets/css/single-video.css', __DIR__ ),
            [],
            '0.2'
        );
    }
}
