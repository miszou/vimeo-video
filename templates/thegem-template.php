<?php
/**
 * Wrapper for rendering a selected TheGem Template Builder template on a video.
 *
 * @package MF_Vimeo_Video
 */

if (!defined('ABSPATH')) {
    exit;
}

get_header();
?>

<main id="primary" class="site-main mfvv-thegem-template-wrapper">
    <?php
    while (have_posts()) {
        the_post();

        if (!MFVV_Template::render_selected_thegem_template()) {
            the_content();
        }
    }
    ?>
</main>

<?php
get_footer();
