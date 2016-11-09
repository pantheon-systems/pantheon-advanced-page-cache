<?php
/**
 * Mock functions available in the Pantheon environment
 *
 * @package Pantheon_Advanced_Page_Cache
 */

/**
 * Purge cache based on surrogate keys.
 *
 * @param array $keys Surrogate keys to purge.
 * @throws Exception Keys are required.
 * @return boolean
 */
function pantheon_clear_edge_keys( $keys ) {
	if ( empty( $keys ) ) {
		throw new Exception( 'Keys must not be empty' );
	}
	return true;
}

/**
 * Purge cache based on paths.
 *
 * @param array $paths Paths to purge.
 * @throws Exception Paths are required.
 * @return boolean
 */
function pantheon_clear_edge_paths( $paths ) {
	if ( empty( $paths ) ) {
		throw new Exception( 'Paths must not be empty' );
	}
	return true;
}

/**
 * Purge the entire cache.
 *
 * @throws Exception Globals are bad.
 */
function pantheon_clear_edge_all() {
	if ( ! empty( $GLOBALS['pantheon_clear_edge_all_throw_exception'] ) ) {
		throw new Exception( 'A global made me do this' );
	}
	return true;
}
