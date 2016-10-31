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
			// @codingStandardsIgnoreStart
			@header( 'Surrogate-Key: ' . implode( ' ', $keys ) );
			// @codingStandardsIgnoreEnd
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
		if ( is_date() ) {
			$keys[] = 'date';
		}
		if ( is_paged() ) {
			$keys[] = 'paged';
		}
		if ( is_search() ) {
			$keys[] = 'search';
		}

		if ( ! empty( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $p ) {
				$keys[] = 'post-' . $p->ID;
				if ( post_type_supports( $p->post_type, 'author' ) ) {
					$keys[] = 'user-' . $p->post_author;
				}
				if ( $wp_query->is_singular() || $wp_query->is_page() ) {
					foreach ( get_object_taxonomies( $p ) as $tax ) {
						$terms = get_the_terms( $p->ID, $tax );
						if ( $terms && ! is_wp_error( $terms ) ) {
							foreach ( $terms as $t ) {
								$keys[] = 'term-' . $t->term_id;
							}
						}
					}
				}
			}
		}

		if ( is_singular() || is_page() ) {
			$keys[] = 'single';
			if ( is_attachment() ) {
				$keys[] = 'attachment';
			}
		} elseif ( is_archive() ) {
			$keys[] = 'archive';
			if ( is_post_type_archive() ) {
				$keys[] = 'post-type-archive';
			} elseif ( is_author() ) {
				if ( $user_id = get_queried_object_id() ) {
					$keys[] = 'user-' . $user_id;
				}
			} elseif ( is_category() || is_tag() || is_tax() ) {
				if ( $term_id = get_queried_object_id() ) {
					$keys[] = 'term-' . $term_id;
				}
			}
		}

		$keys = array_unique( $keys );
		return $keys;
	}

}
