<?php

use Pantheon_Integrated_CDN\Emitter;

class Test_Emitter extends Pantheon_Integrated_CDN_Testcase {

	public function test_homepage_default() {
		$this->go_to( home_url( '/' ) );
		$this->assertArrayValues( array(
			'front',
			'home',
			'post-' . $this->post_id1,
			'post-' . $this->post_id2,
			'post-' . $this->post_id3,
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

	public function test_year_date_archive_with_posts() {
		$this->go_to( home_url( '?year=2016' ) );
		$this->assertArrayValues( array(
			'archive',
			'date',
			'post-' . $this->post_id1,
			'post-' . $this->post_id2,
			'post-' . $this->post_id3,
			'user-' . $this->user_id1,
			'user-' . $this->user_id2,
		), Emitter::get_surrogate_keys() );
	}

	public function test_year_date_archive_without_posts() {
		$this->go_to( home_url( '?year=2015' ) );
		$this->assertArrayValues( array(
		), Emitter::get_surrogate_keys() );
	}

	public function test_month_date_archive_with_posts() {
		$this->go_to( home_url( '?year=2016&monthnum=10' ) );
		$this->assertArrayValues( array(
			'archive',
			'date',
			'post-' . $this->post_id1,
			'post-' . $this->post_id2,
			'post-' . $this->post_id3,
			'user-' . $this->user_id1,
			'user-' . $this->user_id2,
		), Emitter::get_surrogate_keys() );
	}

	public function test_month_date_archive_without_posts() {
		$this->go_to( home_url( '?year=2015&monthnum=10' ) );
		$this->assertArrayValues( array(
		), Emitter::get_surrogate_keys() );
	}

	public function test_day_date_archive_with_posts() {
		$this->go_to( home_url( '?year=2016&monthnum=10&day=15' ) );
		$this->assertArrayValues( array(
			'archive',
			'date',
			'post-' . $this->post_id3,
			'user-' . $this->user_id2,
		), Emitter::get_surrogate_keys() );
	}

	public function test_day_date_archive_without_posts() {
		$this->go_to( home_url( '?year=2015&monthnum=10&day=15' ) );
		$this->assertArrayValues( array(
		), Emitter::get_surrogate_keys() );
	}

	public function test_search() {
		$this->go_to( home_url( '/?s=foo' ) );
		$this->assertArrayValues( array(
			'search'
		), Emitter::get_surrogate_keys() );
	}

}
