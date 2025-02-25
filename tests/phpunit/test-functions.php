<?php
/**
 * Tests for the helper functions
 *
 * @package Pantheon_Advanced_Page_Cache
 */

/**
 * Tests for the helper functions.
 */
class Test_Functions extends Pantheon_Advanced_Page_Cache_Testcase {

	/**
	 * Set up test function tests.
	 */
	public function setUp(): void {
		parent::setUp();
		$GLOBALS['pantheon_clear_edge_all_throw_exception'] = false;
	}

	/**
	 * Ensure Exception is caught when keys aren't supplied.
	 */
	public function test_clear_edge_keys_missing() {
		$this->assertWPError( pantheon_wp_clear_edge_keys( [] ) );
	}

	/**
	 * Ensure function returns true when keys are supplied.
	 */
	public function test_clear_edge_keys_exist() {
		$this->assertTrue( pantheon_wp_clear_edge_keys( [ 'post-1' ] ) );
	}

	/**
	 * Ensure Exception is caught when paths aren't supplied.
	 */
	public function test_clear_edge_paths_missing() {
		$this->assertWPError( pantheon_wp_clear_edge_paths( [] ) );
	}

	/**
	 * Ensure function returns true when paths are supplied.
	 */
	public function test_clear_edge_paths_exist() {
		$this->assertTrue( pantheon_wp_clear_edge_paths( [ '/' ] ) );
	}

	/**
	 * Ensure Exception is caught when there's an error flushing the cache
	 */
	public function test_clear_edge_all_exception() {
		$GLOBALS['pantheon_clear_edge_all_throw_exception'] = true;
		$this->assertWPError( pantheon_wp_clear_edge_all() );
	}

	/**
	 * Ensure function returns true under normal operations.
	 */
	public function test_clear_edge_all_valid() {
		$this->assertTrue( pantheon_wp_clear_edge_all() );
	}
}
