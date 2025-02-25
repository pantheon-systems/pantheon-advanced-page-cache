<?php

require_once __DIR__ . '/class-pantheon-wp-unittest-factory.php';

/**
 * Common testcase abstractions.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

use Pantheon_Advanced_Page_Cache\Emitter;

/**
 * Class from which all tests inherit.
 */
#[AllowDynamicProperties]
class Pantheon_Advanced_Page_Cache_Testcase extends WP_UnitTestCase {

	/**
	 * Surrogate keys cleared by the Purger (reported on an action).
	 *
	 * @var array
	 */
	protected $cleared_keys = [];

	/**
	 * Mapping of views to their surrogate keys.
	 *
	 * @var array
	 */
	protected $view_surrogate_keys = [];

	/**
	 * Sets up the testcase.
	 */
	public function setUp(): void {
		parent::setUp();
		// Use our own factory class to avoid a PHP 8.0 deprecation warning.
		$this->factory = new Pantheon_WP_UnitTest_Factory();

		$this->factory->product_category = new WP_UnitTest_Factory_For_Term( $this->factory, 'product_category' );

		$this->setup_permalink_structure();

		$this->user_id1  = $this->factory->user->create(
			[
				'role'          => 'author',
				'user_nicename' => 'first-user',
			]
		);
		$this->user_id2  = $this->factory->user->create(
			[
				'role'          => 'author',
				'user_nicename' => 'second-user',
			]
		);
		$this->user_id3  = $this->factory->user->create(
			[
				'role'          => 'author',
				'user_nicename' => 'third-user',
			]
		);
		$this->admin_id1 = $this->factory->user->create(
			[
				'role'          => 'administrator',
				'user_nicename' => 'first-admin',
			]
		);

		$this->tag_id1      = $this->factory->tag->create(
			[
				'slug' => 'first-tag',
			]
		);
		$this->tag_id2      = $this->factory->tag->create(
			[
				'slug' => 'second-tag',
			]
		);
		$this->category_id1 = 1; // This is the default 'uncategorized' category.
		$this->category_id2 = $this->factory->category->create(
			[
				'slug' => 'second-category',
			]
		);

		$this->product_category_id1 = $this->factory->product_category->create(
			[
				'slug' => 'first-product-category',
			]
		);
		$this->product_category_id2 = $this->factory->product_category->create(
			[
				'slug' => 'second-product-category',
			]
		);
		$this->product_category_id3 = $this->factory->product_category->create(
			[
				'slug' => 'third-product-category',
			]
		);

		$this->post_id1 = $this->factory->post->create(
			[
				'post_status'   => 'publish',
				'post_author'   => $this->user_id1,
				'post_date'     => '2016-10-14 12:00',
				'post_date_gmt' => '2016-10-14 12:00',
				'post_name'     => 'first-post',
			]
		);
		wp_set_object_terms( $this->post_id1, [ $this->tag_id2 ], 'post_tag' );
		$this->post_id2    = $this->factory->post->create(
			[
				'post_status'   => 'publish',
				'post_author'   => $this->user_id2,
				'post_date'     => '2016-10-14 11:00',
				'post_date_gmt' => '2016-10-14 11:00',
				'post_name'     => 'second-post',
			]
		);
		$this->post_id3    = $this->factory->post->create(
			[
				'post_status'   => 'publish',
				'post_author'   => $this->user_id2,
				'post_date'     => '2016-10-15 11:00',
				'post_date_gmt' => '2016-10-15 11:00',
				'post_name'     => 'third-post',
			]
		);
		$this->post_id4    = $this->factory->post->create(
			[
				'post_status'   => 'draft',
				'post_author'   => $this->user_id2,
				'post_date'     => '2016-10-15 11:00',
				'post_date_gmt' => '2016-10-15 11:00',
				'post_name'     => 'fourth-post',
			]
		);
		$this->page_id1    = $this->factory->post->create(
			[
				'post_status' => 'publish',
				'post_type'   => 'page',
				'post_author' => $this->user_id1,
				'post_name'   => 'first-page',
			]
		);
		$this->product_id1 = $this->factory->post->create(
			[
				'post_status'   => 'publish',
				'post_type'     => 'product',
				'post_author'   => $this->user_id1,
				'post_date'     => '2016-10-14 12:00',
				'post_date_gmt' => '2016-10-14 12:00',
				'post_name'     => 'first-product',
			]
		);
		wp_set_object_terms( $this->product_id1, [ $this->product_category_id2 ], 'product_category' );
		$this->product_id2 = $this->factory->post->create(
			[
				'post_status'   => 'publish',
				'post_type'     => 'product',
				'post_author'   => $this->user_id2,
				'post_date'     => '2016-10-14 11:00',
				'post_date_gmt' => '2016-10-14 11:00',
				'post_name'     => 'second-product',
			]
		);
		wp_set_object_terms( $this->product_id2, [ $this->product_category_id1 ], 'product_category' );

		$this->comment_id1 = $this->factory->comment->create(
			[
				'comment_post_ID'  => $this->post_id1,
				'comment_approved' => 1,
				'user_id'          => 0,
			]
		);

		$this->attachment_id1 = $this->factory->post->create(
			[
				'post_mime_type' => 'image/jpeg',
				'post_type'      => 'attachment',
				'post_author'    => $this->user_id1,
				'post_status'    => 'publish',
				'post_parent'    => 0,
			]
		);

		$this->cleared_keys = [];
		$this->setup_view_surrogate_keys();
		Emitter::reset_rest_api_surrogate_keys();

		add_action( 'pantheon_wp_clear_edge_keys', [ $this, 'action_pantheon_wp_clear_edge_keys' ] );
	}

