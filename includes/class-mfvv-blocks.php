<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MFVV_Blocks {

    public static function init() {
        add_action( 'init', [ __CLASS__, 'register_blocks' ] );
    }

    public static function register_blocks() {
        wp_register_script(
            'mfvv-blocks-editor',
            plugins_url( 'assets/js/blocks.js', __DIR__ ),
            [ 'wp-blocks', 'wp-element', 'wp-components', 'wp-block-editor', 'wp-server-side-render' ],
            '0.4.8',
            true
        );

        wp_register_style(
            'mfvv-shortcodes-blocks',
            plugins_url( 'assets/css/shortcodes-blocks.css', __DIR__ ),
            [],
            '0.4.8'
        );

        register_block_type( 'mfvv/video', [
            'api_version'     => 2,
            'title'           => __( 'Single Vimeo Video', 'mf-vimeo-video' ),
            'category'        => 'embed',
            'icon'            => 'video-alt3',
            'editor_script'   => 'mfvv-blocks-editor',
            'style'           => 'mfvv-shortcodes-blocks',
            'attributes'      => [
                'id'       => [ 'type' => 'number', 'default' => 0 ],
                'autoplay' => [ 'type' => 'boolean', 'default' => false ],
                'loop'     => [ 'type' => 'boolean', 'default' => false ],
                'muted'    => [ 'type' => 'boolean', 'default' => false ],
                'controls' => [ 'type' => 'boolean', 'default' => true ],
                'width'    => [ 'type' => 'string', 'default' => '' ],
                'height'   => [ 'type' => 'string', 'default' => '' ],
                'stretch'  => [ 'type' => 'boolean', 'default' => false ],
            ],
            'render_callback' => [ __CLASS__, 'render_video_block' ],
        ] );

        register_block_type( 'mfvv/gallery', [
            'api_version'     => 2,
            'title'           => __( 'Vimeo Video Gallery', 'mf-vimeo-video' ),
            'category'        => 'widgets',
            'icon'            => 'grid-view',
            'editor_script'   => 'mfvv-blocks-editor',
            'style'           => 'mfvv-shortcodes-blocks',
            'attributes'      => [
                'tag'            => [ 'type' => 'string', 'default' => '' ],
                'posts_per_page' => [ 'type' => 'number', 'default' => 6 ],
                'columns'        => [ 'type' => 'number', 'default' => 3 ],
                'orderby'        => [ 'type' => 'string', 'default' => 'date' ],
                'order'          => [ 'type' => 'string', 'default' => 'DESC' ],
            ],
            'render_callback' => [ __CLASS__, 'render_gallery_block' ],
        ] );

        register_block_type( 'mfvv/related-videos', [
            'api_version'     => 2,
            'title'           => __( 'Related Vimeo Videos', 'mf-vimeo-video' ),
            'category'        => 'widgets',
            'icon'            => 'playlist-video',
            'editor_script'   => 'mfvv-blocks-editor',
            'style'           => 'mfvv-shortcodes-blocks',
            'attributes'      => [
                'id'             => [ 'type' => 'number', 'default' => 0 ],
                'posts_per_page' => [ 'type' => 'number', 'default' => 4 ],
                'columns'        => [ 'type' => 'number', 'default' => 4 ],
            ],
            'render_callback' => [ __CLASS__, 'render_related_videos_block' ],
        ] );
    }

    public static function render_video_block( $attributes ) {
        return MFVV_Render::render_video( $attributes );
    }

    public static function render_gallery_block( $attributes ) {
        return MFVV_Render::render_gallery( $attributes );
    }

    public static function render_related_videos_block( $attributes ) {
        return MFVV_Render::render_related_videos( $attributes );
    }
}
