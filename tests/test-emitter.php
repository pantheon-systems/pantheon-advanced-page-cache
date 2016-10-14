<?php

use Pantheon_Integrated_CDN\Emitter;

class Test_Emitter extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();

		$this->post_id1 = $this->factory->post->create( array( 'post_status' => 'publish' ) );
		$this->post_id2 = $this->factory->post->create( array( 'post_status' => 'publish' ) );
		$this->page_id1 = $this->factory->post->create( array( 'post_status' => 'publish', 'post_type' => 'page' ) );
	}

	public function test_homepage_default() {
		$this->go_to( home_url( '/' ) );
		$this->assertArrayValues( array(
			'home',
			'blog',
			'post-' . $this->post_id1,
			'post-' . $this->post_id2,
		), Emitter::get_surrogate_keys() );
	}

	public function test_single_post() {
		$this->go_to( get_permalink( $this->post_id2 ) );
		$this->assertArrayValues( array(
			'single',
			'post-' . $this->post_id2,
		), Emitter::get_surrogate_keys() );
	}

	public function test_single_page() {
		$this->go_to( get_permalink( $this->page_id1 ) );
		$this->assertArrayValues( array(
			'page',
			'post-' . $this->page_id1,
		), Emitter::get_surrogate_keys() );
	}

	protected function assertArrayValues( $expected, $actual ) {
		sort( $expected );
		sort( $actual );
		$this->assertEquals( $expected, $actual );
	}

}
