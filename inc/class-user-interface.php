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
	 * Render a notice about this plugin at the bottom of the cache settings page.
	 */
	public static function action_pantheon_cache_settings_page_bottom() {
		?>
		<hr />

		<h3><?php _e( 'Pantheon Advanced Page Cache', 'pantheon-advanced-page-cache' ); ?></h3>

		<p><?php _e( 'Pantheon Advanced Page Cache is enabled, which automatically purges related content from the Pantheon Edge.', 'pantheon-advanced-page-cache' ); ?></p>

		<p><?php _e( 'Even with a high <em>Default Cache Time</em>, your visitors will never have to wait for fresh content. We recommend setting a Default Cache Time like 86400 seconds (1 day) in the field above.', 'pantheon-advanced-page-cache' ); ?></p>

		<p><?php echo sprintf( __( 'To learn more, see: %s', 'pantheon-advanced-page-cache' ), '<a href="https://github.com/pantheon-systems/pantheon-advanced-page-cache">pantheon-systems/pantheon-advanced-page-cache</a>' ); ?></p>
		<?php
	}

	/**
	 * Register a toolbar button to purge the cache for the current page.
	 *
	 * @param object $wp_admin_bar Instance of WP_Admin_Bar.
	 */
	public static function action_admin_bar_menu( $wp_admin_bar ) {
		if ( is_admin() || ! is_user_logged_in() || ! current_user_can( 'delete_others_posts' ) ) {
			return;
		}

		if ( ! empty( $_GET['message'] ) && 'pantheon-cleared-page-cache' === $_GET['message'] ) {
			$title = __( 'Page Cache Cleared', 'pantheon-advanced-page-cache' );
		} else {
			$title = __( 'Clear Page Cache', 'pantheon-advanced-page-cache' );
		}

		$wp_admin_bar->add_menu( array(
			'parent'   => '',
			'id'       => 'clear-page-cache',
			'title'    => $title,
			'meta'     => array( 'title' => __( 'Delete cache of the current page.', 'pantheon-advanced-page-cache' ) ),
			'href'     => wp_nonce_url( admin_url( 'admin-ajax.php?action=pantheon_clear_page_cache&path=' . urlencode( preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', $_SERVER['REQUEST_URI'] ) ) ), 'clear-page-cache' ),
		) );
	}

	/**
	 * Handle an admin-ajax request to clear the page cache.
	 */
	public static function handle_ajax_clear_page_cache() {

		if ( empty( $_GET['_wpnonce'] )
			|| ! wp_verify_nonce( $_GET['_wpnonce'], 'clear-page-cache' )
			|| ! current_user_can( 'delete_others_posts' ) ) {
			wp_die( __( "You shouldn't be doing this.", 'pantheon-advanced-page-cache' ) );
		}

		$ret = pantheon_wp_clear_edge_paths( array( $_GET['path'] ) );
		if ( is_wp_error( $ret ) ) {
			wp_die( $ret->get_error_message() );
		}
		wp_safe_redirect( add_query_arg( 'message', 'pantheon-cleared-page-cache', preg_replace( '/[ <>\'\"\r\n\t\(\)]/', '', $_GET['path'] ) ) );
		exit;
	}
}
