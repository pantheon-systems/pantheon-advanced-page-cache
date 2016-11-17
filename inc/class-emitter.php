<?php
/**
 * Generates and emits surrogate keys based on the current request.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

namespace Pantheon_Advanced_Page_Cache;

/**
 * Generates and emits surrogate keys based on the current request.
 */
class Emitter {

	/**
	 * Current instance when set.
	 *
	 * @var Emitter
	 */
	private static $instance;

	/**
	 * REST API surrogate keys to emit.
	 *
	 * @var array
	 */
	private $rest_api_surrogate_keys = array();

	/**
	 * Header key.
	 *
	 * @var string
	 */
	const HEADER_KEY = 'Surrogate-Key';

	/**
	 * Get a copy of the current instance.
	 *
	 * @return Emitter
	 */
	private static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Render surrogate keys after the main query has run
	 */
	public static function action_wp() {

		$keys = self::get_main_query_surrogate_keys();
		if ( ! empty( $keys ) ) {
			// @codingStandardsIgnoreStart
			@header( self::HEADER_KEY . ': ' . implode( ' ', $keys ) );
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Register filters to sniff surrogate keys out of REST API responses.
	 */
	public static function action_rest_api_init() {
		self::get_instance()->rest_api_surrogate_keys = array();
		foreach ( get_post_types( array( 'show_in_rest' => true ), 'names' ) as $post_type ) {
			add_filter( "rest_prepare_{$post_type}", array( __CLASS__, 'filter_rest_prepare_post' ), 10, 3 );
		}
		foreach ( get_taxonomies( array( 'show_in_rest' => true ), 'names' ) as $taxonomy ) {
			add_filter( "rest_prepare_{$taxonomy}", array( __CLASS__, 'filter_rest_prepare_term' ), 10, 3 );
		}
		add_filter( 'rest_prepare_user', array( __CLASS__, 'filter_rest_prepare_user' ), 10, 3 );
	}

	/**
	 * Render surrogate keys after a REST API response is prepared
	 *
	 * @param WP_HTTP_Response $result  Result to send to the client. Usually a WP_REST_Response.
	 * @param WP_REST_Server   $server  Server instance.
	 */
	public static function filter_rest_post_dispatch( $result, $server ) {

		$keys = self::get_rest_api_surrogate_keys();
		if ( ! empty( $keys ) ) {
			$server->send_header( self::HEADER_KEY, implode( ' ', $keys ) );
		}
		return $result;
	}

	/**
	 * Determine which posts are present in a REST API response.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Post          $post     Post object.
	 * @param WP_REST_Request  $request  Request object.
	 */
	public static function filter_rest_prepare_post( $response, $post, $request ) {
		self::get_instance()->rest_api_surrogate_keys[] = 'post-' . $post->ID;
		return $response;
	}

	/**
	 * Determine which terms are present in a REST API response.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Post          $term     Term object.
	 * @param WP_REST_Request  $request  Request object.
	 */
	public static function filter_rest_prepare_term( $response, $term, $request ) {
		self::get_instance()->rest_api_surrogate_keys[] = 'term-' . $term->term_id;
		return $response;
	}

	/**
	 * Determine which users are present in a REST API response.
	 *
	 * @param WP_REST_Response $response The response object.
	 * @param WP_Post          $user     User object.
	 * @param WP_REST_Request  $request  Request object.
	 */
	public static function filter_rest_prepare_user( $response, $user, $request ) {
		self::get_instance()->rest_api_surrogate_keys[] = 'user-' . $user->ID;
		return $response;
	}

	/**
	 * Get the surrogate keys to be included in this view.
	 *
	 * Surrogate keys are generated based on the main WP_Query.
	 *
	 * @return array
	 */
	public static function get_main_query_surrogate_keys() {
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
		 * @param array $keys Existing surrogate keys generated by the plugin.
		 */
		$keys = array_unique( $keys );
		$keys = apply_filters( 'pantheon_wp_main_query_surrogate_keys', $keys );
		$keys = array_unique( $keys );
		return $keys;
	}

	/**
	 * Get the surrogate keys to be included in this view.
	 *
	 * Surrogate keys are generated based on filters added to REST API controllers.
	 *
	 * @return array
	 */
	public static function get_rest_api_surrogate_keys() {

		/**
		 * Customize surrogate keys sent in the REST API header.
		 *
		 * @param array $keys Existing surrogate keys generated by the plugin.
		 */
		$keys = self::get_instance()->rest_api_surrogate_keys;
		$keys = array_unique( $keys );
		$keys = apply_filters( 'pantheon_wp_rest_api_surrogate_keys', $keys );
		$keys = array_unique( $keys );
		return $keys;
	}

}
