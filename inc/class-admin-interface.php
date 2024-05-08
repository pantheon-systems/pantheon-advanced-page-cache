<?php
/**
 * Controller for the admin interface that builds on top of the Pantheon MU plugin.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

namespace Pantheon_Advanced_Page_Cache;

/**
 * Controller for admin UI.
 */
class Admin_Interface {
	/**
	 * Kick off the important bits.
	 */
	public static function bootstrap() {
		// Check if wp_admin_notice exists. We've already noted that the plugin requires at least 6.4, so we're going to not display a notice if you didn't listen to the recommendation.
		if ( ! function_exists( 'wp_admin_notice' ) ) {
			add_filter( 'pantheon_apc_disable_admin_notices', '__return_true' );
		}

		if ( defined( 'PANTHEON_MU_PLUGIN_VERSION' ) ) {
			// Only do things here if we've got the MU plugin and it's > 1.4.0.
			if ( version_compare( PANTHEON_MU_PLUGIN_VERSION, '1.4.0', '>' ) ) {
				// Do stuff, e.g. add_action().
			} else {
				// admin notice to update the WordPress upstream. maybe detect if this is a composer site and suggest a composer update.
			}
		} else {
			// admin notice that this plugin is designed for use on Pantheon.
		}
	}
}
