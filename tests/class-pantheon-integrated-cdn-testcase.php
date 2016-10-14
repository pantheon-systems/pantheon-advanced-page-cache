<?php

class Pantheon_Integrated_CDN_Testcase extends WP_UnitTestCase {

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

}
