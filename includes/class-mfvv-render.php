<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MFVV_Render {

    public static function enqueue_assets() {
        wp_enqueue_style(
            'mfvv-shortcodes-blocks',
            plugins_url( 'assets/css/shortcodes-blocks.css', __DIR__ ),
            [],
            '0.4.8'
        );
    }

    public static function render_video( $attributes = [] ) {
        $attributes = wp_parse_args( $attributes, [
            'id'       => 0,
            'autoplay' => false,
            'loop'     => false,
            'muted'    => false,
            'controls' => true,
            'width'    => '',
            'height'   => '',
            'stretch'  => false,
        ] );

        $post_id = absint( $attributes['id'] );
        if ( ! $post_id && is_singular( 'mfvv_video' ) ) {
            $post_id = get_the_ID();
        }

        if ( ! $post_id || 'mfvv_video' !== get_post_type( $post_id ) || 'publish' !== get_post_status( $post_id ) ) {
            return '';
        }

        $vimeo_url = get_post_meta( $post_id, 'mfvv_vimeo_url', true );
        if ( ! $vimeo_url ) {
            return '';
        }

        self::enqueue_assets();

        $embed_url = self::add_embed_params( $vimeo_url, [
            'autoplay' => self::truthy( $attributes['autoplay'] ) ? '1' : null,
            'loop'     => self::truthy( $attributes['loop'] ) ? '1' : null,
            'muted'    => self::truthy( $attributes['muted'] ) ? '1' : null,
            'controls' => self::truthy( $attributes['controls'] ) ? null : '0',
        ] );

        $embed_args = [];
        if ( is_numeric( $attributes['width'] ) && absint( $attributes['width'] ) ) {
            $embed_args['width'] = absint( $attributes['width'] );
        }
        if ( is_numeric( $attributes['height'] ) && absint( $attributes['height'] ) ) {
            $embed_args['height'] = absint( $attributes['height'] );
        }

        $embed = wp_oembed_get( $embed_url, $embed_args );
        if ( ! $embed ) {
            $embed = sprintf(
                '<p><a href="%1$s" rel="noopener noreferrer">%2$s</a></p>',
                esc_url( $vimeo_url ),
                esc_html( get_the_title( $post_id ) )
            );
        }

        $width   = self::sanitize_css_dimension( $attributes['width'] );
        $height  = self::sanitize_css_dimension( $attributes['height'] );
        $stretch = self::truthy( $attributes['stretch'] );
        $classes = [ 'mfvv-video-embed' ];
        $styles  = [];

        if ( $width || $height || $stretch ) {
            $classes[] = 'has-custom-dimensions';
        }
        if ( $height ) {
            $classes[] = 'has-custom-height';
            $styles[]  = '--mfvv-video-height:' . $height;
        }
        if ( $stretch ) {
            $classes[] = 'is-stretched';
            $styles[]  = '--mfvv-video-width:100%';
        } elseif ( $width ) {
            $styles[] = '--mfvv-video-width:' . $width;
        }

        return sprintf(
            '<div class="%1$s" style="%2$s" data-video-id="%3$d">%4$s</div>',
            esc_attr( implode( ' ', $classes ) ),
            esc_attr( implode( ';', $styles ) ),
            $post_id,
            $embed
        );
    }

    public static function render_gallery( $attributes = [] ) {
        $attributes = wp_parse_args( $attributes, [
            'tag'            => '',
            'posts_per_page' => 6,
            'columns'        => 3,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ] );

        $query_args = [
            'post_type'           => 'mfvv_video',
            'post_status'         => 'publish',
            'posts_per_page'      => max( 1, min( 24, absint( $attributes['posts_per_page'] ) ) ),
            'orderby'             => sanitize_key( $attributes['orderby'] ),
            'order'               => 'ASC' === strtoupper( (string) $attributes['order'] ) ? 'ASC' : 'DESC',
            'ignore_sticky_posts' => true,
        ];

        $tag = sanitize_title( $attributes['tag'] );
        if ( $tag ) {
            $query_args['tax_query'] = [
                [
                    'taxonomy' => 'mfvv_media_tag',
                    'field'    => 'slug',
                    'terms'    => $tag,
                ],
            ];
        }

        return self::render_video_query( $query_args, [
            'class'   => 'mfvv-video-gallery',
            'columns' => absint( $attributes['columns'] ),
        ] );
    }

    public static function render_related_videos( $attributes = [] ) {
        $attributes = wp_parse_args( $attributes, [
            'id'             => 0,
            'posts_per_page' => 4,
            'columns'        => 4,
        ] );

        $post_id = absint( $attributes['id'] );
        if ( ! $post_id && is_singular( 'mfvv_video' ) ) {
            $post_id = get_the_ID();
        }

        if ( ! $post_id || 'mfvv_video' !== get_post_type( $post_id ) ) {
            return '';
        }

        $terms = wp_get_post_terms( $post_id, 'mfvv_media_tag', [ 'fields' => 'ids' ] );
        if ( is_wp_error( $terms ) || empty( $terms ) ) {
            return '';
        }

        $query_args = [
            'post_type'           => 'mfvv_video',
            'post_status'         => 'publish',
            'posts_per_page'      => max( 1, min( 12, absint( $attributes['posts_per_page'] ) ) ),
            'post__not_in'        => [ $post_id ],
            'ignore_sticky_posts' => true,
            'tax_query'           => [
                [
                    'taxonomy' => 'mfvv_media_tag',
                    'field'    => 'term_id',
                    'terms'    => array_map( 'absint', $terms ),
                ],
            ],
        ];

        return self::render_video_query( $query_args, [
            'class'   => 'mfvv-related-videos',
            'columns' => absint( $attributes['columns'] ),
        ] );
    }

    private static function render_video_query( $query_args, $options = [] ) {
        self::enqueue_assets();

        $columns = isset( $options['columns'] ) ? max( 1, min( 6, absint( $options['columns'] ) ) ) : 3;
        $class   = isset( $options['class'] ) ? sanitize_html_class( $options['class'] ) : 'mfvv-video-gallery';
        $query   = new WP_Query( $query_args );

        if ( ! $query->have_posts() ) {
            return '';
        }

        ob_start();
        ?>
        <div class="<?php echo esc_attr( $class ); ?> mfvv-video-grid" style="--mfvv-columns: <?php echo esc_attr( $columns ); ?>;">
            <?php while ( $query->have_posts() ) : $query->the_post(); ?>
                <article class="mfvv-video-card">
                    <a class="mfvv-video-card__link" href="<?php the_permalink(); ?>">
                        <?php if ( has_post_thumbnail() ) : ?>
                            <?php the_post_thumbnail( 'medium_large', [ 'class' => 'mfvv-video-card__image' ] ); ?>
                        <?php else : ?>
                            <span class="mfvv-video-card__placeholder" aria-hidden="true"></span>
                        <?php endif; ?>
                        <span class="mfvv-video-card__title"><?php the_title(); ?></span>
                    </a>
                </article>
            <?php endwhile; ?>
        </div>
        <?php
        wp_reset_postdata();

        return trim( ob_get_clean() );
    }

    private static function add_embed_params( $url, $params ) {
        $params = array_filter( $params, static function ( $value ) {
            return null !== $value && '' !== $value;
        } );

        if ( empty( $params ) ) {
            return $url;
        }

        return add_query_arg( $params, $url );
    }

    private static function sanitize_css_dimension( $value ) {
        $value = is_string( $value ) ? trim( $value ) : $value;

        if ( '' === $value || null === $value ) {
            return '';
        }

        if ( is_numeric( $value ) ) {
            $pixels = absint( $value );
            return $pixels ? $pixels . 'px' : '';
        }

        if ( ! is_string( $value ) ) {
            return '';
        }

        if ( preg_match( '/^\d+(?:\.\d+)?(px|%|em|rem|vw|vh)$/', $value ) ) {
            return $value;
        }

        return '';
    }

    private static function truthy( $value ) {
        return in_array( $value, [ true, 1, '1', 'true', 'yes', 'on' ], true );
    }
}
