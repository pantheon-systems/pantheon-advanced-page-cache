<?php
/**
 * Controller for a variety of user interfaces
 *
 * @package Pantheon_Advanced_Page_Cache
 */

namespace Pantheon_Advanced_Page_Cache;

/**
 * Controller for a variety of admin UI.
 */
class User_Interface {
	/**
	 * Register a toolbar button to purge the cache for the current page.
	 *
	 * @param object $wp_admin_bar Instance of WP_Admin_Bar.
	 */
	public static function action_admin_bar_menu( $wp_admin_bar ) {
		if ( is_admin() || ! is_user_logged_in() || ! ( current_user_can( 'delete_others_posts' ) || current_user_can( 'manage_options' ) ) ) {
			return;
		}

		// Todo: Maybe add nonce check here.
		if ( ! empty( $_GET['message'] ) && 'pantheon-cleared-url-cache' === $_GET['message'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$title = esc_html__( 'URL Cache Cleared', 'pantheon-advanced-page-cache' );
		} else {
			$title = esc_html__( 'Clear URL Cache', 'pantheon-advanced-page-cache' );
		}

		$request_uri = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( $_SERVER['REQUEST_URI'] ) : '';
		$wp_admin_bar->add_menu( [
			'parent' => '',
			'id'     => 'clear-page-cache',
			'title'  => $title,
			'meta'   => [
				'title' => __( 'Delete cache of the current URL.', 'pantheon-advanced-page-cache' ),
			],
			'href'   => wp_nonce_url( admin_url( 'admin-ajax.php?action=pantheon_clear_url_cache&path=' . rawurlencode( preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', $request_uri ) ) ), 'clear-url-cache' ),
		] );
	}

	/**
	 * Handle an admin-ajax request to clear the URL cache.
	 */
	public static function handle_ajax_clear_url_cache() {
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( $_GET['_wpnonce'] ) : '';
		if ( empty( $nonce )
			|| ! wp_verify_nonce( $nonce, 'clear-url-cache' )
			|| ! current_user_can( 'delete_others_posts' ) ) {
			wp_die( esc_html__( "You shouldn't be doing this.", 'pantheon-advanced-page-cache' ) );
		}

		$path = isset( $_GET['path'] ) ? sanitize_text_field( $_GET['path'] ) : '';
		$ret = pantheon_wp_clear_edge_paths( [ $path ] );
		if ( is_wp_error( $ret ) ) {
			wp_die( wp_kses_post( $ret->get_error_message() ) );
		}
		wp_safe_redirect( add_query_arg( 'message', 'pantheon-cleared-url-cache', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', $path ) ) );
		exit;
	}
}
