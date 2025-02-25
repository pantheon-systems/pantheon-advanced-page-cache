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
	 * Get the WordPress version-compatible string for "5 minutes".
	 */
	private function get_five_minutes() {
		$wp_version = get_bloginfo( 'version' );

		// If the version contains a string like -alpha, -beta, etc., strip it.
		$wp_version = preg_replace( '/-.*/', '', $wp_version );

		// WordPress 6.7 changed the string for "5 mins" to "5 minutes".
		$five_mins = version_compare( $wp_version, '6.7', '<' ) ? '5 mins' : '5 minutes';
		return $five_mins;
	}

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
		delete_option( 'pantheon_max_age_updated' );
		delete_transient( 'papc_max_age_compare' );
		remove_all_filters( 'pantheon_cache_default_max_age' );
		remove_all_filters( 'pantheon_apc_disable_admin_notices' );
		remove_all_filters( 'nonce_life' );
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
		$five_mins = $this->get_five_minutes();

		$this->assertContains( 'pantheon_edge_cache', array_keys( $tests['direct'] ) );

		// Base test with 300 second max-age.
		$test_results = test_cache_max_age();
		$this->assertEquals( 'recommended', $test_results['status'] );
		$this->assertEquals( 'red', $test_results['badge']['color'] );

		$this->assertStringContainsString( $five_mins, $test_results['description'] );
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
		$this->assertEquals( 'orange', $test_results['badge']['color'] );
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
		$this->assertEquals( 'blue', $test_results['badge']['color'] );
		$this->assertStringContainsString( '1 week', $test_results['label'] );
		$this->assertStringContainsString( 'Pantheon GCDN Cache Max Age set to 1 week', $test_results['label'] );
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
		$five_mins = $this->get_five_minutes();

		return [
			[ 300, $five_mins ], // 300 seconds is humanized to 5 mins.
			[ 5 * DAY_IN_SECONDS, '5 days' ],
			[ WEEK_IN_SECONDS, '1 week' ],
		];
	}

	/**
	 * Test the max_age_compare function.
	 *
	 * @dataProvider max_age_compare_provider
	 */
	public function test_max_age_compare( $max_age, $expected ) {
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
		update_option( 'pantheon-cache', [ 'default_ttl' => $max_age ] );
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

	/**
	 * Test running set_max_age_to_default when no option has been set.
	 */
	public function test_set_max_age_to_default_start() {
		// Default start state.
		delete_option( 'pantheon-cache' );
		set_max_age_to_default();
		$pantheon_cache = get_option( 'pantheon-cache' );
		$max_age_updated = get_option( 'pantheon_max_age_updated' );
		$this->assertFalse( isset( $pantheon_cache['default_ttl'] ), 'The default_ttl option should not be set.' );
		$this->assertFalse( $max_age_updated, 'The max age updated option should not be set.' );
	}

	/**
	 * Test running set_max_age_to_default when the default_ttl is 600.
	 */
	public function test_set_max_age_to_default_600() {
		// Cache max-age set to 600 and we haven't updated it since the notice.
		$pantheon_cache = [];
		$pantheon_cache['default_ttl'] = 600;
		update_option( 'pantheon-cache', $pantheon_cache );
		$pantheon_cache = get_option( 'pantheon-cache' );
		// Validate that the option was set correctly. We'll only check this once.
		$this->assertEquals( 600, $pantheon_cache['default_ttl'], 'The default_ttl option should be set to 600.' );
		set_max_age_to_default();
		$pantheon_cache = get_option( 'pantheon-cache' );
		$max_age_updated = get_option( 'pantheon_max_age_updated' );
		$this->assertEquals( WEEK_IN_SECONDS, $pantheon_cache['default_ttl'], 'The default_ttl option should be set to 1 week.' );
		$this->assertTrue( $max_age_updated, 'The max age updated option should be true.' );
	}

	/**
	 * Test running set_max_age_to_default when the default_ttl was manually reset to 600.
	 */
	public function test_set_max_age_to_default_600_reset() {
		// Cache max-age set to 600 and we have updated it since the notice.
		$pantheon_cache = [];
		$pantheon_cache['default_ttl'] = 600;
		update_option( 'pantheon-cache', $pantheon_cache );
		update_option( 'pantheon_max_age_updated', true );
		set_max_age_to_default();
		$pantheon_cache = get_option( 'pantheon-cache' );
		$this->assertEquals( 600, $pantheon_cache['default_ttl'], 'The default_ttl option should be set to 600.' );
	}

	/**
	 * Test running set_max_age_to_default when the default_ttl is 432000.
	 */
	public function test_set_max_age_to_default_432000() {
		// Cache max-age set to anything else. We shouldn't ever see the notice.
		delete_option( 'pantheon_max_age_updated' );
		$pantheon_cache = [];
		$pantheon_cache['default_ttl'] = 432000;
		update_option( 'pantheon-cache', $pantheon_cache );
		set_max_age_to_default();
		$pantheon_cache = get_option( 'pantheon-cache' );
		$max_age_updated = get_option( 'pantheon_max_age_updated' );
		$this->assertEquals( 432000, $pantheon_cache['default_ttl'], 'The default_ttl option should be set to 432000.' );
		$this->assertTrue( $max_age_updated, 'The max age updated option should be true.' );
	}

	/**
	 * Test running set_max_age_to_default when the default_ttl is 600 and a filter is set.
	 */
	public function test_set_max_age_to_default_600_filter() {
		// Use the filter to override the default. If a site had 600 set, we should still update it to the filtered value.
		add_filter( 'pantheon_cache_default_max_age', function () {
			return 3 * DAY_IN_SECONDS;
		} );
		$pantheon_cache = [];
		$pantheon_cache['default_ttl'] = 600;
		update_option( 'pantheon-cache', $pantheon_cache );
		set_max_age_to_default();
		$pantheon_cache = [];
		$pantheon_cache = get_option( 'pantheon-cache' );
		$max_age_updated = get_option( 'pantheon_max_age_updated' );
		$this->assertEquals( 3 * DAY_IN_SECONDS, $pantheon_cache['default_ttl'], 'The default_ttl option should be set to 3 days (the filtered value).' );
		$this->assertTrue( $max_age_updated, 'The max age updated option should be true.' );
	}

	/**
	 * Test the admin notice for the max age being updated.
	 */
	public function test_max_age_updated_admin_notice() {
		// Switch to admin.
		wp_set_current_user( 1 );

		// We're testing notices but we don't want to display the "no mu plugin" notice.
		add_filter( 'pantheon_apc_disable_admin_notices', function ( $disable_notices, $callback ) {
			if ( $callback === __NAMESPACE__ . '\\admin_notice_no_mu_plugin' ) {
				return true;
			}
			return $disable_notices;
		}, 10, 2 );

		$current_user_id = get_current_user_id();

		// Reset everything to start.
		delete_option( 'pantheon-cache' );
		delete_user_meta( $current_user_id, 'pantheon_max_age_updated_notice' );

		// Make sure the option says we've updated the max age.
		update_option( 'pantheon_max_age_updated', true );
		update_option( 'pantheon-cache', [ 'default_ttl' => WEEK_IN_SECONDS ] );

		ob_start();
		max_age_updated_admin_notice();
		$notice = ob_get_clean();

		// The notice that we're catching should be the one that the max-age was updated.
		$this->assertStringContainsString( 'The Pantheon GCDN cache max age has been updated. The previous value was 10 minutes. The new value is 1 week.', $notice );
		// The user meta should have been updated in the process.
		$this->assertEquals( 1, get_user_meta( $current_user_id, 'pantheon_max_age_updated_notice', true ) );
	}

	/**
	 * Test that the user meta for the global admin notice is created.
	 */
	public function test_low_max_age_admin_notice_user_meta() {
		// Switch to admin.
		wp_set_current_user( 1 );
		delete_user_meta( 1, 'pantheon_max_age_global_warning_notice' );
		ob_start();
		admin_notice_maybe_recommend_higher_max_age();
		$notice = ob_get_clean();

		$notice_shown = get_user_meta( 1, 'pantheon_max_age_global_warning_notice', true );
		$this->assertStringContainsString( 'notice-error', $notice );
		$this->assertStringContainsString( 'Your site\'s cache max age is set below the recommendation (1 week).', $notice );
		$this->assertEquals( 1, $notice_shown );
	}

	/**
	 * Test that the user meta for the global admin notice is created.
	 */
	public function test_low_max_age_admin_notice_user_meta_warning() {
		// Switch to admin.
		wp_set_current_user( 1 );
		delete_user_meta( 1, 'pantheon_max_age_global_warning_notice' );
		update_option( 'pantheon-cache', [ 'default_ttl' => 432000 ] );
		ob_start();
		admin_notice_maybe_recommend_higher_max_age();
		$notice = ob_get_clean();

		$notice_shown = get_user_meta( 1, 'pantheon_max_age_global_warning_notice', true );
		$this->assertStringContainsString( 'notice-warning', $notice );
		$this->assertStringContainsString( 'Your site\'s cache max age is set below the recommendation (1 week).', $notice );
		$this->assertEquals( 1, $notice_shown );
	}

	/**
	 * Test the max age setting description.
	 *
	 * @dataProvider add_max_age_setting_description_provider
	 */
	public function test_add_max_age_setting_description( $max_age, $expected ) {

		if ( $max_age === 'filter-below' ) {
			// Filter the max age and test again.
			add_filter( 'pantheon_cache_default_max_age', function () {
				return 3 * DAY_IN_SECONDS;
			} );
		} elseif ( $max_age === 'filter-above' ) {
			// Filter the max age and test again.
			add_filter( 'pantheon_cache_default_max_age', function () {
				return 10 * DAY_IN_SECONDS;
			} );
		} elseif ( $max_age === 'multiple-filters-below' ) {
			add_filter( 'pantheon_cache_default_max_age', [ $this, 'reset_cache_max_age' ] );
			add_filter( 'pantheon_cache_default_max_age', [ $this, 'another_function' ] );
			add_filter( 'pantheon_cache_default_max_age', function () {
				return 3 * DAY_IN_SECONDS;
			} );
		} elseif ( $max_age === 'just-named-filters' ) {
			add_filter( 'pantheon_cache_default_max_age', [ $this, 'reset_cache_max_age' ] );
		} else {
			update_option( 'pantheon-cache', [ 'default_ttl' => $max_age ] );
		}

		$output = add_max_age_setting_description();
		$this->assertStringContainsString( $expected, $output );
	}

	/**
	 * Data provider for test_add_max_age_setting_description.
	 *
	 * @return array
	 */
	public function add_max_age_setting_description_provider() {
		return [
			[ 'filter-below', 'Your cache maximum age is currently <strong>below</strong> the recommended value. This value has been hardcoded to <strong>3 days</strong> via a filter hooked to an anonymous function in your code.' ],
			[ 'filter-above', 'Your cache maximum age is currently <strong>above</strong> the recommended value. This value has been hardcoded to <strong>1 week</strong> via a filter hooked to an anonymous function in your code.' ],
			[ 'multiple-filters-below', 'Your cache maximum age is currently <strong>below</strong> the recommended value. This value has been hardcoded to <strong>3 days</strong> via a filter hooked to <code>Pantheon_Advanced_Page_Cache\Admin_Interface\Admin_Interface_Functions::reset_cache_max_age</code>, <code>Pantheon_Advanced_Page_Cache\Admin_Interface\Admin_Interface_Functions::another_function</code>, and an anonymous function in your code.' ],
			[ 'just-named-filters', 'Your cache maximum age is currently <strong>below</strong> the recommended value. This value has been hardcoded to <strong>1 second</strong> via a filter hooked to <code>Pantheon_Advanced_Page_Cache\Admin_Interface\Admin_Interface_Functions::reset_cache_max_age<code> in your code.' ],
			[ 600, 'Your cache maximum age is currently <strong>below</strong> the recommended value. <br /><strong>Warning:</strong>The cache max age is not one of the recommended values.' ],
			[ WEEK_IN_SECONDS, 'Your cache maximum age is currently set to the recommended value.' ],
			[ MONTH_IN_SECONDS, 'Your cache maximum age is currently <strong>above</strong> the recommended value.' ],
			[ YEAR_IN_SECONDS, 'Your cache maximum age is currently <strong>above</strong> the recommended value.' ],
		];
	}

	/**
	 * Filter callback for testing pantheon_cache_default_max_age.
	 */
	public function reset_cache_max_age() {
		return 0;
	}

	/**
	 * Filter callback for testing pantheon_cache_default_max_age.
	 */
	public function another_function() {
		return 42;
	}

	/**
	 * Test the update_default_ttl_input function. Check the input type if the max age has been filtered.
	 *
	 * @dataProvider update_default_ttl_input_provider
	 */
	public function test_update_default_ttl_input( $max_age, $expected ) {
		if ( $max_age === 'filter' ) {
			// Filter the max age and test again.
			add_filter( 'pantheon_cache_default_max_age', function () {
				return 3 * DAY_IN_SECONDS;
			} );
		} else {
			update_option( 'pantheon-cache', [ 'default_ttl' => $max_age ] );
		}

		$default_ttl_field = $this->default_ttl_field_mock();
		$output = update_default_ttl_input( $default_ttl_field );
		$this->assertStringContainsString( $expected, $output );
	}

	/**
	 * Data provider for test_update_default_ttl_input.
	 *
	 * @return array
	 */
	public function update_default_ttl_input_provider() {
		return [
			[ 3 * DAY_IN_SECONDS, 'Custom (3 days)' ],
			[ WEEK_IN_SECONDS, 'select' ],
			[ 'filter', 'disabled' ],
		];
	}

	/**
	 * Add the HTML for the default max-age field.
	 *
	 * Copy pasta from pantheon-mu-plugin with output buffering.
	 *
	 * @return string
	 */
	private function default_ttl_field_mock() {
		$options = get_option( 'pantheon-cache' );
		ob_start();
		$disabled = ( has_filter( 'pantheon_cache_default_max_age' ) ) ? ' disabled' : '';
		echo wp_kses_post( apply_filters( 'pantheon_cache_max_age_field_before_html', '<div class="pantheon-cache-default-max-age">' ) );
		echo '<h3>' . esc_html__( 'Page Cache Max Age', 'pantheon-cache' ) . '</h3>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '<p>' . wp_kses_post( sprintf(
			// translators: %s is a link to the Pantheon Advanced Page Cache plugin page.
			__( 'When your site content is updated, <a href="%s">Pantheon Advanced Page Cache</a> clears page cache automatically. This setting determines how long a page will be stored in the Global Content Delivery Network (GCDN) cache before GCDN retrieves the content from WordPress again.', 'pantheon-cache' ),
			'https://wordpress.org/plugins/pantheon-advanced-page-cache/'
		) ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		$input_field = '<input type="text" name="pantheon-cache[default_ttl]" value="' . $options['default_ttl'] . '" size="7" ' . $disabled . ' /> ' . esc_html__( 'seconds', 'pantheon-cache' );
		
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo apply_filters( 'pantheon_cache_max_age_input', $input_field );
		echo wp_kses_post( apply_filters( 'pantheon_cache_max_age_field_after_html', '</div>' ) );

		// Display a message if the setting is disabled.
		if ( $disabled ) {
			echo '<p>' . esc_html__( 'This setting is disabled because the default max-age has been filtered to the current value.', 'pantheon-cache' ) . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		return ob_get_clean();
	}

	/**
	 * Test the max_age_options method.
	 */
	public function test_max_age_options() {
		$options = max_age_options();
		foreach ( $this->max_age_options_mock() as $max_age => $expected ) {
			$this->assertArrayHasKey( $max_age, $options );
			$this->assertEquals( $expected, $options[ $max_age ] );
		}

		// Test the filter.
		add_filter( 'pantheon_apc_max_age_options', function () {
			return [ 3 * DAY_IN_SECONDS => 'foo (bar)' ];
		} );
		$options = max_age_options();
		$this->assertArrayHasKey( 3 * DAY_IN_SECONDS, $options );
		$this->assertEquals( 'foo (bar)', $options[ 3 * DAY_IN_SECONDS ] );
	}

	/**
	 * Option mock for test_max_age_options.
	 *
	 * @return array
	 */
	public function max_age_options_mock() {
		return [
			WEEK_IN_SECONDS => 'Recommended (1 week)',
			MONTH_IN_SECONDS => 'Extended (1 month)',
			YEAR_IN_SECONDS => 'Perpetual (1 year)',
		];
	}

	/**
	 * Test filter_nonce_cache_lifetime.
	 * Make sure that the max age is updated when creating nonces on the front-end.
	 *
	 * @dataProvider filter_nonce_cache_lifetime_provider
	 */
	public function test_filter_nonce_cache_lifetime( $screen, $expected ) {
		global $current_screen;
		if ( $screen === 'front-end' ) {
			$current_screen = null;
		} else {
			set_current_screen( $screen );
		}

		$nonce_life = apply_filters( 'nonce_life', DAY_IN_SECONDS );
		do_action( 'pantheon_cache_nonce_lifetime' );
		$cache_max_age = apply_filters( 'pantheon_cache_default_max_age', $nonce_life );

		$this->assertEquals(
			$expected,
			$cache_max_age,
			sprintf(
				// 1: Screen, 2: Cache max age, 3: Expected max age.
				'%s test failed to assert that %s was equal to %s',
				$screen,
				humanized_max_age( $cache_max_age ),
				humanized_max_age( $expected )
			)
		);
	}

	/**
	 * Data provider for test_filter_nonce_cache_lifetime.
	 *
	 * @return array
	 */
	public function filter_nonce_cache_lifetime_provider() {
		// screen, updated max_age
		return [
			[ 'dashboard', DAY_IN_SECONDS ],
			[ 'front-end', DAY_IN_SECONDS - HOUR_IN_SECONDS ],
		];
	}
}
