<?php
/**
 * Controller for a variety of admin UI.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

namespace Pantheon_Advanced_Page_Cache;

/**
 * Controller for a variety of admin UI.
 */
class Admin {

	/**
	 * Render a notice about this plugin at the bottom of the cache settings page.
	 */
	public function action_pantheon_cache_settings_page_bottom() {
		?>
		<hr />

		<h3><?php _e( 'Pantheon Advanced Page Cache', 'pantheon-advanced-page-cache' ); ?></h3>

		<p><?php _e( 'Pantheon Advanced Page Cache is enabled, which automatically purges related content from the Pantheon Edge.', 'pantheon-advanced-page-cache' ); ?></p>

		<p><?php _e( 'Even with a high <em>Default Cache Time</em>, your visitors will never have to wait for fresh content. We recommend setting a Default Cache Time like 86400 seconds (1 day) in the field above.', 'pantheon-advanced-page-cache' ); ?></p>

		<p><?php echo sprintf( __( 'To learn more, see: %s', 'pantheon-advanced-page-cache' ), '<a href="https://github.com/pantheon-systems/pantheon-advanced-page-cache">pantheon-systems/pantheon-advanced-page-cache</a>' ); ?></p>
		<?php
	}
}
