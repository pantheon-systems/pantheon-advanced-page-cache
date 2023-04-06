<?php
/**
 * Tests for the Emitter class.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

use Pantheon_Advanced_Page_Cache\Emitter;

/**
 * Tests for the Emitter class.
 */
class Test_Emitter extends Pantheon_Advanced_Page_Cache_Testcase {

	/**
	 * Assert expected surrogate keys for the homepage.
	 */
	public function test_homepage_default() {
		$this->go_to( home_url( '/' ) );
		$this->assertArrayValues(
			array(
				'front',
				'home',
				'post-' . $this->post_id1,
				'post-' . $this->post_id2,
				'post-' . $this->post_id3,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for the product archive
	 */
	public function test_single_product() {
		$this->go_to( get_post_type_archive_link( 'product' ) );
		$this->assertArrayValues(
			array(
				'archive',
				'post-type-archive',
				'product-archive',
				'post-' . $this->$this->product_id1,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a single post.
	 */
	public function test_single_post() {
		$this->go_to( get_permalink( $this->post_id2 ) );
		$this->assertArrayValues(
			array(
				'single',
				'post-' . $this->post_id2,
				'post-user-' . $this->user_id2,
				'post-term-' . $this->category_id1,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a single page.
	 */
	public function test_single_page() {
		$this->go_to( get_permalink( $this->page_id1 ) );
		$this->assertArrayValues(
			array(
				'single',
				'post-' . $this->page_id1,
				'post-user-' . $this->user_id1,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a single product.
	 */
	public function test_surrogate_keys() {
		$this->go_to( get_permalink( $this->product_id1 ) );
		$this->assertArrayValues(
			array(
				'single',
				'post-' . $this->product_id1,
				'post-term-' . $this->product_category_id2,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for an author archive who has posts.
	 */
	public function test_single_author_with_posts() {
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		$this->assertArrayValues(
			array(
				'archive',
				'user-' . $this->user_id1,
				'post-' . $this->post_id1,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for an author archive who doesn't have posts.
	 */
	public function test_single_author_without_posts() {
		$this->go_to( get_author_posts_url( $this->user_id3 ) );
		$this->assertArrayValues(
			array(
				'archive',
				'user-' . $this->user_id3,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a tag archive which has posts.
	 */
	public function test_single_tag_with_posts() {
		$this->go_to( get_term_link( $this->tag_id2 ) );
		$this->assertArrayValues(
			array(
				'archive',
				'term-' . $this->tag_id2,
				'post-' . $this->post_id1,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a tag archive which doesn't have posts.
	 */
	public function test_single_tag_without_posts() {
		$this->go_to( get_term_link( $this->tag_id1 ) );
		$this->assertArrayValues(
			array(
				'archive',
				'term-' . $this->tag_id1,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a product category archive which has posts.
	 */
	public function test_single_product_category_with_posts() {
		$this->go_to( get_term_link( $this->product_category_id1 ) );
		$this->assertArrayValues(
			array(
				'archive',
				'term-' . $this->product_category_id1,
				'post-' . $this->product_id2,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a product category archive which doesn't have posts.
	 */
	public function test_single_product_category_without_posts() {
		$this->go_to( get_term_link( $this->product_category_id3 ) );
		$this->assertArrayValues(
			array(
				'archive',
				'term-' . $this->product_category_id3,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for custom post type archive.
	 */
	public function test_product_archive_with_posts() {
		$this->go_to( home_url( 'products/' ) );
		$this->assertArrayValues(
			array(
				'archive',
				'post-type-archive',
				'product-archive',
				'post-' . $this->product_id1,
				'post-' . $this->product_id2,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a year date archive with posts.
	 */
	public function test_year_date_archive_with_posts() {
		$this->go_to( home_url( '2016/' ) );
		$this->assertArrayValues(
			array(
				'archive',
				'date',
				'post-' . $this->post_id1,
				'post-' . $this->post_id2,
				'post-' . $this->post_id3,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a year date archive without posts.
	 */
	public function test_year_date_archive_without_posts() {
		$this->go_to( home_url( '2015/' ) );
		$this->assertArrayValues(
			array(
				'404',
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a month date archive with posts.
	 */
	public function test_month_date_archive_with_posts() {
		$this->go_to( home_url( '2016/10/' ) );
		$this->assertArrayValues(
			array(
				'archive',
				'date',
				'post-' . $this->post_id1,
				'post-' . $this->post_id2,
				'post-' . $this->post_id3,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a month date archive with posts.
	 */
	public function test_month_date_archive_without_posts() {
		$this->go_to( home_url( '2015/10/' ) );
		$this->assertArrayValues(
			array(
				'404',
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a day date archive with posts.
	 */
	public function test_day_date_archive_with_posts() {
		$this->go_to( home_url( '2016/10/15/' ) );
		$this->assertArrayValues(
			array(
				'archive',
				'date',
				'post-' . $this->post_id3,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a day date archive without posts.
	 */
	public function test_day_date_archive_without_posts() {
		$this->go_to( home_url( '2015/10/15/' ) );
		$this->assertArrayValues(
			array(
				'404',
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a search with posts
	 */
	public function test_search_with_posts() {
		$this->go_to( home_url( '/?s=post' ) );
		$this->assertArrayValues(
			array(
				'search',
				'search-results',
				'post-' . $this->post_id1,
				'post-' . $this->post_id2,
				'post-' . $this->post_id3,
				'post-' . $this->page_id1,
				'post-' . $this->product_id1,
				'post-' . $this->product_id2,
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for a search without posts
	 */
	public function test_search_without_posts() {
		$this->go_to( home_url( '/?s=foo' ) );
		$this->assertArrayValues(
			array(
				'search',
				'search-no-results',
			),
			Emitter::get_main_query_surrogate_keys()
		);
	}

	/**
	 * Assert expected surrogate keys for filtering a small list of keys
	 */
	public function test_filter_huge_surrogate_keys_list_smalllist() {
		$keys = array(
			'post',
			'post-term-1',
			'post-term-5',
		);
		for ( $i = 1000; $i < 25; $i++ ) {
			$keys[] = 'post-' . $i;
		}
		$this->assertArrayValues( $keys, Pantheon_Advanced_Page_Cache\Emitter::filter_huge_surrogate_keys_list( $keys ) );
	}

	/**
	 * Assert expected surrogate keys for filtering a huge list of keys
	 */
	public function test_filter_huge_surrogate_keys_list_largelist() {
		$keys = array(
			'post',
			'post-term-1',
			'post-term-5',
		);
		for ( $i = 1; $i < ( Pantheon_Advanced_Page_Cache\Emitter::HEADER_MAX_LENGTH / 6 ); $i++ ) {
			$keys[] = 'post-' . $i;
		}
		$this->assertArrayValues( array( 'post-huge', 'post-term-1', 'post-term-5', 'post' ), Pantheon_Advanced_Page_Cache\Emitter::filter_huge_surrogate_keys_list( $keys ) );
	}

}
