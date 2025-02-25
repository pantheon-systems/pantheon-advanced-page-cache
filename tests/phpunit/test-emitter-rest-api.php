<?php
/**
 * Tests for the Emitter class, as it relates to the REST API.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

use Pantheon_Advanced_Page_Cache\Emitter;

/**
 * Tests for the Emitter class.
 */
class Test_Emitter_REST_API extends Pantheon_Advanced_Page_Cache_Testcase {

	/**
	 * Set up REST API tests.
	 */
	public function setUp(): void {
		parent::setUp();
		if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
			$this->wp_not_supported();
		}
	}

	/**
	 * Skip tests because WP version is not supported.
	 */
	public function wp_not_supported() {
		return $this->markTestSkipped( 'WordPress version not supported.' );
	}

	/**
	 * Ensure GET /wp/v2/posts emits the expected surrogate keys
	 */
	public function test_get_posts() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 3, $response->get_data() );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-post-collection',
					'rest-post-' . $this->post_id1,
					'rest-post-' . $this->post_id2,
					'rest-post-' . $this->post_id3,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-post-collection',
					'blog-1-rest-post-' . $this->post_id1,
					'blog-1-rest-post-' . $this->post_id2,
					'blog-1-rest-post-' . $this->post_id3,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/posts?_embed emits the expected surrogate keys
	 */
	public function test_get_posts_embed() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = $this->server->dispatch( $request );
		$data     = $this->server->response_to_data( $response, true );
		$this->assertCount( 3, $data );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-post-collection',
					'rest-post-' . $this->post_id1,
					'rest-post-' . $this->post_id2,
					'rest-post-' . $this->post_id3,
					'rest-comment-collection',
					'rest-comment-' . $this->comment_id1,
					'rest-comment-post-' . $this->post_id1,
					'rest-category-collection',
					'rest-term-1',
					'rest-post_tag-collection',
					'rest-term-' . $this->tag_id2,
					'rest-user-' . $this->user_id1,
					'rest-user-' . $this->user_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-post-collection',
					'blog-1-rest-post-' . $this->post_id1,
					'blog-1-rest-post-' . $this->post_id2,
					'blog-1-rest-post-' . $this->post_id3,
					'blog-1-rest-comment-collection',
					'blog-1-rest-comment-' . $this->comment_id1,
					'blog-1-rest-comment-post-' . $this->post_id1,
					'blog-1-rest-category-collection',
					'blog-1-rest-term-1',
					'blog-1-rest-post_tag-collection',
					'blog-1-rest-term-' . $this->tag_id2,
					'blog-1-rest-user-' . $this->user_id1,
					'blog-1-rest-user-' . $this->user_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/posts with no items emits the expected surrogate keys
	 */
	public function test_get_posts_no_results() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$request->set_param( 'author', REST_TESTS_IMPOSSIBLY_HIGH_NUMBER );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 0, $response->get_data() );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-post-collection',
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-post-collection',
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/post/<id> emits the expected surrogate keys
	 */
	public function test_get_post() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $this->post_id2 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->post_id2, $data['id'] );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-post-' . $this->post_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-post-' . $this->post_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/pages emits the expected surrogate keys
	 */
	public function test_get_pages() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/pages' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 1, $response->get_data() );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-page-collection',
					'rest-post-' . $this->page_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-page-collection',
					'blog-1-rest-post-' . $this->page_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/page/<id> emits the expected surrogate keys
	 */
	public function test_get_page() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/pages/' . $this->page_id1 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->page_id1, $data['id'] );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-post-' . $this->page_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-post-' . $this->page_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/media emits the expected surrogate keys
	 */
	public function test_get_media() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/media' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 1, $response->get_data() );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-attachment-collection',
					'rest-post-' . $this->attachment_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-attachment-collection',
					'blog-1-rest-post-' . $this->attachment_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/media emits the expected surrogate keys
	 */
	public function test_get_medii() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/media/' . $this->attachment_id1 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->attachment_id1, $data['id'] );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-post-' . $this->attachment_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-post-' . $this->attachment_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/categories emits the expected surrogate keys
	 */
	public function test_get_categories() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/categories' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-category-collection',
					'rest-term-' . $this->category_id1,
					'rest-term-' . $this->category_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-category-collection',
					'blog-1-rest-term-' . $this->category_id1,
					'blog-1-rest-term-' . $this->category_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/categories/<id> emits the expected surrogate keys
	 */
	public function test_get_category() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/categories/' . $this->category_id2 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->category_id2, $data['id'] );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-term-' . $this->category_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-term-' . $this->category_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/tags emits the expected surrogate keys
	 */
	public function test_get_tags() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/tags' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-post_tag-collection',
					'rest-term-' . $this->tag_id1,
					'rest-term-' . $this->tag_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-post_tag-collection',
					'blog-1-rest-term-' . $this->tag_id1,
					'blog-1-rest-term-' . $this->tag_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/tags/<id> emits the expected surrogate keys
	 */
	public function test_get_tag() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/tags/' . $this->tag_id1 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->tag_id1, $data['id'] );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-term-' . $this->tag_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-term-' . $this->tag_id1,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/comments emits the expected surrogate keys
	 */
	public function test_get_comments() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/comments' );
		$comment  = get_comment( $this->comment_id1 );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 1, $response->get_data() );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-comment-collection',
					'rest-comment-' . $this->comment_id1,
					'rest-comment-post-' . $comment->comment_post_ID,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-comment-collection',
					'blog-1-rest-comment-' . $this->comment_id1,
					'blog-1-rest-comment-post-' . $comment->comment_post_ID,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/comments without results emits the expected surrogate keys
	 */
	public function test_get_comments_no_results() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/comments' );
		$request->set_param( 'post', REST_TESTS_IMPOSSIBLY_HIGH_NUMBER );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 0, $response->get_data() );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-comment-collection',
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-comment-collection',
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/comments/<id> emits the expected surrogate keys
	 */
	public function test_get_comment() {
		$comment  = get_comment( $this->comment_id1 );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/comments/' . $this->comment_id1 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->comment_id1, $data['id'] );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-comment-' . $this->comment_id1,
					'rest-comment-post-' . $comment->comment_post_ID,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-comment-' . $this->comment_id1,
					'blog-1-rest-comment-post-' . $comment->comment_post_ID,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/users emits the expected surrogate keys
	 */
	public function test_get_users() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/users' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-user-collection',
					'rest-user-' . $this->user_id1,
					'rest-user-' . $this->user_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-user-collection',
					'blog-1-rest-user-' . $this->user_id1,
					'blog-1-rest-user-' . $this->user_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/users/<id> emits the expected surrogate keys
	 */
	public function test_get_user() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/users/' . $this->user_id2 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->user_id2, $data['id'] );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'rest-user-' . $this->user_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-rest-user-' . $this->user_id2,
				],
				Emitter::get_rest_api_surrogate_keys()
			);
		}
	}

	/**
	 * Ensure GET /wp/v2/settings emits the expected surrogate keys
	 */
	public function test_get_settings() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Test only applicable on single site.' );
		}
		wp_set_current_user( $this->admin_id1 );
		$request        = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response       = $this->server->dispatch( $request );
		$expected_count = 15;
		if ( version_compare( $GLOBALS['wp_version'], '6.0.3', '>=' ) ) {
			$expected_count = 20;
		} elseif ( version_compare( $GLOBALS['wp_version'], '5.9-alpha', '>=' ) ) {
			$expected_count = 17;
		} elseif ( version_compare( $GLOBALS['wp_version'], '5.8', '>=' ) ) {
			$expected_count = 16;
		}
		if ( ! is_multisite() ) {
			$expected_values = [
				'rest-setting-date_format',
				'rest-setting-default_category',
				'rest-setting-default_comment_status',
				'rest-setting-default_ping_status',
				'rest-setting-default_post_format',
				'rest-setting-description',
				'rest-setting-email',
				'rest-setting-language',
				'rest-setting-posts_per_page',
				'rest-setting-start_of_week',
				'rest-setting-time_format',
				'rest-setting-timezone',
				'rest-setting-title',
				'rest-setting-url',
				'rest-setting-use_smilies',
			];
		} else {
			$expected_values = [
				'blog-1-rest-setting-date_format',
				'blog-1-rest-setting-default_category',
				'blog-1-rest-setting-default_comment_status',
				'blog-1-rest-setting-default_ping_status',
				'blog-1-rest-setting-default_post_format',
				'blog-1-rest-setting-description',
				'blog-1-rest-setting-email',
				'blog-1-rest-setting-language',
				'blog-1-rest-setting-posts_per_page',
				'blog-1-rest-setting-start_of_week',
				'blog-1-rest-setting-time_format',
				'blog-1-rest-setting-timezone',
				'blog-1-rest-setting-title',
				'blog-1-rest-setting-url',
				'blog-1-rest-setting-use_smilies',
			];
		}
		if ( ! is_multisite() ) {
			if ( version_compare( $GLOBALS['wp_version'], '6.0.3', '>=' ) ) {
				array_splice(
					$expected_values,
					9,
					0,
					[ 'rest-setting-page_for_posts', 'rest-setting-page_on_front', 'rest-setting-show_on_front', 'rest-setting-site_icon', 'rest-setting-site_logo' ]
				);
			} elseif ( version_compare( $GLOBALS['wp_version'], '5.9-alpha', '>=' ) ) {
				array_splice(
					$expected_values,
					9,
					0,
					[ 'rest-setting-site_icon', 'rest-setting-site_logo' ]
				);
			} elseif ( version_compare( $GLOBALS['wp_version'], '5.8', '>=' ) ) {
				array_splice(
					$expected_values,
					9,
					0,
					[ 'rest-setting-site_logo' ]
				);
			}
		} elseif ( version_compare( $GLOBALS['wp_version'], '6.0.3', '>=' ) ) {
				array_splice(
					$expected_values,
					9,
					0,
					[ 'blog-1-rest-setting-page_for_posts', 'blog-1-rest-setting-page_on_front', 'blog-1-rest-setting-show_on_front', 'blog-1-rest-setting-site_icon', 'blog-1-rest-setting-site_logo' ]
				);
		} elseif ( version_compare( $GLOBALS['wp_version'], '5.9-alpha', '>=' ) ) {
			array_splice(
				$expected_values,
				9,
				0,
				[ 'blog-1-rest-setting-site_icon', 'blog-1-rest-setting-site_logo' ]
			);
		} elseif ( version_compare( $GLOBALS['wp_version'], '5.8', '>=' ) ) {
			array_splice(
				$expected_values,
				9,
				0,
				[ 'blog-1-rest-setting-site_logo' ]
			);
		}
		$this->assertCount( $expected_count, $response->get_data() );
		$this->assertArrayValues(
			$expected_values,
			Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Test headers set on a REST API response.
	 */
	public function test_rest_api_response_headers() {
		// Add the filter to include the test key
		add_filter('pantheon_wp_rest_api_surrogate_keys', function ( $keys ) {
			$keys[] = 'test-key';
			return $keys;
		});

		// Create a new instance of WP_REST_Response
		$response = new WP_REST_Response( [ 'foo' => 'bar' ] );
		$server = rest_get_server();

		// Fire up the filter to capture the headers from the response.
		$rest_post_dispatch = Emitter::filter_rest_post_dispatch( $response, $server );
		$actual_headers = $rest_post_dispatch->headers;

		// Define your expected surrogate keys
		$expected_surrogate_keys = Emitter::get_rest_api_surrogate_keys();

		// Verify that the headers were set correctly
		$this->assertContains( 'test-key', $expected_surrogate_keys );
		$this->assertArrayHasKey( 'Surrogate-Key', $actual_headers );
		$this->assertEquals( $actual_headers['Surrogate-Key'], implode( ' ', $expected_surrogate_keys ) );
	}

	/**
	 * Test headers set on a GraphQL response.
	 */
	public function test_graphql_response_headers() {
		// Set up the mock response array with the existing headers set by the GraphQL plugin
		$existing_headers = [
			'header1' => 'value1',
			'header2' => 'value2',
		];

		// Simulate adding a surrogate key via a filter
		add_filter('pantheon_wp_graphql_surrogate_keys', function ( $keys ) {
			$keys[] = 'test-key';
			return $keys;
		});

		// Call the filter_graphql_response_headers_to_send() method
		$result_headers = Emitter::filter_graphql_response_headers_to_send( $existing_headers );

		$graphql_collection_key = is_multisite() ? 'blog-1-graphql-collection' : 'graphql-collection';
		$expected_headers = array_merge( $existing_headers, [ 'Surrogate-Key' => "$graphql_collection_key test-key" ] );
		// Verify that the existing headers are preserved
		$this->assertEquals( $expected_headers, $result_headers );

		// Verify that the 'Surrogate-Key' header was added with the expected value
		$expected_surrogate_keys = Emitter::get_graphql_surrogate_keys();
		$this->assertArrayHasKey( Emitter::HEADER_KEY, $result_headers );
		$this->assertEquals( implode( ' ', $expected_surrogate_keys ), $result_headers[ Emitter::HEADER_KEY ] );
		$this->assertContains( 'test-key', $expected_surrogate_keys );
	}
}
