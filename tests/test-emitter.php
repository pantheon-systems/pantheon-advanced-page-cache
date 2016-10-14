<?php

use Pantheon_Integrated_CDN\Emitter;

class Test_Emitter extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$this->user_id1 = $this->factory->user->create( array( 'user_role' => 'author' ) );
		$this->user_id2 = $this->factory->user->create( array( 'user_role' => 'author' ) );
		$this->user_id3 = $this->factory->user->create( array( 'user_role' => 'author' ) );

		$this->tag_id1 = $this->factory->tag->create();
		$this->tag_id2 = $this->factory->tag->create();
		$this->category_id2 = $this->factory->category->create();

		$this->post_id1 = $this->factory->post->create( array(
			'post_status' => 'publish',
			'post_author' => $this->user_id1,
		) );
		wp_set_object_terms( $this->post_id1, array( $this->tag_id2 ), 'post_tag' );
		$this->post_id2 = $this->factory->post->create( array(
			'post_status' => 'publish',
			'post_author' => $this->user_id2
		) );
		$this->page_id1 = $this->factory->post->create( array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_author' => $this->user_id1,
		) );
	}

	public function test_homepage_default() {
		$this->go_to( home_url( '/' ) );
		$this->assertArrayValues( array(
			'front',
			'home',
			'post-' . $this->post_id1,
			'post-' . $this->post_id2,
			'user-' . $this->user_id1,
			'user-' . $this->user_id2,
		), Emitter::get_surrogate_keys() );
	}

	public function test_single_post() {
		$this->go_to( get_permalink( $this->post_id2 ) );
		$this->assertArrayValues( array(
			'single',
			'post-' . $this->post_id2,
			'user-' . $this->user_id2,
		), Emitter::get_surrogate_keys() );
	}

	public function test_single_page() {
		$this->go_to( get_permalink( $this->page_id1 ) );
		$this->assertArrayValues( array(
			'page',
			'post-' . $this->page_id1,
			'user-' . $this->user_id1,
		), Emitter::get_surrogate_keys() );
	}

	public function test_single_author_with_posts() {
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		$this->assertArrayValues( array(
			'archive',
			'post-' . $this->post_id1,
			'user-' . $this->user_id1,
		), Emitter::get_surrogate_keys() );
	}

	public function test_single_author_without_posts() {
		$this->go_to( get_author_posts_url( $this->user_id3 ) );
		$this->assertArrayValues( array(
			'archive',
			'user-' . $this->user_id3,
		), Emitter::get_surrogate_keys() );
	}

	public function test_single_tag_with_posts() {
		$this->go_to( get_term_link( $this->tag_id2 ) );
		$this->assertArrayValues( array(
			'archive',
			'term-' . $this->tag_id2,
			'post-' . $this->post_id1,
			'user-' . $this->user_id1,
		), Emitter::get_surrogate_keys() );
	}

	public function test_single_tag_without_posts() {
		$this->go_to( get_term_link( $this->tag_id1 ) );
		$this->assertArrayValues( array(
			'archive',
			'term-' . $this->tag_id1,
		), Emitter::get_surrogate_keys() );
	}

	public function test_search() {
		$this->go_to( home_url( '/?s=foo' ) );
		$this->assertArrayValues( array(
			'search'
		), Emitter::get_surrogate_keys() );
	}

	protected function assertArrayValues( $expected, $actual ) {
		sort( $expected );
		sort( $actual );
		$this->assertEquals( $expected, $actual );
	}

}