	/**
	 * Hooks into the 'pantheon_wp_clear_edge_keys' to listen to cleared keys.
	 *
	 * @param array $keys Surrogate keys being cleared.
	 */
	public function action_pantheon_wp_clear_edge_keys( $keys ) {
		$this->cleared_keys = array_merge( $this->cleared_keys, $keys );
	}

	/**
	 * Sets up the permalink structure so our tests have pretty permalinks.
	 */
	private function setup_permalink_structure() {
		global $wp_rewrite;

		$structure = '%year%/%monthnum%/%day%/%postname%/';
		update_option( 'permalink_structure', $structure );

		$wp_rewrite->init();
		$wp_rewrite->set_permalink_structure( $structure );

		create_initial_taxonomies();
		$this->register_custom_types();
		
		// phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.flush_rules_flush_rules
		$wp_rewrite->flush_rules();
	}

	/**
	 * Sets up the REST API server object
	 */
	protected function setup_rest_api_server() {
		$GLOBALS['wp_rest_server'] = new Spy_REST_Server();
		$this->server              = $GLOBALS['wp_rest_server'];
		do_action( 'rest_api_init' );
	}

	/**
	 * Primes the mapping of views to their surrogate keys.
	 */
	protected function setup_view_surrogate_keys() {
		$this->view_surrogate_keys = [];
		// Primes the mapping of views to their surrogate keys.
		$views           = [
			home_url( '/' ), // Homepage.
			'/products/', // Product post type archive.
			'/2016/10/14/', // Day archive with posts.
			'/2015/10/15/', // Day archive without posts.
			'/2016/10/', // Month archive with posts.
			'/2015/10/', // Month archive without posts.
			'/2016/', // Year archive with posts.
			'/2015/', // Year archive without posts.
			'/feed/', // Basic RSS feed.
		];
		$rest_api_routes = [];
		
		// phpcs:disable WordPressVIPMinimum.Functions.RestrictedFunctions.get_posts_get_posts
		$posts = get_posts(
			[
				'post_type'      => 'any',
				'post_status'    => 'any',
				'posts_per_page' => -1,
			]
		);
		foreach ( $posts as $post ) {
			$views[]          = get_permalink( $post->ID );
			$post_type_object = get_post_type_object( $post->post_type );
			if ( ! empty( $post_type_object->show_in_rest ) ) {
				$base              = ! empty( $post_type_object->rest_base ) ? $post_type_object->rest_base : $post_type_object->name;
				$rest_api_routes[] = '/wp/v2/' . $base;
				$rest_api_routes[] = '/wp/v2/' . $base . '/' . $post->ID;
			}
		}
		$users = get_users(
			[
				'fields' => 'ids',
			]
		);
		foreach ( $users as $user_id ) {
			$views[]           = get_author_posts_url( $user_id );
			$rest_api_routes[] = '/wp/v2/users';
			$rest_api_routes[] = '/wp/v2/users/' . $user_id;
		}
		
		// TODO(pwt): PHP CS flags this 'hide_empty' as deprecated but removing it breaks at least one test. Shelving the fix for now.
		// phpcs:disable WordPress.WP.DeprecatedParameters.Get_termsParam2Found
		$terms = get_terms(
			[ 'post_tag', 'category', 'product_category' ],
			[
				'hide_empty' => false,
			]
		);
		foreach ( $terms as $term ) {
			$views[]         = get_term_link( $term );
			$taxonomy_object = get_taxonomy( $term->taxonomy );
			if ( ! empty( $taxonomy_object->show_in_rest ) ) {
				$base              = ! empty( $taxonomy_object->rest_base ) ? $taxonomy_object->rest_base : $taxonomy_object->name;
				$rest_api_routes[] = '/wp/v2/' . $base;
				$rest_api_routes[] = '/wp/v2/' . $base . '/' . $term->term_id;
			}
		}
		$rest_api_routes[] = '/wp/v2/media';
		$rest_api_routes[] = '/wp/v2/media/' . $this->attachment_id1;
		$rest_api_routes[] = '/wp/v2/comments';
		$rest_api_routes[] = '/wp/v2/comments/' . $this->comment_id1;
		$views             = array_unique( $views );
		foreach ( $views as $view ) {
			$path  = wp_parse_url( $view, PHP_URL_PATH );
			$query = wp_parse_url( $view, PHP_URL_QUERY );
			if ( $query ) {
				$path .= '?' . $query;
			}
			$this->go_to( $view );
			$this->view_surrogate_keys[ $path ] = Emitter::get_main_query_surrogate_keys();
		}
		if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
			return;
		}
		$this->setup_rest_api_server();
		$rest_api_routes = array_unique( $rest_api_routes );
		foreach ( $rest_api_routes as $rest_api_route ) {
			$request = new WP_REST_Request( 'GET', $rest_api_route );
			$this->server->dispatch( $request );
			$this->view_surrogate_keys[ '/wp-json' . $rest_api_route ] = Emitter::get_rest_api_surrogate_keys();
			Emitter::reset_rest_api_surrogate_keys();
		}
		// Routes with no items present.
		$request = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$request->set_param( 'author', REST_TESTS_IMPOSSIBLY_HIGH_NUMBER );
		$this->server->dispatch( $request );
		$this->view_surrogate_keys[ '/wp-json/wp/v2/posts?author=' . REST_TESTS_IMPOSSIBLY_HIGH_NUMBER ] = Emitter::get_rest_api_surrogate_keys();
		Emitter::reset_rest_api_surrogate_keys();
		$request = new WP_REST_Request( 'GET', '/wp/v2/pages' );
		$request->set_param( 'parent', REST_TESTS_IMPOSSIBLY_HIGH_NUMBER );
		$this->server->dispatch( $request );
		$this->view_surrogate_keys[ '/wp-json/wp/v2/pages?parent=' . REST_TESTS_IMPOSSIBLY_HIGH_NUMBER ] = Emitter::get_rest_api_surrogate_keys();
		Emitter::reset_rest_api_surrogate_keys();
		$request = new WP_REST_Request( 'GET', '/wp/v2/media' );
		$request->set_param( 'parent', REST_TESTS_IMPOSSIBLY_HIGH_NUMBER );
		$this->server->dispatch( $request );
		$this->view_surrogate_keys[ '/wp-json/wp/v2/media?parent=' . REST_TESTS_IMPOSSIBLY_HIGH_NUMBER ] = Emitter::get_rest_api_surrogate_keys();
		Emitter::reset_rest_api_surrogate_keys();
		$request = new WP_REST_Request( 'GET', '/wp/v2/comments' );
		$request->set_param( 'post', REST_TESTS_IMPOSSIBLY_HIGH_NUMBER );
		$this->server->dispatch( $request );
		$this->view_surrogate_keys[ '/wp-json/wp/v2/comments?post=' . REST_TESTS_IMPOSSIBLY_HIGH_NUMBER ] = Emitter::get_rest_api_surrogate_keys();
		Emitter::reset_rest_api_surrogate_keys();
		// Settings route needs an authenticated request.
		$current_user_id = get_current_user_id();
		wp_set_current_user( $this->admin_id1 );
		$rest_api_route = '/wp/v2/settings';
		$request        = new WP_REST_Request( 'GET', $rest_api_route );
		$this->server->dispatch( $request );
		$this->view_surrogate_keys[ '/wp-json' . $rest_api_route ] = Emitter::get_rest_api_surrogate_keys();
		Emitter::reset_rest_api_surrogate_keys();
		wp_set_current_user( $current_user_id );
	}

	/**
	 * Register custom post types and taxonomies.
	 */
	private function register_custom_types() {
		register_post_type(
			'product',
			[
				'public'       => true,
				'has_archive'  => 'products',
				'show_in_rest' => true,
			]
		);
		register_taxonomy(
			'product_category',
			[ 'product' ],
			[
				'public'       => true,
				'rewrite'      => [
					'slug' => 'product-category',
				],
				'show_in_rest' => true,
			]
		);
	}

	/**
	 * Assert cleared keys to match the expected set.
	 *
	 * @param array $expected Surrogate keys expected to be cleared.
	 */
	protected function assertClearedKeys( $expected ) {
		$actual = array_unique( $this->cleared_keys );
		// Drop rest- surrogate keys when <WP 4.7.
		if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
			foreach ( $expected as $k => $v ) {
				if ( 0 === stripos( $v, 'rest-' ) ) {
					unset( $expected[ $k ] );
				}
			}
			foreach ( $actual as $k => $v ) {
				if ( 0 === stripos( $v, 'rest-' ) ) {
					unset( $actual[ $k ] );
				}
			}
		}
		$this->assertArrayValues( $expected, $actual );
	}

	/**
	 * Assert URIs purged by cleared keys to match the expected set.
	 *
	 * @param array $expected URIs expected to be cleared based on cleared keys.
	 */
	protected function assertPurgedURIs( $expected ) {
		$actual = [];
		foreach ( $this->view_surrogate_keys as $view => $keys ) {
			if ( array_intersect( $keys, $this->cleared_keys ) ) {
				$actual[] = $view;
			}
		}
		// Drop /wp-json/ URLs when <WP 4.7.
		if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
			foreach ( $expected as $k => $v ) {
				if ( 0 === stripos( $v, '/wp-json/' ) ) {
					unset( $expected[ $k ] );
				}
			}
		}
		$this->assertArrayValues( $expected, $actual );
	}

	/**
	 * Assert expected array values to match actual array values.
	 *
	 * Improves upon assertEquals by ensuring arrays are in similar order.
	 *
	 * @param array $expected Expected array values.
	 * @param array $actual Actual array values.
	 */
	protected function assertArrayValues( $expected, $actual ) {
		sort( $expected );
		sort( $actual );
		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Tear down behaviors after the tests have completed.
	 */
	public function tearDown(): void {
		$this->cleared_keys = [];
		remove_action( 'pantheon_wp_clear_edge_keys', [ $this, 'action_pantheon_wp_clear_edge_keys' ] );
		_unregister_post_type( 'product' );
		_unregister_taxonomy( 'product_category' );
		parent::tearDown();
	}
}
