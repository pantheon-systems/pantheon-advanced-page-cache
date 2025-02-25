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
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'front',
					'home',
					'post-' . $this->post_id1,
					'post-' . $this->post_id2,
					'post-' . $this->post_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-front',
					'blog-1-home',
					'blog-1-post-' . $this->post_id1,
					'blog-1-post-' . $this->post_id2,
					'blog-1-post-' . $this->post_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for the product archive
	 */
	public function test_product_archive() {
		$this->go_to( get_post_type_archive_link( 'product' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'post-type-archive',
					'product-archive',
					'post-' . $this->product_id1,
					'post-' . $this->product_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-post-type-archive',
					'blog-1-product-archive',
					'blog-1-post-' . $this->product_id1,
					'blog-1-post-' . $this->product_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a single post.
	 */
	public function test_single_post() {
		$this->go_to( get_permalink( $this->post_id2 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'single',
					'post-' . $this->post_id2,
					'post-user-' . $this->user_id2,
					'post-term-' . $this->category_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-single',
					'blog-1-post-' . $this->post_id2,
					'blog-1-post-user-' . $this->user_id2,
					'blog-1-post-term-' . $this->category_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a single page.
	 */
	public function test_single_page() {
		$this->go_to( get_permalink( $this->page_id1 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'single',
					'post-' . $this->page_id1,
					'post-user-' . $this->user_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-single',
					'blog-1-post-' . $this->page_id1,
					'blog-1-post-user-' . $this->user_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a single product.
	 */
	public function test_surrogate_keys() {
		$this->go_to( get_permalink( $this->product_id1 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'single',
					'post-' . $this->product_id1,
					'post-term-' . $this->product_category_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-single',
					'blog-1-post-' . $this->product_id1,
					'blog-1-post-term-' . $this->product_category_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for an author archive who has posts.
	 */
	public function test_single_author_with_posts() {
		$this->go_to( get_author_posts_url( $this->user_id1 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'user-' . $this->user_id1,
					'post-' . $this->post_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-user-' . $this->user_id1,
					'blog-1-post-' . $this->post_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for an author archive who doesn't have posts.
	 */
	public function test_single_author_without_posts() {
		$this->go_to( get_author_posts_url( $this->user_id3 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'user-' . $this->user_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-user-' . $this->user_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a tag archive which has posts.
	 */
	public function test_single_tag_with_posts() {
		$this->go_to( get_term_link( $this->tag_id2 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'term-' . $this->tag_id2,
					'post-' . $this->post_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-term-' . $this->tag_id2,
					'blog-1-post-' . $this->post_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a tag archive which doesn't have posts.
	 */
	public function test_single_tag_without_posts() {
		$this->go_to( get_term_link( $this->tag_id1 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'term-' . $this->tag_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-term-' . $this->tag_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a product category archive which has posts.
	 */
	public function test_single_product_category_with_posts() {
		$this->go_to( get_term_link( $this->product_category_id1 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'term-' . $this->product_category_id1,
					'post-' . $this->product_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-term-' . $this->product_category_id1,
					'blog-1-post-' . $this->product_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a product category archive which doesn't have posts.
	 */
	public function test_single_product_category_without_posts() {
		$this->go_to( get_term_link( $this->product_category_id3 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'term-' . $this->product_category_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-term-' . $this->product_category_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for custom post type archive.
	 */
	public function test_product_archive_with_posts() {
		$this->go_to( home_url( 'products/' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'post-type-archive',
					'product-archive',
					'post-' . $this->product_id1,
					'post-' . $this->product_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-post-type-archive',
					'blog-1-product-archive',
					'blog-1-post-' . $this->product_id1,
					'blog-1-post-' . $this->product_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a year date archive with posts.
	 */
	public function test_year_date_archive_with_posts() {
		$this->go_to( home_url( '2016/' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'date',
					'post-' . $this->post_id1,
					'post-' . $this->post_id2,
					'post-' . $this->post_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-date',
					'blog-1-post-' . $this->post_id1,
					'blog-1-post-' . $this->post_id2,
					'blog-1-post-' . $this->post_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a year date archive without posts.
	 */
	public function test_year_date_archive_without_posts() {
		$this->go_to( home_url( '2015/' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'404',
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-404',
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a month date archive with posts.
	 */
	public function test_month_date_archive_with_posts() {
		$this->go_to( home_url( '2016/10/' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'date',
					'post-' . $this->post_id1,
					'post-' . $this->post_id2,
					'post-' . $this->post_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-date',
					'blog-1-post-' . $this->post_id1,
					'blog-1-post-' . $this->post_id2,
					'blog-1-post-' . $this->post_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a month date archive with posts.
	 */
	public function test_month_date_archive_without_posts() {
		$this->go_to( home_url( '2015/10/' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'404',
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-404',
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a day date archive with posts.
	 */
	public function test_day_date_archive_with_posts() {
		$this->go_to( home_url( '2016/10/15/' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'archive',
					'date',
					'post-' . $this->post_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-archive',
					'blog-1-date',
					'blog-1-post-' . $this->post_id3,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a day date archive without posts.
	 */
	public function test_day_date_archive_without_posts() {
		$this->go_to( home_url( '2015/10/15/' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'404',
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-404',
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a search with posts
	 */
	public function test_search_with_posts() {
		$this->go_to( home_url( '/?s=post' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'search',
					'search-results',
					'post-' . $this->post_id1,
					'post-' . $this->post_id2,
					'post-' . $this->post_id3,
					'post-' . $this->page_id1,
					'post-' . $this->product_id1,
					'post-' . $this->product_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-search',
					'blog-1-search-results',
					'blog-1-post-' . $this->post_id1,
					'blog-1-post-' . $this->post_id2,
					'blog-1-post-' . $this->post_id3,
					'blog-1-post-' . $this->page_id1,
					'blog-1-post-' . $this->product_id1,
					'blog-1-post-' . $this->product_id2,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for a search without posts
	 */
	public function test_search_without_posts() {
		$this->go_to( home_url( '/?s=foo' ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'search',
					'search-no-results',
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-search',
					'blog-1-search-no-results',
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
	}

	/**
	 * Assert expected surrogate keys for filtering a small list of keys
	 */
	public function test_filter_huge_surrogate_keys_list_smalllist() {
		if ( ! is_multisite() ) {
			$keys = [
				'post',
				'post-term-1',
				'post-term-5',
			];
			for ( $i = 1000; $i < 25; $i++ ) {
				$keys[] = 'post-' . $i;
			}
		} else {
			$keys = [
				'blog-1-post',
				'blog-1-post-term-1',
				'blog-1-post-term-5',
			];
			for ( $i = 1000; $i < 25; $i++ ) {
				$keys[] = 'blog-1-post-' . $i;
			}
		}
		$this->assertArrayValues( $keys, Pantheon_Advanced_Page_Cache\Emitter::filter_huge_surrogate_keys_list( $keys ) );
	}

	/**
	 * Assert expected surrogate keys for filtering a huge list of keys
	 */
	public function test_filter_huge_surrogate_keys_list_largelist() {
		if ( ! is_multisite() ) {
			$keys = [
				'post',
				'post-term-1',
				'post-term-5',
			];
			for ( $i = 1; $i < ( Pantheon_Advanced_Page_Cache\Emitter::HEADER_MAX_LENGTH / 6 ); $i++ ) {
				$keys[] = 'post-' . $i;
			}
			$this->assertArrayValues( [ 'post-huge', 'post-term-1', 'post-term-5', 'post' ], Pantheon_Advanced_Page_Cache\Emitter::filter_huge_surrogate_keys_list( $keys ) );
		} else {
			$keys = [
				'blog-1-post',
				'blog-1-post-term-1',
				'blog-1-post-term-5',
			];
			for ( $i = 1; $i < ( Pantheon_Advanced_Page_Cache\Emitter::HEADER_MAX_LENGTH / 6 ); $i++ ) {
				$keys[] = 'blog-1-post-' . $i;
			}
			$this->assertArrayValues( [ 'blog-1-post-huge', 'blog-1-post-term-1', 'blog-1-post-term-5', 'blog-1-post' ], Pantheon_Advanced_Page_Cache\Emitter::filter_huge_surrogate_keys_list( $keys ) );
		}
	}

	/**
	 * Assert no surrogate keys for a single product when filter is overriden to skip them.
	 */
	public function test_surrogate_keys_with_filter_override() {
		add_filter( 'pantheon_should_add_terms', '__return_false', 10, 2 );
		$this->go_to( get_permalink( $this->product_id1 ) );
		if ( ! is_multisite() ) {
			$this->assertArrayValues(
				[
					'single',
					'post-' . $this->product_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		} else {
			$this->assertArrayValues(
				[
					'blog-1-single',
					'blog-1-post-' . $this->product_id1,
				],
				Emitter::get_main_query_surrogate_keys()
			);
		}
		remove_all_filters( 'pantheon_should_add_terms' );
	}

	/**
	 * Test that queries on archive pages with multiple post types don't throw an error.
	 *
	 * @see https://getpantheon.atlassian.net/browse/BUGS-6934
	 */
	public function test_surrogate_keys_multiple_post_types() {
		global $wp_query;

		// Set up posts of different post types.
		$product_id = $this->factory->post->create( [
			'post_type' => 'product',
			'post_title' => 'test product',
		] );
		$post_id = $this->factory->post->create( [
			'post_type' => 'post',
			'post_title' => 'test post',
		] );

		// Query the posts we just created.
		$wp_query = new WP_Query( [
			's' => 'test',
			'post_type' => [ 'post', 'product' ],
		] );
		$wp_query->is_archive = true;
		$wp_query->is_post_type_archive = true;

		// Ensure that we don't get a WP Error from the surrogate key generator. This is a bit superfluous since if there was an error, we'd see it in the tests.
		$this->assertTrue( ! is_wp_error( Emitter::get_main_query_surrogate_keys() ) );

		// Test that we have surrogate keys for the post and product archives.
		$wpms_prefix = '';
		if ( is_multisite() ) {
			$wpms_prefix = 'blog-1-';
		}
		$post_archive_key = $wpms_prefix . 'post-archive';
		$product_archive_key = $wpms_prefix . 'product-archive';
		$this->assertContains(
			$post_archive_key,
			Emitter::get_main_query_surrogate_keys()
		);
		$this->assertContains(
			$product_archive_key,
			Emitter::get_main_query_surrogate_keys()
		);

		// Make sure our newly-created posts are in the list of surrogate keys.
		$this->assertContains(
			$wpms_prefix . "post-$post_id",
			Emitter::get_main_query_surrogate_keys()
		);
		$this->assertContains(
			$wpms_prefix . "post-$product_id",
			Emitter::get_main_query_surrogate_keys()
		);
		wp_reset_postdata( $wp_query );
	}
}
