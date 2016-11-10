<?php
/**
 * Plugin Name:     Pantheon Advanced Page Cache
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Pantheon
 * Author URI:      https://pantheon.io
 * Text Domain:     pantheon-advanced-page-cache
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Pantheon_Advanced_Page_Cache
 */

/**
 * Purge the cache for specific surrogate keys.
 *
 * @param array $keys Surrogate keys to purge.
 */
function pantheon_wp_clear_edge_keys( $keys ) {

	/**
	 * Fires when purging specific surrogate keys.
	 *
	 * @param array $keys Surrogate keys to purge.
	 */
	do_action( 'pantheon_wp_clear_edge_keys', $keys );

	try {
		if ( function_exists( 'pantheon_clear_edge_keys' ) ) {
			pantheon_clear_edge_keys( $keys );
		}
	} catch ( Exception $e ) {
		return new WP_Error( 'pantheon_clear_edge_keys', $e->getMessage() );
	}
	return true;
}

/**
 * Purge the cache for specific paths.
 *
 * @param array $paths URI paths to purge.
 */
function pantheon_wp_clear_edge_paths( $paths ) {

	/**
	 * Fires when purging specific URI paths.
	 *
	 * @param array $paths URI paths to purge.
	 */
	do_action( 'pantheon_wp_clear_edge_paths', $paths );

	try {
		if ( function_exists( 'pantheon_clear_edge_paths' ) ) {
			pantheon_clear_edge_paths( $paths );
		}
	} catch ( Exception $e ) {
		return new WP_Error( 'pantheon_clear_edge_paths', $e->getMessage() );
	}
	return true;
}

/**
 * Purge the entire cache.
 */
function pantheon_wp_clear_edge_all() {

	/**
	 * Fires when purging the entire cache.
	 */
	do_action( 'pantheon_wp_clear_edge_all' );

	try {
		if ( function_exists( 'pantheon_clear_edge_all' ) ) {
			pantheon_clear_edge_all();
		}
	} catch ( Exception $e ) {
		return new WP_Error( 'pantheon_clear_edge_all', $e->getMessage() );
	}
	return true;
}

/**
 * Registers the class autoloader.
 */
spl_autoload_register( function( $class ) {
	$class = ltrim( $class, '\\' );
	if ( 0 !== stripos( $class, 'Pantheon_Advanced_Page_Cache\\' ) ) {
		return;
	}

	$parts = explode( '\\', $class );
	array_shift( $parts ); // Don't need "Pantheon_Advanced_Page_Cache".
	$last = array_pop( $parts ); // File should be 'class-[...].php'.
	$last = 'class-' . $last . '.php';
	$parts[] = $last;
	$file = dirname( __FILE__ ) . '/inc/' . str_replace( '_', '-', strtolower( implode( $parts, '/' ) ) );
	if ( file_exists( $file ) ) {
		require $file;
	}
});

/**
 * Registers relevant admin UI
 */
add_action( 'pantheon_cache_settings_page_bottom', array( 'Pantheon_Advanced_Page_Cache\Admin', 'action_pantheon_cache_settings_page_bottom' ) );

/**
 * Emits the appropriate surrogate tags per view.
 */
add_filter( 'wp', array( 'Pantheon_Advanced_Page_Cache\Emitter', 'action_wp' ) );

/**
 * Clears surrogate tags when various modification behaviors are performed.
 */
add_action( 'wp_insert_post', array( 'Pantheon_Advanced_Page_Cache\Purger', 'action_wp_insert_post' ) );
add_action( 'before_delete_post', array( 'Pantheon_Advanced_Page_Cache\Purger', 'action_before_delete_post' ) );
add_action( 'delete_attachment', array( 'Pantheon_Advanced_Page_Cache\Purger', 'action_delete_attachment' ) );
add_action( 'clean_post_cache', array( 'Pantheon_Advanced_Page_Cache\Purger', 'action_clean_post_cache' ) );
add_action( 'created_term', array( 'Pantheon_Advanced_Page_Cache\Purger', 'action_created_term' ) );
add_action( 'edited_term', array( 'Pantheon_Advanced_Page_Cache\Purger', 'action_edited_term' ) );
add_action( 'delete_term', array( 'Pantheon_Advanced_Page_Cache\Purger', 'action_delete_term' ) );
add_action( 'clean_term_cache', array( 'Pantheon_Advanced_Page_Cache\Purger', 'action_clean_term_cache' ) );
add_action( 'clean_user_cache', array( 'Pantheon_Advanced_Page_Cache\Purger', 'action_clean_user_cache' ) );

/**
 * Registers the WP-CLI commands.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pantheon cache', 'Pantheon_Advanced_Page_Cache\CLI' );
}
