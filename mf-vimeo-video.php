<?php
/*
Plugin Name: Vimeo Video CPT
Plugin URI: https://github.com/miszou/vimeo-video
GitHub Plugin URI: miszou/vimeo-video
Description: Registers a Vimeo Video custom post type with Media Tag taxonomy, filterable and searchable via REST API.
Version: 0.1
Author: miszou
Text Domain: mf-vimeo-video
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/includes/class-mfvv-template.php';
MFVV_Template::init();

// Flush rewrite rules on activation so video URLs work immediately
function mfvv_activate() {
    mfvv_register_media_tag_taxonomy();
    mfvv_register_video_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'mfvv_activate' );

// Register shared taxonomy (prefixed slug to avoid conflicts)
function mfvv_register_media_tag_taxonomy() {
    $labels = [
        'name'          => __( 'Media Tags', 'mf-vimeo-video' ),
        'singular_name' => __( 'Media Tag', 'mf-vimeo-video' ),
        'search_items'  => __( 'Search Media Tags', 'mf-vimeo-video' ),
        'all_items'     => __( 'All Media Tags', 'mf-vimeo-video' ),
        'edit_item'     => __( 'Edit Media Tag', 'mf-vimeo-video' ),
        'add_new_item'  => __( 'Add New Media Tag', 'mf-vimeo-video' ),
    ];

    register_taxonomy(
        'mfvv_media_tag',
        [ 'post', 'mfvv_video' ],
        [
            'labels'       => $labels,
            'hierarchical' => false,
            'public'       => true,
            'rewrite'      => [ 'slug' => 'media-tag' ],
            'show_in_rest' => true,
        ]
    );
}
add_action( 'init', 'mfvv_register_media_tag_taxonomy' );

// Register Video CPT (prefixed slug to avoid conflicts)
function mfvv_register_video_cpt() {
    $labels = [
        'name'          => __( 'Videos', 'mf-vimeo-video' ),
        'singular_name' => __( 'Video', 'mf-vimeo-video' ),
        'add_new'       => __( 'Add New Video', 'mf-vimeo-video' ),
        'add_new_item'  => __( 'Add New Video', 'mf-vimeo-video' ),
        'edit_item'     => __( 'Edit Video', 'mf-vimeo-video' ),
        'view_item'     => __( 'View Video', 'mf-vimeo-video' ),
        'search_items'  => __( 'Search Videos', 'mf-vimeo-video' ),
        'not_found'     => __( 'No videos found', 'mf-vimeo-video' ),
    ];

    register_post_type( 'mfvv_video', [
        'labels'       => $labels,
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-video-alt3',
        'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
        'rewrite'      => [ 'slug' => 'videos' ],
        'show_in_rest' => true,
        'rest_base'    => 'videos',
        'taxonomies'   => [ 'mfvv_media_tag' ],
    ] );
}
add_action( 'init', 'mfvv_register_video_cpt' );

// Register post meta for REST API access (enables filtering/searching)
function mfvv_register_meta() {
    register_post_meta( 'mfvv_video', 'mfvv_vimeo_url', [
        'show_in_rest'  => true,
        'single'        => true,
        'type'          => 'string',
        'auth_callback' => function () {
            return current_user_can( 'edit_posts' );
        },
    ] );
}
add_action( 'init', 'mfvv_register_meta' );

// Add meta box for Vimeo URL
function mfvv_video_meta_box() {
    add_meta_box(
        'mfvv_vimeo_url',
        __( 'Vimeo Video URL', 'mf-vimeo-video' ),
        'mfvv_video_meta_box_html',
        'mfvv_video',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'mfvv_video_meta_box' );

function mfvv_video_meta_box_html( $post ) {
    $vimeo_url = get_post_meta( $post->ID, 'mfvv_vimeo_url', true );
    wp_nonce_field( 'mfvv_save_vimeo_url', 'mfvv_vimeo_url_nonce' );
    echo '<label for="mfvv_vimeo_url">' . esc_html__( 'Vimeo Video URL', 'mf-vimeo-video' ) . '</label>';
    echo '<input type="url" id="mfvv_vimeo_url" name="mfvv_vimeo_url" value="' .
        esc_url( $vimeo_url ) .
        '" style="width:100%" placeholder="https://vimeo.com/123456789" />';
}

function mfvv_save_vimeo_url( $post_id ) {
    if (
        ! isset( $_POST['mfvv_vimeo_url_nonce'] ) ||
        ! wp_verify_nonce( $_POST['mfvv_vimeo_url_nonce'], 'mfvv_save_vimeo_url' )
    ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    if ( isset( $_POST['mfvv_vimeo_url'] ) ) {
        $new_url = esc_url_raw( wp_unslash( $_POST['mfvv_vimeo_url'] ) );
        $old_url = get_post_meta( $post_id, 'mfvv_vimeo_url', true );

        update_post_meta( $post_id, 'mfvv_vimeo_url', $new_url );

        // Auto-fetch Vimeo thumbnail when URL changes and no featured image is set
        if ( $new_url && $new_url !== $old_url && ! has_post_thumbnail( $post_id ) ) {
            mfvv_fetch_vimeo_thumbnail( $post_id, $new_url );
        }
    }
}
add_action( 'save_post_mfvv_video', 'mfvv_save_vimeo_url' );

// Fetch Vimeo thumbnail via oEmbed and set as featured image
function mfvv_fetch_vimeo_thumbnail( $post_id, $vimeo_url ) {
    $oembed_url = 'https://vimeo.com/api/oembed.json?url=' . urlencode( $vimeo_url );
    $response   = wp_remote_get( $oembed_url, [ 'timeout' => 10 ] );

    if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
        return;
    }

    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    if ( empty( $data['thumbnail_url'] ) ) {
        return;
    }

    // Download and sideload the thumbnail into the media library
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';

    $tmp_file = download_url( $data['thumbnail_url'] );

    if ( is_wp_error( $tmp_file ) ) {
        return;
    }

    $file_array = [
        'name'     => sanitize_file_name( get_the_title( $post_id ) ) . '.jpg',
        'tmp_name' => $tmp_file,
    ];

    $attachment_id = media_handle_sideload( $file_array, $post_id );

    if ( is_wp_error( $attachment_id ) ) {
        @unlink( $tmp_file );
        return;
    }

    set_post_thumbnail( $post_id, $attachment_id );
}

// Enable filtering by media_tag in REST API queries
function mfvv_rest_query_filter( $args, $request ) {
    if ( ! empty( $request['mfvv_media_tag'] ) ) {
        $args['tax_query'][] = [
            'taxonomy' => 'mfvv_media_tag',
            'field'    => 'slug',
            'terms'    => array_map( 'sanitize_text_field', (array) $request['mfvv_media_tag'] ),
        ];
    }
    return $args;
}
add_filter( 'rest_mfvv_video_query', 'mfvv_rest_query_filter', 10, 2 );

// Register custom REST query parameter
function mfvv_rest_query_params( $params ) {
    $params['mfvv_media_tag'] = [
        'description' => __( 'Filter by media tag slug.', 'mf-vimeo-video' ),
        'type'        => 'array',
        'items'       => [ 'type' => 'string' ],
    ];
    return $params;
}
add_filter( 'rest_mfvv_video_collection_params', 'mfvv_rest_query_params' );
