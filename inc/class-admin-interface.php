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
	public function bootstrap() {
		// Only do things here if we've got the MU plugin and it's > 1.4.0.
		if ( defined( 'PANTHEON_MU_PLUGIN_VERSION' ) && version_compare( PANTHEON_MU_PLUGIN_VERSION, '1.4.0', '>' ) ) {
			// Do stuff, e.g. add_action().
		}
	}
}
