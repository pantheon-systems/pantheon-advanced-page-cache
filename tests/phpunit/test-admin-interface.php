<?php
/**
 * Tests for the admin interface namespace.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

namespace Pantheon_Advanced_Page_Cache\Admin_Interface;

/**
 * Tests for the admin interface namespace..
 */
class Admin_Interface_Functions extends \Pantheon_Advanced_Page_Cache_Testcase {
	/**
	 * Set up tests.
	 */
	public function setUp(): void {
		parent::setUp();
		update_option( 'pantheon-cache', [ 'default_ttl' => 300 ] );
	}

	/**
	 * Tear down tests.
	 */
	public function tearDown(): void {
		parent::tearDown();
		delete_option( 'pantheon-cache' );
	}

	/**
	 * Test the get_current_max_age and get_default_max_age functions.
	 */
	public function test_get_max_age() {
		$this->assertEquals( 300, get_current_max_age() );
		$this->assertNotEquals( get_default_max_age(), get_current_max_age() );
		$this->assertEquals( WEEK_IN_SECONDS, get_default_max_age() );
	}

	/**
	 * Test the Site Health tests.
	 */
	public function test_site_health_tests() {
		$tests = apply_filters( 'site_status_tests', [] );

		$this->assertContains( 'pantheon_edge_cache', array_keys( $tests['direct'] ) );

		// Base test with 300 second max-age.
		$test_results = test_cache_max_age();
		$this->assertEquals( 'recommended', $test_results['status'] );
		$this->assertEquals( 'red',$test_results['badge']['color'] );
		$this->assertStringContainsString( '300 seconds', $test_results['description'] );
		$this->assertStringContainsString( 'We recommend increasing to 1 week', $test_results['description'] );

		// Update the option and rerun.
		update_option( 'pantheon-cache', [ 'default_ttl' => 5 * DAY_IN_SECONDS ] );
		$test_results = test_cache_max_age();
		$this->assertEquals( 'recommended', $test_results['status'] );
		$this->assertEquals( 'orange',$test_results['badge']['color'] );
		$this->assertStringContainsString( '5 days', $test_results['description'] );
		$this->assertStringContainsString( 'We recommend increasing to 1 week', $test_results['description'] );

		// Update the option to the default and rerun.
		update_option( 'pantheon-cache', [ 'default_ttl' => WEEK_IN_SECONDS ] );
		$test_results = test_cache_max_age();
		$this->assertEquals( 'good', $test_results['status'] );
		$this->assertEquals( 'blue',$test_results['badge']['color'] );
		$this->assertStringContainsString( '1 week', $test_results['label'] );
		$this->assertStringContainsString( 'Pantheon GCDN Cache Max-Age set to 1 week', $test_results['label'] );
	}

	/**
	 * Test the humanized_max_age function.
	 */
	public function test_humanized_max_age() {
		$this->assertEquals( '5 mins', humanized_max_age() );

		update_option( 'pantheon-cache', [ 'default_ttl' => 5 * DAY_IN_SECONDS ] );
		$this->assertEquals( '5 days', humanized_max_age() );

		update_option( 'pantheon-cache', [ 'default_ttl' => WEEK_IN_SECONDS ] );
		$this->assertEquals( '1 week', humanized_max_age() );
	}

	/**
	 * Test the max_age_compare function.
	 */
	public function test_max_age_compare() {
		// 300 seconds is bad. It should rank the highest.
		$this->assertEquals( 10, max_age_compare() );

		// 5 days is better.
		update_option( 'pantheon-cache', [ 'default_ttl' => 5 * DAY_IN_SECONDS ] );
		$this->assertEquals( 3, max_age_compare() );

		// Default recommendation should always return 0.
		update_option( 'pantheon-cache', [ 'default_ttl' => WEEK_IN_SECONDS ] );
		$this->assertEquals( 0, max_age_compare() );

		// More than the recommendation is also good and should always return 0.
		update_option( 'pantheon-cache', [ 'default_ttl' => 2 * WEEK_IN_SECONDS ] );
		$this->assertEquals( 0, max_age_compare() );
	}

	/**
	 * Test the delete transient on option update hook.
	 */
	public function test_delete_transient_on_option_update() {
		// Run max_age_compare to set the transient.
		max_age_compare();
		$cached_max_age_compare = get_transient( 'papc_max_age_compare' );
		$this->assertTrue( false !== $cached_max_age_compare );
		$this->assertEquals( 10 , $cached_max_age_compare );

		// Update the option.
		update_option( 'pantheon-cache', [ 'default_ttl' => 5 * DAY_IN_SECONDS ] );
		max_age_compare();
		$cached_max_age_compare = get_transient( 'papc_max_age_compare' );
		$this->assertTrue( false !== $cached_max_age_compare );
		$this->assertEquals( 3 , $cached_max_age_compare );

		// Update the option again. The transient should be deleted if the max_age_compare rank is 0.
		update_option( 'pantheon-cache', [ 'default_ttl' => WEEK_IN_SECONDS ] );
		max_age_compare();
		$cached_max_age_compare = get_transient( 'papc_max_age_compare' );
		$this->assertFalse( $cached_max_age_compare );
	}

}
