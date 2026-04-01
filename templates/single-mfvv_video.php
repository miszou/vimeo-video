<?php
/**
 * Classic-theme fallback template for single mfvv_video posts.
 *
 * Renders the block-markup from single-mfvv_video.html through
 * do_blocks() so patterns and block styles work even when the
 * active theme is not a block theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

get_header();

$template_file = __DIR__ . '/single-mfvv_video.html';

if ( file_exists( $template_file ) ) {
    // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    $content = file_get_contents( $template_file );

    // Strip template-part references that only work in block themes
    // (header/footer are already provided by get_header/get_footer).
    $content = preg_replace(
        '/<!--\s*wp:template-part\s*\{[^}]*"slug"\s*:\s*"(header|footer)"[^}]*\}\s*\/-->/',
        '',
        $content
    );

    // Parse and render blocks (resolves patterns, dynamic blocks, etc.)
    echo do_blocks( $content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

get_footer();
