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

		// More than the recommendation is also good and should alwasy return 0.
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

	/**
	 * Test setting the max age to the default value if it was set to 600.
	 */
	public function test_set_max_age_to_default() {
		// Default start state.
		delete_option( 'pantheon-cache' );
		set_max_age_to_default();
		$pantheon_cache = get_option( 'pantheon-cache' );
		$max_age_updated = get_option( 'pantheon_max_age_updated' );
		$this->assertFalse( isset( $pantheon_cache['default_ttl'] ) );
		$this->assertFalse( $max_age_updated );

		// Cache max-age set to 600 and we haven't updated it since the notice.
		delete_option( 'pantheon-cache' );
		$pantheon_cache = [];
		$pantheon_cache['default_ttl'] = 600;
		update_option( 'pantheon-cache', $pantheon_cache );
		$pantheon_cache = get_option( 'pantheon-cache' );
		// Validate that the option was set correctly. We'll only check this once.
		$this->assertEquals( 600, $pantheon_cache['default_ttl'] );
		set_max_age_to_default();
		$pantheon_cache = get_option( 'pantheon-cache' );
		$max_age_updated = get_option( 'pantheon_max_age_updated' );
		$this->assertEquals( WEEK_IN_SECONDS, $pantheon_cache['default_ttl'] );
		$this->assertTrue( $max_age_updated );

		// Cache max-age set to 600 and we have updated it since the notice.
		delete_option( 'pantheon-cache' );
		$pantheon_cache = [];
		$pantheon_cache['default_ttl'] = 600;
		update_option( 'pantheon-cache', $pantheon_cache );
		set_max_age_to_default();
		$pantheon_cache = get_option( 'pantheon-cache' );
		$this->assertEquals( 600, $pantheon_cache['default_ttl'] );

		// Cache max-age set to anything else. We shouldn't ever see the notice.
		delete_option( 'pantheon-cache' );
		delete_option( 'pantheon_max_age_updated' );
		$pantheon_cache = [];
		$pantheon_cache['default_ttl'] = 432000;
		update_option( 'pantheon-cache', $pantheon_cache );
		set_max_age_to_default();
		$pantheon_cache = get_option( 'pantheon-cache' );
		$max_age_updated = get_option( 'pantheon_max_age_updated' );
		$this->assertEquals( 432000, $pantheon_cache['default_ttl'] );
		$this->assertTrue( $max_age_updated );

		// Use the filter to override the default. If a site had 600 set, we should still update it to the filtered value.
		add_filter( 'pantheon_cache_default_max_age', function() {
			return 3 * DAY_IN_SECONDS;
		} );
		$pantheon_cache = [];
		delete_option( 'pantheon-cache' );
		delete_option( 'pantheon_max_age_updated' );
		$pantheon_cache['default_ttl'] = 600;
		update_option( 'pantheon-cache', $pantheon_cache );
		set_max_age_to_default();
		$pantheon_cache = [];
		$pantheon_cache = get_option( 'pantheon-cache' );
		$max_age_updated = get_option( 'pantheon_max_age_updated' );
		$this->assertEquals( 3 * DAY_IN_SECONDS, $pantheon_cache['default_ttl'] );
		$this->assertTrue( $max_age_updated );
	}

	/**
	 * Test the admin notice for the max age being updated.
	 */
	function test_max_age_updated_admin_notice() {
		// Switch to admin.
		wp_set_current_user( 1 );

		// We're testing notices but we don't want to display the "no mu plugin" notice.
		add_filter( 'pantheon_apc_disable_admin_notices', function( $disable_notices, $callback ) {
			if ( $callback === __NAMESPACE__ . '\\admin_notice_no_mu_plugin' ) {
				return true;
			}
			return $disable_notices;
		}, 10, 2 );

		$current_user_id = get_current_user_id();

		// Reset everything to start.
		delete_option( 'pantheon-cache' );
		delete_user_meta( $current_user_id, 'pantheon_max_age_updated_notice' );

		// Make sure the option says we've updated the max age. We're checking the notice, not the option.
		update_option( 'pantheon_max_age_updated', true );

		ob_start();
		max_age_updated_admin_notice();
		$notice = ob_get_clean();

		// The notice that we're catching should be the one that the max-age was updated.
		$this->assertStringContainsString( 'The Pantheon GCDN cache max-age has been updated. The previous value was 10 minutes. The new value is 1 week.', $notice );
		// The user meta should have been updated in the process.
		$this->assertEquals( 1, get_user_meta( $current_user_id, 'pantheon_max_age_updated_notice', true ) );
	}
}
