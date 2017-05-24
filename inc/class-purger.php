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
	 * @param object  $post    The post object.
	 */
	public static function action_wp_insert_post( $post_id, $post ) {
		if ( 'publish' !== $post->post_status ) {
			return;
		}
		self::purge_post_with_related( $post );
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
		self::purge_post_with_related( $post );
	}

	/**
	 * Purge surrogate keys associated with a post being deleted.
	 *
	 * @param integer $post_id ID for the post to be deleted.
	 */
	public static function action_before_delete_post( $post_id ) {
		$post = get_post( $post_id );
		self::purge_post_with_related( $post );
	}

	/**
	 * Purge surrogate keys associated with an attachment being deleted.
	 *
	 * @param integer $post_id ID for the modified attachment.
	 */
	public static function action_delete_attachment( $post_id ) {
		$post = get_post( $post_id );
		self::purge_post_with_related( $post );
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
		pantheon_wp_clear_edge_keys( array( 'post-' . $post_id, 'rest-post-' . $post_id ) );
	}

	/**
	 * Purge surrogate keys associated with a term being created.
	 *
	 * @param integer $term_id  ID for the created term.
	 * @param int     $tt_id    Term taxonomy ID.
	 * @param string  $taxonomy Taxonomy slug.
	 */
	public static function action_created_term( $term_id, $tt_id, $taxonomy ) {
		self::purge_term( $term_id );
		pantheon_wp_clear_edge_keys( array( 'rest-' . $taxonomy . '-collection' ) );
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
			$keys[] = 'rest-term-' . $term_id;
		}
		pantheon_wp_clear_edge_keys( $keys );
	}

	/**
	 * Purge surrogate keys when an approved comment is updated.
	 *
	 * @param integer    $id      The comment ID.
	 * @param WP_Comment $comment Comment object.
	 */
	public static function action_wp_insert_comment( $id, $comment ) {
		if ( 1 != $comment->comment_approved ) {
			return;
		}
		pantheon_wp_clear_edge_keys( array( 'rest-comment-' . $comment->comment_ID, 'rest-comment-collection' ) );
	}

	/**
	 * Purge surrogate keys when a comment is approved or unapproved.
	 *
	 * @param int|string $new_status The new comment status.
	 * @param int|string $old_status The old comment status.
	 * @param object     $comment    The comment data.
	 */
	public static function action_transition_comment_status( $new_status, $old_status, $comment ) {
		pantheon_wp_clear_edge_keys( array( 'rest-comment-' . $comment->comment_ID, 'rest-comment-collection' ) );
	}

	/**
	 * Purge the comment's surrogate key when the comment is modified.
	 *
	 * @param integer $comment_id Modified comment id.
	 */
	public static function action_clean_comment_cache( $comment_id ) {
		pantheon_wp_clear_edge_keys( array( 'rest-comment-' . $comment_id ) );
	}

	/**
	 * Purge the surrogate keys associated with a post being modified.
	 *
	 * @param object $post Object representing the modified post.
	 */
	private static function purge_post_with_related( $post ) {
		// Ignore revisions, which aren't ever displayed on the site.
		if ( 'revision' === $post->post_type ) {
			return;
		}
		$keys = array(
			'home',
			'front',
			'feed',
			'post-' . $post->ID,
		);
		$keys[] = 'rest-' . $post->post_type . '-collection';
		if ( post_type_supports( $post->post_type, 'author' ) ) {
			$keys[] = 'user-' . $post->post_author;
		}
		if ( post_type_supports( $post->post_type, 'comments' ) ) {
			$keys[] = 'rest-comment-post-' . $post->ID;
		}
		$taxonomies = wp_list_filter( get_object_taxonomies( $post->post_type, 'objects' ), array(
			'public' => true,
		) );
		foreach ( $taxonomies as $taxonomy ) {
			$terms = get_the_terms( $post, $taxonomy->name );
			if ( $terms ) {
				foreach ( $terms as $term ) {
					$keys[] = 'term-' . $term->term_id;
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
		pantheon_wp_clear_edge_keys( array( 'term-' . $term_id, 'rest-term-' . $term_id, 'post-term-' . $term_id ) );
	}


	/**
	 * Purge a variety of surrogate keys when a user is modified.
	 *
	 * @param integer $user_id ID for the modified user.
	 */
	public static function action_clean_user_cache( $user_id ) {
		$keys = array(
			'user-' . $user_id,
			'rest-user-' . $user_id,
		);
		pantheon_wp_clear_edge_keys( $keys );
	}

	/**
	 * Purge a variety of surrogate keys when an option is modified.
	 *
	 * @param string $option Name of the updated option.
	 */
	public static function action_updated_option( $option ) {
		if ( ! function_exists( 'get_registered_settings' ) ) {
			return;
		}
		$settings = get_registered_settings();
		if ( empty( $settings[ $option ] ) || empty( $settings[ $option ]['show_in_rest'] ) ) {
			return;
		}
		$rest_name = ! empty( $settings[ $option ]['show_in_rest']['name'] ) ? $settings[ $option ]['show_in_rest']['name'] : $option;
		pantheon_wp_clear_edge_keys( array( 'rest-setting-' . $rest_name ) );
	}

}
