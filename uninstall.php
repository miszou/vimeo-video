<?php
/**
 * Uninstall handler for Vimeo Video CPT plugin.
 *
 * Removes all plugin data: video posts, sideloaded attachments,
 * orphaned media-tag terms, and flushes rewrite rules.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// --- 1. Delete all mfvv_video posts (and their attachments) in batches ---

$batch_size = 50;

while ( true ) {
	$post_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} WHERE post_type = %s LIMIT %d",
			'mfvv_video',
			$batch_size
		)
	);

	if ( empty( $post_ids ) ) {
		break;
	}

	foreach ( $post_ids as $post_id ) {
		// Delete child attachments (sideloaded Vimeo thumbnails).
		$attachments = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_type = 'attachment'",
				$post_id
			)
		);

		foreach ( $attachments as $attachment_id ) {
			wp_delete_attachment( (int) $attachment_id, true );
		}

		// Force-delete post (skips trash), removes meta, revisions, and term relationships.
		wp_delete_post( (int) $post_id, true );
	}
}

// --- 2. Clean up orphaned mfvv_media_tag terms ---

// Register the taxonomy temporarily so wp_delete_term() works.
register_taxonomy( 'mfvv_media_tag', [ 'post' ] );

$term_ids = $wpdb->get_col(
	"SELECT t.term_id FROM {$wpdb->terms} AS t
	 INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
	 WHERE tt.taxonomy = 'mfvv_media_tag'"
);

foreach ( $term_ids as $term_id ) {
	$count = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->term_relationships} AS tr
			 INNER JOIN {$wpdb->term_taxonomy} AS tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
			 WHERE tt.term_id = %d AND tt.taxonomy = 'mfvv_media_tag'",
			$term_id
		)
	);

	if ( 0 === (int) $count ) {
		wp_delete_term( (int) $term_id, 'mfvv_media_tag' );
	}
}

// --- 3. Flush rewrite rules ---

flush_rewrite_rules();
