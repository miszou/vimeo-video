<?php
/**
 * Title: Vimeo Player
 * Slug: mfvv/vimeo-player
 * Inserter: no
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$mfvv_vimeo_url = get_post_meta( get_the_ID(), 'mfvv_vimeo_url', true );

if ( empty( $mfvv_vimeo_url ) ) {
    return;
}

$mfvv_embed_html = wp_oembed_get( $mfvv_vimeo_url, [
    'width' => 1340,
] );

if ( ! $mfvv_embed_html ) {
    return;
}
?>
<div class="mfvv-player alignwide">
    <?php echo $mfvv_embed_html; ?>
</div>
