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
		$request = new WP_REST_Request( 'GET', '/wp/v2/posts' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 3, $response->get_data() );
		$this->assertArrayValues( array(
			'post-' . $this->post_id1,
			'post-' . $this->post_id2,
			'post-' . $this->post_id3,
		), Emitter::get_rest_api_surrogate_keys() );
	}

	/**
	 * Ensure GET /wp/v2/post/<id> emits the expected surrogate keys
	 */
	public function test_get_post() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/posts/' . $this->post_id2 );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( $this->post_id2, $data['id'] );
		$this->assertArrayValues( array(
			'post-' . $this->post_id2,
		), Emitter::get_rest_api_surrogate_keys() );
	}

	/**
	 * Ensure GET /wp/v2/pages emits the expected surrogate keys
	 */
	public function test_get_pages() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/pages' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 1, $response->get_data() );
		$this->assertArrayValues( array(
			'post-' . $this->page_id1,
		), Emitter::get_rest_api_surrogate_keys() );
	}

	/**
	 * Ensure GET /wp/v2/page/<id> emits the expected surrogate keys
	 */
	public function test_get_page() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/pages/' . $this->page_id1 );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( $this->page_id1, $data['id'] );
		$this->assertArrayValues( array(
			'post-' . $this->page_id1,
		), Emitter::get_rest_api_surrogate_keys() );
	}

	/**
	 * Ensure GET /wp/v2/categories emits the expected surrogate keys
	 */
	public function test_get_categories() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/categories' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		$this->assertArrayValues( array(
			'term-' . $this->category_id1,
			'term-' . $this->category_id2,
		), Emitter::get_rest_api_surrogate_keys() );
	}

	/**
	 * Ensure GET /wp/v2/categories/<id> emits the expected surrogate keys
	 */
	public function test_get_category() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/categories/' . $this->category_id2 );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( $this->category_id2, $data['id'] );
		$this->assertArrayValues( array(
			'term-' . $this->category_id2,
		), Emitter::get_rest_api_surrogate_keys() );
	}

	/**
	 * Ensure GET /wp/v2/tags emits the expected surrogate keys
	 */
	public function test_get_tags() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/tags' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		$this->assertArrayValues( array(
			'term-' . $this->tag_id1,
			'term-' . $this->tag_id2,
		), Emitter::get_rest_api_surrogate_keys() );
	}

	/**
	 * Ensure GET /wp/v2/tags/<id> emits the expected surrogate keys
	 */
	public function test_get_tag() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/tags/' . $this->tag_id1 );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( $this->tag_id1, $data['id'] );
		$this->assertArrayValues( array(
			'term-' . $this->tag_id1,
		), Emitter::get_rest_api_surrogate_keys() );
	}

	/**
	 * Ensure GET /wp/v2/users emits the expected surrogate keys
	 */
	public function test_get_users() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/users' );
		$response = $this->server->dispatch( $request );
		$this->assertCount( 2, $response->get_data() );
		$this->assertArrayValues( array(
			'user-' . $this->user_id1,
			'user-' . $this->user_id2,
		), Emitter::get_rest_api_surrogate_keys() );
	}

	/**
	 * Ensure GET /wp/v2/users/<id> emits the expected surrogate keys
	 */
	public function test_get_user() {
		$request = new WP_REST_Request( 'GET', '/wp/v2/users/' . $this->user_id2 );
		$response = $this->server->dispatch( $request );
		$data = $response->get_data();
		$this->assertEquals( $this->user_id2, $data['id'] );
		$this->assertArrayValues( array(
			'user-' . $this->user_id2,
		), Emitter::get_rest_api_surrogate_keys() );
	}

}
