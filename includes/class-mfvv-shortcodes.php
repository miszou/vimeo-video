<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MFVV_Shortcodes {

    public static function init() {
        add_shortcode( 'mfvv_video', [ __CLASS__, 'video' ] );
        add_shortcode( 'mfvv_gallery', [ __CLASS__, 'gallery' ] );
        add_shortcode( 'mfvv_related_videos', [ __CLASS__, 'related_videos' ] );
    }

    public static function video( $atts ) {
        $atts = shortcode_atts(
            [
                'id'       => 0,
                'autoplay' => '0',
                'loop'     => '0',
                'muted'    => '0',
                'controls' => '1',
            ],
            $atts,
            'mfvv_video'
        );

        return MFVV_Render::render_video( $atts );
    }

    public static function gallery( $atts ) {
        $atts = shortcode_atts(
            [
                'tag'            => '',
                'posts_per_page' => 6,
                'columns'        => 3,
                'orderby'        => 'date',
                'order'          => 'DESC',
            ],
            $atts,
            'mfvv_gallery'
        );

        return MFVV_Render::render_gallery( $atts );
    }

    public static function related_videos( $atts ) {
        $atts = shortcode_atts(
            [
                'id'             => 0,
                'posts_per_page' => 4,
                'columns'        => 4,
            ],
            $atts,
            'mfvv_related_videos'
        );

        return MFVV_Render::render_related_videos( $atts );
    }
}
