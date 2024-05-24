<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Pantheon_Advanced_Page_Cache
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __DIR__ ) ) . '/pantheon-advanced-page-cache.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

if ( ! defined( 'REST_TESTS_IMPOSSIBLY_HIGH_NUMBER' ) ) {
	define( 'REST_TESTS_IMPOSSIBLY_HIGH_NUMBER', 99999999 );
}

// Include WP_Screen class definition if not already included
if ( ! class_exists( 'WP_Screen' ) ) {
	require_once $_tests_dir . '/includes/class-wp-screen.php';
}

require __DIR__ . '/class-pantheon-advanced-page-cache-testcase.php';
require __DIR__ . '/pantheon-edge-functions.php';
