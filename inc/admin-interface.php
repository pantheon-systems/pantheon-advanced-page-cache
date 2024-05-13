<?php
/**
 * Controller for the admin interface that builds on top of the Pantheon MU plugin.
 *
 * @package Pantheon_Advanced_Page_Cache
 */

namespace Pantheon_Advanced_Page_Cache\Admin_Interface;

/**
 * Kick off the important bits.
 */
function bootstrap() {
	// Check if wp_admin_notice exists. We've already noted that the plugin requires at least 6.4, so we're going to not display a notice if you didn't listen to the recommendation.
	if ( ! function_exists( 'wp_admin_notice' ) ) {
		add_filter( 'pantheon_apc_disable_admin_notices', '__return_true' );
	}

	if ( defined( 'PANTHEON_MU_PLUGIN_VERSION' ) ) {
		// Only do things here if we've got the MU plugin and it's > 1.4.0.
		if ( version_compare( PANTHEON_MU_PLUGIN_VERSION, '1.4.0', '>' ) ) {
			// Do stuff, e.g. add_action().
			add_action( 'admin_notices', __NAMESPACE__ . '\\admin_notice_maybe_recommend_higher_max_age' );
		} else {
			add_action( 'admin_notices', __NAMESPACE__ . '\\admin_notice_old_mu_plugin' );
		}
	} else {
		add_action( 'admin_notices', __NAMESPACE__ . '\\admin_notice_no_mu_plugin' );
	}

	add_filter( 'site_status_tests', __NAMESPACE__ . '\\default_cache_max_age_test' );
}

/**
 * Display an admin notice if the Pantheon MU plugin was not found.
 */
