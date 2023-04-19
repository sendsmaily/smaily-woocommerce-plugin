<?php
/**
 * This is a plugin for WoooCommerce to handle subscribers directly
 * to your Smaily contacts and generate rss-feed of products.
 *
 * @package smaily_for_woocommerce
 * @author Smaily
 * @license GPL-3.0+
 * @link https://github.com/sendsmaily/smaily-woocommerce-plugin
 * @copyright 2018 Smaily
 *
 * @wordpress-plugin
 * Plugin Name: Smaily for WooCommerce
 * Plugin URI: https://github.com/sendsmaily/smaily-woocommerce-plugin
 * Description: Smaily email marketing and automation extension plugin for WooCommerce. Set up easy sync for your contacts, add opt-in subscription form, import products directly to your email template and send abandoned cart reminder emails.
 * Version: 1.11.2
 * License: GPL3
 * Author: Smaily
 * Author URI: https://smaily.com/
 * Text Domain: smaily
 * Domain Path: languages
 *
 * Smaily for WooCommerce is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Smaily for WooCommerce is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Smaily for WooCommerce. If not, see <http://www.gnu.org/licenses/>.
 */


// If accessed directly exit program.
defined( 'ABSPATH' ) || die( "This is a plugin you can't access directly" );

// check if autoload exists and require it and use for namespaces.
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
	require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// Define constants.
define( 'SMAILY_PLUGIN_FILE', __FILE__ );
define( 'SMAILY_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'SMAILY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SMAILY_PLUGIN_NAME', plugin_basename( __FILE__ ) );
define( 'SMAILY_PLUGIN_VERSION', '1.11.2' );

// Required to use functions is_plugin_active and deactivate_plugins.
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// Check if WooCommerce is installed and activate plugin only if possible.
if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

	// call Init class that is responsible for initiating all other classes.
	if ( ! class_exists( 'Smaily_Inc\\Init.php' ) ) {
		Smaily_Inc\Init::register_services();
	}
	// Load translations.
	add_action( 'plugins_loaded', 'smaily_for_woocommerce_load_textdomain' );

} else {
	deactivate_plugins( SMAILY_PLUGIN_NAME );
	add_action( 'admin_notices', 'smaily_plugin_admin_notices' );
	// Stop "Plugin Activated" message from appearing.
	if ( isset( $_GET['activate'] ) ) {
		unset( $_GET['activate'] );
	}
}

/**
 * Add translation folder to plugin.
 */
function smaily_for_woocommerce_load_textdomain() {
	load_plugin_textdomain( 'smaily', false, basename( dirname( __FILE__ ) ) . '/lang' );
}

/**
 * Display error message to user if WooCommerce not installed.
 *
 * @return void
 */
function smaily_plugin_admin_notices() {
	$message = __(
		'Smaily for WooCommerce is not able to activate. WooCommerce needed to function properly. Is WooCommerce installed?',
		'smaily'
	);
	echo "<div class='update-message notice inline notice-warning notice-alt'><p>" . esc_html( $message ) . '</p></div>';
}
