<?php

class Pantheon_Integrated_CDN_Testcase extends WP_UnitTestCase {

	protected $cleared_keys = array();

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
			'post_author' => $this->user_id2,
		) );
		$this->page_id1 = $this->factory->post->create( array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_author' => $this->user_id1,
		) );
		$this->cleared_keys = array();
		add_action( 'pantheon_integrated_cdn_clear_keys', array( $this, 'action_pantheon_integrated_cdn_clear_keys' ) );
	}

	public function action_pantheon_integrated_cdn_clear_keys( $keys ) {
		$this->cleared_keys = $keys;
	}

	protected function assertClearedKeys( $expected ) {
		$this->assertArrayValues( $expected, $this->cleared_keys );
	}

	protected function assertArrayValues( $expected, $actual ) {
		sort( $expected );
		sort( $actual );
		$this->assertEquals( $expected, $actual );
	}

	public function tearDown() {
		$this->cleared_keys = array();
		remove_action( 'pantheon_integrated_cdn_clear_keys', array( $this, 'action_pantheon_integrated_cdn_clear_keys' ) );
		parent::tearDown();
	}

}