function admin_notice_no_mu_plugin() {
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
function admin_notice_old_mu_plugin() {
	$current_screen = get_current_screen();

	if ( apply_filters( 'pantheon_apc_disable_admin_notices', false ) || 'settings_page_pantheon-cache' !== $current_screen->id ) {
		return;
	}

	$mu_plugin_version = PANTHEON_MU_PLUGIN_VERSION;
	$message = sprintf(
		// translators: %1$s is a link to the Pantheon MU plugin, %2$s is the version of the MU plugin.
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
 * Display an admin notice if the max-age is less than a week but not equal to 600 seconds.
 */
function admin_notice_maybe_recommend_higher_max_age() {
	$current_screen = get_current_screen();

	if ( apply_filters( 'pantheon_apc_disable_admin_notices', false ) || 'settings_page_pantheon-cache' !== $current_screen->id ) {
		return;
	}

	$max_age_rank = max_age_compare();
	$current_max_age = get_current_max_age();
	if (
		$max_age_rank > 0 &&
		$current_max_age < WEEK_IN_SECONDS &&
		$current_max_age !== 600
	) {
		// If the current max-age value has a rank of 3 or more (10 is the highest), we'll note that it's very low.
		$very_low = $max_age_rank > 3 ? __( 'This is a very low value and may not be optimal for your site.', 'pantheon-advanced-page-cache' ) : '';
		$message = sprintf(
			// translators: %1$s is the current max-age, %2$d is the recommended max-age in seconds, %3$s is the humanized max-age.
			__( 'The cache max-age is currently set to %1$s (%2$s seconds). %3$s Consider increasing the cache max-age to at least %4$d seconds (%5$s).', 'pantheon-advanced-page-cache' ),
			humanized_max_age(),
			$current_max_age,
			$very_low,
			WEEK_IN_SECONDS,
			humanized_max_age( true )
		);

		wp_admin_notice(
			$message,
			[
				'type' => 'warning',
				'dismissible' => true,
			]
		);
	}
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
function get_current_max_age() {
	$options = get_option( 'pantheon-cache', [] );

	// If the default_ttl option is not set, we're using the default, which is 1 week.
	if ( ! isset( $options['default_ttl'] ) ) {
		return get_default_max_age();
	}

	return apply_filters( 'pantheon_cache_default_max_age', $options['default_ttl'] );
}

/**
 * Add a test to the Site Health page to check the cache max-age.
 *
 * @param array $tests The Site Health tests.
 * @return array
 */
function default_cache_max_age_test( $tests ) {
	$tests['direct']['pantheon_edge_cache'] = [
		'label' => __( 'Pantheon Edge Cache', 'pantheon-advanced-page-cache' ),
		'test' => __NAMESPACE__ . '\\test_cache_max_age',
	];

	return $tests;
}

/**
 * Get the humanized max-age.
 *
 * @param bool $recommended Whether to get the recommended max-age.
 *
 * @return string
 */
function humanized_max_age( $recommended = false ) {
	$time = time();
	$current_max_age = $recommended ? get_default_max_age() : get_current_max_age();
	$humanized_time = human_time_diff( $time, $time + $current_max_age );

	return $humanized_time;
}

/**
 * Get the default max-age.
 *
 * @return int
 */
function get_default_max_age() {
	return apply_filters( 'pantheon_cache_default_max_age', WEEK_IN_SECONDS );
}

/**
 * Compare the current max-age to the default max-age.
 *
 * @return int A ranked value from 0 to 10 where 0 is optimal (equal to or greater than the recommended max age) and 10 is very bad.
 */
function max_age_compare() {
	$current_max_age = get_current_max_age();
	$default_max_age = get_default_max_age();
	$diff = $current_max_age - $default_max_age;

	if ( $diff >= 0 ) {
		return 0;
	}

	// Rank the difference on a scale of 0 ($current_max_age >= $default_max_age) to 10 and return the rank int.
	$rank = round( abs( $diff ) / $default_max_age * 10 );

	return min( max( $rank, 1 ), 10 );
}

/**
 * The GCDN cache max-age Site Health test.
 *
 * @return array
 */
function test_cache_max_age() {
	$default_max_age = get_default_max_age();
	$current_max_age = get_current_max_age();
	$humanized_time = humanized_max_age();
	$humanized_reccomended_time = humanized_max_age( true );

	if ( $current_max_age < $default_max_age ) {
		$result = [
			'label' => __( 'Pantheon GCDN Cache Max-Age', 'pantheon-advanced-page-cache' ),
			'status' => 'recommended',
			'badge' => [
				'label' => __( 'Performance', 'pantheon-advanced-page-cache' ),
				'color' => 'orange',
			],
			'description' => sprintf(
				// translators: %1$s is the current max-age, %2$s is the recommended max-age, %3$d is the recommended max-age in seconds.
				__( 'The Pantheon GCDN cache max-age is currently set to %1$s (%2$d seconds). We recommend increasing to %3$s (%4$d seconds)', 'pantheon-advanced-page-cache' ),
				$humanized_time,
				$current_max_age,
				$humanized_reccomended_time,
				$default_max_age
			),
			'test' => 'pantheon_edge_cache',
		];

		return $result;
	}

	$result = [
		'label' => sprintf(
			// translators: %s is the humanized time.
			__( 'Pantheon GCDN Cache Max-Age set to %1$s', 'pantheon-advanced-page-cache' ),
			$humanized_time,
			$humanized_reccomended_time
		),
		'status' => 'good',
		'badge' => [
			'label' => __( 'Performance', 'pantheon-advanced-page-cache' ),
			'color' => 'blue',
		],
		'description' => sprintf(
			'%1$s<br />%2$s',
			sprintf(
				// translators: %1$s is the current max-age, %2$s is the recommended max-age, %3$d is the recommended max-age in seconds.
				__( 'The Pantheon cache max-age is currently set to %1$s (%2$s seconds). Our recommendation is %3$s (%4$d seconds) or more.', 'pantheon-advanced-page-cache' ),
				$humanized_time,
				$current_max_age,
				$humanized_reccomended_time,
				$default_max_age
			),
			sprintf(
				// translators: %s is a link to the cache configuration guide.
				__( 'View our <a href="%s">cache configuration guide</a> for more information.', 'pantheon-advanced-page-cache' ),
				'https://docs.pantheon.io/guides/wordpress-configurations/wordpress-cache-plugin#pantheon-page-cache-plugin-configuration'
			)
		),
		'test' => 'pantheon_edge_cache',
	];

	return $result;
}
