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

require_once dirname( __FILE__ ) . '/inc/class-emitter.php';

add_filter( 'wp', array( 'Pantheon_Integrated_CDN\Emitter', 'action_wp' ) );
