<?php

namespace Pantheon_Integrated_CDN;

/**
 * Generates and emits surrogate keys based on the current request.
 */
class Emitter {

	/**
	 * Render surrogate keys after the main query has run
	 */
	public static function action_wp() {

		$keys = self::get_surrogate_keys();
		if ( ! empty( $keys ) ) {
			@header( 'Surrogate-Keys: ' . implode( ' ', $keys ) );
		}
	}

	public static function get_surrogate_keys() {
		global $wp_query;

		$keys = array();
		if ( is_front_page() ) {
			$keys[] = 'front';
		}
		if ( is_home() ) {
			$keys[] = 'home';
		}
		if ( is_single() ) {
			$keys[] = 'single';
		}
		if ( is_page() ) {
			$keys[] = 'page';
		}
		if ( is_archive() ) {
			$keys[] = 'archive';
		}
		if ( is_date() ) {
			$keys[] = 'date';
		}
		if ( is_paged() ) {
			$keys[] = 'paged';
		}
		if ( is_attachment() ) {
			$keys[] = 'attachment';
		}

		if ( ! empty( $wp_query->posts ) ) {
			foreach( $wp_query->posts as $p ) {
				$keys[] = 'post-' . $p->ID;
				if ( post_type_supports( $p->post_type, 'author' ) ) {
					$keys[] = 'user-' . $p->post_author;
				}
			}
		}

		return array_unique( $keys );
	}

}
