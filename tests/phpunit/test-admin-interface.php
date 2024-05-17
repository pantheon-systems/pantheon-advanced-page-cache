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
		delete_transient( 'papc_max_age_compare' );
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
	 * Test the Site Health tests with very low cache max age.
	 */
	public function test_site_health_tests_300_seconds() {
		$tests = apply_filters( 'site_status_tests', [] );

		$this->assertContains( 'pantheon_edge_cache', array_keys( $tests['direct'] ) );

		// Base test with 300 second max-age.
		$test_results = test_cache_max_age();
		$this->assertEquals( 'recommended', $test_results['status'] );
		$this->assertEquals( 'red',$test_results['badge']['color'] );
		$this->assertStringContainsString( '300 seconds', $test_results['description'] );
		$this->assertStringContainsString( 'We recommend increasing to 1 week', $test_results['description'] );
	}

	/**
	 * Test the Site Health tests with 5 day cache max age.
	 */
	public function test_site_health_tests_5_days() {
		// Update the option and rerun.
		update_option( 'pantheon-cache', [ 'default_ttl' => 5 * DAY_IN_SECONDS ] );
		$test_results = test_cache_max_age();
		$this->assertEquals( 'recommended', $test_results['status'] );
		$this->assertEquals( 'orange',$test_results['badge']['color'] );
		$this->assertStringContainsString( '5 days', $test_results['description'] );
		$this->assertStringContainsString( 'We recommend increasing to 1 week', $test_results['description'] );
	}

	/**
	 * Test the Site Health tests with 1 week cache max age.
	 */
	public function test_site_health_tests_1_week() {
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
	 *
	 * @dataProvider humanized_max_age_provider
	 */
	public function test_humanized_max_age( $max_age, $expected ) {
		update_option( 'pantheon-cache', [ 'default_ttl' => $max_age ] );
		$this->assertEquals( $expected, humanized_max_age() );
	}

	/**
	 * Data provider for test_humanized_max_age.
	 *
	 * @return array
	 */
	public function humanized_max_age_provider() {
		return [
			[ 300, '5 mins' ], // 300 seconds is humanized to 5 mins.
			[ 5 * DAY_IN_SECONDS, '5 days' ],
			[ WEEK_IN_SECONDS, '1 week' ],
		];
	}

	/**
	 * Test the max_age_compare function.
	 *
	 * @dataProvider max_age_compare_provider
	 */
	public function test_max_age_compare( $max_age, $expected ){
		update_option( 'pantheon-cache', [ 'default_ttl' => $max_age ] );
		$this->assertEquals( $expected, max_age_compare() );
	}

	/**
	 * Data provider for test_max_age_compare.
	 *
	 * @return array
	 */
	public function max_age_compare_provider() {
		return [
			[ 300, 10 ], // 300 seconds is bad. It should rank the highest.
			[ 5 * DAY_IN_SECONDS, 3 ], // 5 days is better.
			[ WEEK_IN_SECONDS, 0 ], // Default recommendation should always return 0.
			[ 2 * WEEK_IN_SECONDS, 0 ], // More than the recommendation is also good and should always return 0.
		];
	}

	/**
	 * Test the delete transient on option update hook.
	 *
	 * @dataProvider delete_transient_on_option_update_provider
	 */
	public function test_delete_transient_on_option_update( $expected, $max_age ) {
		update_option( 'pantheon-cache', [ 'default_ttl' => $max_age] );
		max_age_compare();
		$cached_max_age_compare = get_transient( 'papc_max_age_compare' );
		// When the max_age_compare rank is zero, which is the case when it is at least the recommended 1 week, the transient will be deleted.
		if ( $max_age === WEEK_IN_SECONDS ) {
			$this->assertFalse( $cached_max_age_compare );
		} else {
			$this->assertNotFalse( $cached_max_age_compare );
		}
		$this->assertEquals( $expected, $cached_max_age_compare );
	}

	/**
	 * Data provider for delete_transient_on_option_update.
	 *
	 * @return array
	 */
	public function delete_transient_on_option_update_provider() {
		return [
			[ 10, 300 ],
			[ 3, 5 * DAY_IN_SECONDS ],
			[ 0, WEEK_IN_SECONDS ],
		];
	}

}
