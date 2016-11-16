<?php
/**
 * Purges the cache based on a variety of WordPress events.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

namespace Pantheon_Advanced_Page_Cache;

/**
 * Purges the appropriate surrogate key based on the event.
 */
class Purger {

	/**
	 * Purge surrogate keys associated with a post being updated.
	 *
	 * @param integer $post_id ID for the modified post.
	 */
	public static function action_wp_insert_post( $post_id ) {
		if ( 'publish' !== get_post_status( $post_id ) ) {
			return;
		}
		self::purge_post_with_related( $post_id );
	}

	/**
	 * Purge surrogate keys associated with a post being published or unpublished.
	 *
	 * @param string  $new_status New status for the post.
	 * @param string  $old_status Old status for the post.
	 * @param WP_Post $post Post object.
	 */
	public static function action_transition_post_status( $new_status, $old_status, $post ) {
		if ( 'publish' !== $new_status && 'publish' !== $old_status ) {
			return;
		}
		self::purge_post_with_related( $post->ID );
	}

	/**
	 * Purge surrogate keys associated with a post being deleted.
	 *
	 * @param integer $post_id ID for the post to be deleted.
	 */
	public static function action_before_delete_post( $post_id ) {
		self::purge_post_with_related( $post_id );
	}

	/**
	 * Purge surrogate keys associated with an attachment being deleted.
	 *
	 * @param integer $post_id ID for the modified attachment.
	 */
	public static function action_delete_attachment( $post_id ) {
		self::purge_post_with_related( $post_id );
	}

	/**
	 * Purge the post's surrogate key when the post cache is cleared.
	 *
	 * @param integer $post_id ID for the modified post.
	 */
	public static function action_clean_post_cache( $post_id ) {
		$type = get_post_type( $post_id );
		// Ignore revisions, which aren't ever displayed on the site.
		if ( $type && 'revision' === $type ) {
			return;
		}
		pantheon_wp_clear_edge_keys( array( 'post-' . $post_id ) );
	}

	/**
	 * Purge surrogate keys associated with a term being created.
	 *
	 * @param integer $term_id ID for the created term.
	 */
	public static function action_created_term( $term_id ) {
		self::purge_term( $term_id );
	}

	/**
	 * Purge surrogate keys associated with a term being edited.
	 *
	 * @param integer $term_id ID for the edited term.
	 */
	public static function action_edited_term( $term_id ) {
		self::purge_term( $term_id );
	}

	/**
	 * Purge surrogate keys associated with a term being deleted.
	 *
	 * @param integer $term_id ID for the deleted term.
	 */
	public static function action_delete_term( $term_id ) {
		self::purge_term( $term_id );
	}

	/**
	 * Purge the term's archive surrogate key when the term is modified.
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
	 * Purge the surrogate keys associated with a post being modified.
	 *
	 * @param integer $post_id ID for the modified post.
	 */
	private static function purge_post_with_related( $post_id ) {
		$type = get_post_type( $post_id );
		// Ignore revisions, which aren't ever displayed on the site.
		if ( $type && 'revision' === $type ) {
			return;
		}
		$keys = array(
			'home',
			'front',
			'post-' . $post_id,
		);
		$post = get_post( $post_id );
		if ( $post ) {
			if ( post_type_supports( $post->post_type, 'author' ) ) {
				$keys[] = 'user-' . $post->post_author;
			}
			$taxonomies = wp_list_filter( get_object_taxonomies( $post->post_type, 'objects' ), array( 'public' => true ) );
			foreach ( $taxonomies as $taxonomy ) {
				$terms = get_the_terms( $post, $taxonomy->name );
				if ( $terms ) {
					foreach ( $terms as $term ) {
						$keys[] = 'term-' . $term->term_id;
					}
				}
			}
		}
		pantheon_wp_clear_edge_keys( $keys );
	}

	/**
	 * Purge the surrogate keys associated with a term being modified.
	 *
	 * @param integer $term_id ID for the modified term.
	 */
	private static function purge_term( $term_id ) {
		pantheon_wp_clear_edge_keys( array( 'term-' . $term_id, 'post-term-' . $term_id ) );
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
