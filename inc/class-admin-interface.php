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
				add_action( 'admin_notices', [ __NAMESPACE__ . '\\Admin_Interface' , 'admin_notice_old_mu_plugin' ] );
			}
		} else {
			add_action( 'admin_notices', [ __NAMESPACE__ . '\\Admin_Interface' , 'admin_notice_no_mu_plugin' ] );
		}
	}

	/**
	 * Display an admin notice if the Pantheon MU plugin was not found.
	 */
	public static function admin_notice_no_mu_plugin() {
		/**
		 * Allow disabling the admin notice.
		 *
		 * @param bool $disable_admin_notices Whether to disable the admin notice.
		 */
		if ( apply_filters( 'pantheon_apc_disable_admin_notices', false ) ) {
			return;
		}

		wp_admin_notice(
			// translators: %s is a link to the Pantheon MU plugin.
			sprintf( __( 'Pantheon Advanced Page Cache works best on the Pantheon platform. If you are working inside a Pantheon environment, ensure your site is using the <a href="%s">Pantheon MU plugin</a>.', 'pantheon-advanced-page-cache' ), 'https://github.com/pantheon-systems/pantheon-mu-plugin' ),
			[
				'type' => 'error',
				'dismissible' => true,
			]
		);
	}

	/**
	 * Display an admin notice if the Pantheon MU plugin is out of date.
	 */
	public static function admin_notice_old_mu_plugin() {
		if ( apply_filters( 'pantheon_apc_disable_admin_notices', false ) ) {
			return;
		}

		$mu_plugin_version = PANTHEON_MU_PLUGIN_VERSION;
		$message = sprintf(
			__( 'You appear to have an old version of the <a href="%1$s">Pantheon MU plugin</a>. 1.4.0 or above expected but %2$s found.', 'pantheon-advanced-page-cache' ),
			'https://github.com/pantheon-systems/pantheon-mu-plugin',
			$mu_plugin_version
		);

		// Check if there's a composer.json file in the root of the site.
		if ( file_exists( ABSPATH . 'composer.json' ) ) {
			$message .= ' ' . __( 'If you are using Composer, you can update the MU plugin by running <code>composer update</code>.', 'pantheon-advanced-page-cache' );
		} else {
			$message .= ' ' . __( 'You should Apply Updates from the Pantheon Dashboard to get the latest version of WordPress and the Pantheon MU plugin.', 'pantheon-advanced-page-cache' );
		}

		wp_admin_notice(
			// translators: %s is a link to the Pantheon MU plugin.
			$message,
			[
				'type' => 'warning',
				'dismissible' => true,
			]
		);
	}
	/**
	 * Get the current max-age value.
	 *
	 * This comes from the Pantheon mu-plugin and only exists if settings were actually saved.
	 *
	 * If the site existed prior to 1.4.0 of the mu-plugin, the default value is 600 seconds. Otherwise, the default value is 1 week.
	 *
	 * @return int
	 */
	private static function get_current_max_age() {
		$options = get_option( 'pantheon-cache', [] );

		// If the default_ttl option is not set, we're using the default, which is 1 week.
		if ( ! isset( $options['default_ttl'] ) ) {
			return apply_filters( 'pantheon_cache_default_max_age', WEEK_IN_SECONDS );
		}

		return apply_filters( 'pantheon_cache_default_max_age', $options['default_ttl'] );
	}
}
