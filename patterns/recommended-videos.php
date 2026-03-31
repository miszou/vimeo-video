<?php
/**
 * Title: Recommended Videos
 * Slug: mfvv/recommended-videos
 * Inserter: no
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$mfvv_current_id = get_the_ID();
$mfvv_terms      = get_the_terms( $mfvv_current_id, 'mfvv_media_tag' );

if ( empty( $mfvv_terms ) || is_wp_error( $mfvv_terms ) ) {
    return;
}

$mfvv_term_ids = wp_list_pluck( $mfvv_terms, 'term_id' );

$mfvv_related = new WP_Query( [
    'post_type'      => 'mfvv_video',
    'posts_per_page' => 8,
    'post__not_in'   => [ $mfvv_current_id ],
    'tax_query'      => [ [
        'taxonomy' => 'mfvv_media_tag',
        'field'    => 'term_id',
        'terms'    => $mfvv_term_ids,
    ] ],
    'orderby'        => 'date',
    'order'          => 'DESC',
] );

if ( ! $mfvv_related->have_posts() ) {
    wp_reset_postdata();
    return;
}
?>
<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60)">
    <h2 class="wp-block-heading alignwide has-small-font-size" style="font-style:normal;font-weight:700;letter-spacing:1.4px;text-transform:uppercase;margin-bottom:var(--wp--preset--spacing--40)">
        <?php esc_html_e( 'Recommended', 'mf-vimeo-video' ); ?>
    </h2>
    <div class="mfvv-slider alignwide">
        <?php while ( $mfvv_related->have_posts() ) : $mfvv_related->the_post(); ?>
        <a href="<?php the_permalink(); ?>" class="mfvv-slider__card">
            <?php if ( has_post_thumbnail() ) : ?>
                <?php the_post_thumbnail( 'medium_large', [ 'class' => 'mfvv-slider__thumb' ] ); ?>
            <?php else : ?>
                <div class="mfvv-slider__placeholder">
                    <span class="dashicons dashicons-video-alt3"></span>
                </div>
            <?php endif; ?>
            <span class="mfvv-slider__title"><?php the_title(); ?></span>
        </a>
        <?php endwhile; ?>
    </div>
</div>
<?php wp_reset_postdata(); ?>
