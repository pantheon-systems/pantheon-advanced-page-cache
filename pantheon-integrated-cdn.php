<?php
/**
 * Plugin Name:     Pantheon Integrated CDN
 * Plugin URI:      PLUGIN SITE HERE
 * Description:     PLUGIN DESCRIPTION HERE
 * Author:          Pantheon
 * Author URI:      https://pantheon.io
 * Text Domain:     pantheon-integrated-cdn
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Pantheon_Integrated_Cdn
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

	if ( function_exists( 'pantheon_clear_edge_keys' ) ) {
		pantheon_clear_edge_keys( $keys );
	}
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

	if ( function_exists( 'pantheon_clear_edge_paths' ) ) {
		pantheon_clear_edge_paths( $paths );
	}
}

/**
 * Purge the entire cache.
 */
function pantheon_wp_clear_edge_all() {

	/**
	 * Fires when purging the entire cache.
	 */
	do_action( 'pantheon_wp_clear_edge_all' );

	if ( function_exists( 'pantheon_clear_edge_all' ) ) {
		pantheon_clear_edge_all();
	}
}

/**
 * Registers the class autoloader.
 */
spl_autoload_register( function( $class ) {
	$class = ltrim( $class, '\\' );
	if ( 0 !== stripos( $class, 'Pantheon_Integrated_CDN\\' ) ) {
		return;
	}

	$parts = explode( '\\', $class );
	array_shift( $parts ); // Don't need "Pantheon_Integrated_CDN".
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
add_action( 'pantheon_cache_settings_page_bottom', array( 'Pantheon_Integrated_CDN\Admin', 'action_pantheon_cache_settings_page_bottom' ) );

/**
 * Emits the appropriate surrogate tags per view.
 */
add_filter( 'wp', array( 'Pantheon_Integrated_CDN\Emitter', 'action_wp' ) );

/**
 * Clears surrogate tags when object caches are cleared.
 */
add_action( 'clean_post_cache', array( 'Pantheon_Integrated_CDN\Purger', 'action_clean_post_cache' ) );
add_action( 'clean_term_cache', array( 'Pantheon_Integrated_CDN\Purger', 'action_clean_term_cache' ) );
add_action( 'clean_user_cache', array( 'Pantheon_Integrated_CDN\Purger', 'action_clean_user_cache' ) );
