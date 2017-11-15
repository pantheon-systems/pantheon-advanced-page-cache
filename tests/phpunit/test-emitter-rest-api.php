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
	public function setUp() {
		parent::setUp();
		if ( version_compare( $GLOBALS['wp_version'], '4.7-alpha', '<' ) ) {
			return $this->markTestSkipped( 'WordPress version not supported.' );
		}
	}

	/**
	 * Ensure GET /wp/v2/posts emits the expected surrogate keys
	 */
	public function test_get_posts() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 3, $response->get_data() );
		$this->assertArrayValues(
			array(
				'rest-post-collection',
				'rest-post-' . $this->post_id1,
				'rest-post-' . $this->post_id2,
				'rest-post-' . $this->post_id3,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/posts?_embed emits the expected surrogate keys
	 */
	public function test_get_posts_embed() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = $this->server->dispatch( $request );
		$data     = $this->server->response_to_data( $response, true );
		$this->assertCount( 3, $data );
		$this->assertArrayValues(
			array(
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
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/posts with no items emits the expected surrogate keys
	 */
	public function test_get_posts_no_results() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$request->set_param( 'author', REST_TESTS_IMPOSSIBLY_HIGH_NUMBER );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 0, $response->get_data() );
		$this->assertArrayValues(
			array(
				'rest-post-collection',
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/post/<id> emits the expected surrogate keys
	 */
	public function test_get_post() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $this->post_id2 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->post_id2, $data['id'] );
		$this->assertArrayValues(
			array(
				'rest-post-' . $this->post_id2,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/pages emits the expected surrogate keys
	 */
	public function test_get_pages() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/pages' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 1, $response->get_data() );
		$this->assertArrayValues(
			array(
				'rest-page-collection',
				'rest-post-' . $this->page_id1,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/page/<id> emits the expected surrogate keys
	 */
	public function test_get_page() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/pages/' . $this->page_id1 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->page_id1, $data['id'] );
		$this->assertArrayValues(
			array(
				'rest-post-' . $this->page_id1,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/media emits the expected surrogate keys
	 */
	public function test_get_media() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/media' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 1, $response->get_data() );
		$this->assertArrayValues(
			array(
				'rest-attachment-collection',
				'rest-post-' . $this->attachment_id1,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/media emits the expected surrogate keys
	 */
	public function test_get_medii() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/media/' . $this->attachment_id1 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->attachment_id1, $data['id'] );
		$this->assertArrayValues(
			array(
				'rest-post-' . $this->attachment_id1,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/categories emits the expected surrogate keys
	 */
	public function test_get_categories() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/categories' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		$this->assertArrayValues(
			array(
				'rest-category-collection',
				'rest-term-' . $this->category_id1,
				'rest-term-' . $this->category_id2,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/categories/<id> emits the expected surrogate keys
	 */
	public function test_get_category() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/categories/' . $this->category_id2 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->category_id2, $data['id'] );
		$this->assertArrayValues(
			array(
				'rest-term-' . $this->category_id2,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/tags emits the expected surrogate keys
	 */
	public function test_get_tags() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/tags' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		$this->assertArrayValues(
			array(
				'rest-post_tag-collection',
				'rest-term-' . $this->tag_id1,
				'rest-term-' . $this->tag_id2,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/tags/<id> emits the expected surrogate keys
	 */
	public function test_get_tag() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/tags/' . $this->tag_id1 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->tag_id1, $data['id'] );
		$this->assertArrayValues(
			array(
				'rest-term-' . $this->tag_id1,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/comments emits the expected surrogate keys
	 */
	public function test_get_comments() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/comments' );
		$comment  = get_comment( $this->comment_id1 );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 1, $response->get_data() );
		$this->assertArrayValues(
			array(
				'rest-comment-collection',
				'rest-comment-' . $this->comment_id1,
				'rest-comment-post-' . $comment->comment_post_ID,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/comments without results emits the expected surrogate keys
	 */
	public function test_get_comments_no_results() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/comments' );
		$request->set_param( 'post', REST_TESTS_IMPOSSIBLY_HIGH_NUMBER );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 0, $response->get_data() );
		$this->assertArrayValues(
			array(
				'rest-comment-collection',
			), Emitter::get_rest_api_surrogate_keys()
		);
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
		$this->assertArrayValues(
			array(
				'rest-comment-' . $this->comment_id1,
				'rest-comment-post-' . $comment->comment_post_ID,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/users emits the expected surrogate keys
	 */
	public function test_get_users() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/users' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		$this->assertArrayValues(
			array(
				'rest-user-collection',
				'rest-user-' . $this->user_id1,
				'rest-user-' . $this->user_id2,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/users/<id> emits the expected surrogate keys
	 */
	public function test_get_user() {
		$request  = new WP_REST_Request( 'GET', '/wp/v2/users/' . $this->user_id2 );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$this->assertEquals( $this->user_id2, $data['id'] );
		$this->assertArrayValues(
			array(
				'rest-user-' . $this->user_id2,
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

	/**
	 * Ensure GET /wp/v2/settings emits the expected surrogate keys
	 */
	public function test_get_settings() {
		if ( is_multisite() ) {
			$this->markTestSkipped( 'Test only applicable on single site.' );
		}
		wp_set_current_user( $this->admin_id1 );
		$request  = new WP_REST_Request( 'GET', '/wp/v2/settings' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 15, $response->get_data() );
		$this->assertArrayValues(
			array(
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
			), Emitter::get_rest_api_surrogate_keys()
		);
	}

}
