<?php
/**
 * @package smaily_woocommerce_plugin
 */
namespace Inc\Pages;

/**
 * Controlls Admin panel items
 */
class Admin {

	/**
	 * Adds Smaily plugin menu to WooCommerce submenu
	 */
	public function register() {
		// add Smaily menu to WooCommerce submenu
		add_action( 'admin_menu', array( $this, 'smaily_menu' ) );

	}

	/**
	 * Add admin page for Smaily plugin
	 *
	 * @return void
	 */
	public function smaily_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'smaily', 'woocommerce' ),
			__( 'Smaily email marketing and automation', 'woocommerce' ),
			'manage_options',
			'smaily-settings',
			array( $this, 'smaily_page' )
		);
	}

	/**
	 * Template for Smaily admin page
	 *
	 * @return void
	 */
	public function smaily_page() {
		require_once PLUGIN_PATH . '/templates/smaily-woocommerce-admin.phtml';
	}

}
