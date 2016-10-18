<?php

namespace Pantheon_Integrated_CDN;

/**
 * Purges the appropriate surrogate key based on the event.
 */
class Purger {

	public static function clear_keys( $keys = array() ) {

		do_action( 'pantheon_integrated_cdn_clear_keys', $keys );

		if ( function_exists( 'pantheon_clear_edge_keys' ) ) {
			pantheon_clear_edge_keys( $keys );
		}
	}

	public static function action_clean_post_cache( $post_id ) {
		$keys = array(
			'home',
			'front',
			'post-' . $post_id,
		);
		$post = get_post( $post_id );
		if ( $post ) {
			$keys[] = 'user-' . $post->post_author;
		}
		self::clear_keys( $keys );
	}

	public static function action_clean_term_cache( $term_ids ) {
		$keys = array();
		$term_ids = is_array( $term_ids ) ? $term_ids : array( $term_id );
		foreach ( $term_ids as $term_id ) {
			$keys[] = 'term-' . $term_id;
		}
		self::clear_keys( $keys );
	}

	public static function action_clean_user_cache( $user_id ) {
		$keys = array(
			'user-' . $user_id,
		);
		self::clear_keys( $keys );
	}

}
