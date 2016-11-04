<?php
/**
 * Purges the cache based on a variety of WordPress events.
 *
 * @package Pantheon_Integrated_CDN
 */

namespace Pantheon_Integrated_CDN;

/**
 * Purges the appropriate surrogate key based on the event.
 */
class Purger {

	/**
	 * Purge a variety of surrogate keys when a post is modified.
	 *
	 * @param integer $post_id ID for the modified post.
	 */
	public static function action_clean_post_cache( $post_id ) {
		$keys = array(
			'home',
			'front',
			'post-' . $post_id,
		);
		$post = get_post( $post_id );
		if ( $post && post_type_supports( $post->post_type, 'author' ) ) {
			$keys[] = 'archive-user-' . $post->post_author;
		}
		pantheon_wp_clear_edge_keys( $keys );
	}

	/**
	 * Purge a variety of surrogate keys when a term is modified.
	 *
	 * @param integer $term_ids One or more IDs of modified terms.
	 */
	public static function action_clean_term_cache( $term_ids ) {
		$keys = array();
		$term_ids = is_array( $term_ids ) ? $term_ids : array( $term_id );
		foreach ( $term_ids as $term_id ) {
			$keys[] = 'term-' . $term_id;
		}
		pantheon_wp_clear_edge_keys( $keys );
	}

	/**
	 * Purge a variety of surrogate keys when a user is modified.
	 *
	 * @param integer $user_id ID for the modified user.
	 */
	public static function action_clean_user_cache( $user_id ) {
		$keys = array(
			'user-' . $user_id,
		);
		pantheon_wp_clear_edge_keys( $keys );
	}

}
