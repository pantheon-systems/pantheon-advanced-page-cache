<?php
/**
 * Generates and emits surrogate keys based on the current request.
 *
 * @package Pantheon_Integrated_CDN
 */

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

	/**
	 * Get the surrogate keys to be included in this view.
	 *
	 * Surrogate keys are generated based on the main WP_Query.
	 *
	 * @return array
	 */
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
			if ( $wp_query->found_posts ) {
				$keys[] = 'search-results';
			} else {
				$keys[] = 'search-no-results';
			}
		}

		if ( ! empty( $wp_query->posts ) ) {
			foreach ( $wp_query->posts as $p ) {
				$keys[] = 'post-' . $p->ID;
				if ( $wp_query->is_singular() || $wp_query->is_page() ) {
					if ( post_type_supports( $p->post_type, 'author' ) ) {
						$keys[] = 'post-user-' . $p->post_author;
					}
					foreach ( get_object_taxonomies( $p ) as $tax ) {
						$terms = get_the_terms( $p->ID, $tax );
						if ( $terms && ! is_wp_error( $terms ) ) {
							foreach ( $terms as $t ) {
								$keys[] = 'post-term-' . $t->term_id;
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

		/**
		 * Customize surrogate keys sent in the header.
		 *
		 * @param array $keys Existing surrogate keys generate by the plugin.
		 */
		$keys = apply_filters( 'pantheon_wp_surrogate_keys', $keys );
		$keys = array_unique( $keys );
		return $keys;
	}

}
