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
	require dirname( dirname( dirname( __FILE__ ) ) ) . '/pantheon-advanced-page-cache.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';

require dirname( __FILE__ ) . '/class-pantheon-advanced-page-cache-testcase.php';
require dirname( __FILE__ ) . '/pantheon-edge-functions.php';
