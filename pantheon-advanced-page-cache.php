<?php
/**
 * Plugin Name:     Pantheon Advanced Page Cache
 * Plugin URI:      https://wordpress.org/plugins/pantheon-advanced-page-cache/
 * Description:     Automatically clear related pages from Pantheon's Edge when you update content. High TTL. Fresh content. Visitors never wait.
 * Author:          Pantheon
 * Author URI:      https://pantheon.io
 * Text Domain:     pantheon-advanced-page-cache
 * Domain Path:     /languages
 * Version:         1.4.2
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
 * Prefix surrogate keys with the blog ID to provide compatibility with WPMS. See https://github.com/pantheon-systems/pantheon-advanced-page-cache/issues/196.
 *
 * @param array $keys Keys to be prefixed.
 */
function pantheon_wp_prefix_surrogate_keys_with_blog_id( $keys ) {
	// Do not prefix keys if this is not a multisite install.
	if ( ! is_multisite() ) {
		return $keys;
	}

	// Array that will hold the new keys.
	$prefixed_keys = [];

	$prefix = 'blog-' . get_current_blog_id() . '-';
	$prefix = apply_filters( 'pantheon_wp_surrogate_key_prepend', $prefix );
	foreach ( $keys as $key ) {
		$prefixed_keys[] = $prefix . $key;
	}

	return $prefixed_keys;
}

/**
 * Registers the class autoloader.
 */
spl_autoload_register(
	function ( $class_autoloader ) {
			$class_autoloader = ltrim( $class_autoloader, '\\' );
		if ( 0 !== stripos( $class_autoloader, 'Pantheon_Advanced_Page_Cache\\' ) ) {
			return;
		}

			$parts = explode( '\\', $class_autoloader );
			array_shift( $parts ); // Don't need "Pantheon_Advanced_Page_Cache".
			$last    = array_pop( $parts ); // File should be 'class-[...].php'.
			$last    = 'class-' . $last . '.php';
			$parts[] = $last;
			$file    = __DIR__ . '/inc/' . str_replace( '_', '-', strtolower( implode( '/', $parts ) ) );
		if ( file_exists( $file ) ) {
			require $file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
		}
	}
);

/**
 * Registers relevant UI
 */
add_action( 'admin_bar_menu', [ 'Pantheon_Advanced_Page_Cache\User_Interface', 'action_admin_bar_menu' ], 99 ); // End of the stack.
add_action( 'wp_ajax_pantheon_clear_url_cache', [ 'Pantheon_Advanced_Page_Cache\User_Interface', 'handle_ajax_clear_url_cache' ] );

/**
 * Emits the appropriate surrogate tags per view.
 */
add_filter( 'wp', [ 'Pantheon_Advanced_Page_Cache\Emitter', 'action_wp' ] );
add_action( 'rest_api_init', [ 'Pantheon_Advanced_Page_Cache\Emitter', 'action_rest_api_init' ] );
add_filter( 'rest_pre_dispatch', [ 'Pantheon_Advanced_Page_Cache\Emitter', 'filter_rest_pre_dispatch' ], 10, 3 );
add_filter( 'rest_post_dispatch', [ 'Pantheon_Advanced_Page_Cache\Emitter', 'filter_rest_post_dispatch' ], 10, 2 );

add_filter( 'graphql_dataloader_get_model', [ 'Pantheon_Advanced_Page_Cache\Emitter', 'filter_graphql_dataloader_get_model' ] );
add_filter( 'graphql_response_headers_to_send', [ 'Pantheon_Advanced_Page_Cache\Emitter', 'filter_graphql_response_headers_to_send' ] );

/**
 * Clears surrogate tags when various modification behaviors are performed.
 */
add_action( 'wp_insert_post', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_wp_insert_post' ], 10, 2 );
add_action( 'transition_post_status', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_transition_post_status' ], 10, 3 );
add_action( 'before_delete_post', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_before_delete_post' ] );
add_action( 'delete_attachment', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_delete_attachment' ] );
add_action( 'clean_post_cache', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_clean_post_cache' ] );
add_action( 'created_term', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_created_term' ], 10, 3 );
add_action( 'edited_term', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_edited_term' ] );
add_action( 'delete_term', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_delete_term' ] );
add_action( 'clean_term_cache', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_clean_term_cache' ] );
add_action( 'wp_insert_comment', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_wp_insert_comment' ], 10, 2 );
add_action( 'transition_comment_status', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_transition_comment_status' ], 10, 3 );
add_action( 'clean_comment_cache', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_clean_comment_cache' ] );
add_action( 'clean_user_cache', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_clean_user_cache' ] );
add_action( 'updated_option', [ 'Pantheon_Advanced_Page_Cache\Purger', 'action_updated_option' ] );

/**
 * Registers the WP-CLI commands.
 */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pantheon cache', 'Pantheon_Advanced_Page_Cache\CLI' );
}
