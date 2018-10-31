<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

/**
 * Imoprts all stylesheets and javascript files to plugin
 */
class Enqueue {

	/**
	 * Register hook for importing css and javascript
	 *
	 * @return void
	 */
	public function register() {
		// add javascript and css files to plugin.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_scripts' ) );

	}

	/**
	 * Enqueue .css and .js for admin
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {
		// enque css and js.
		wp_enqueue_script( 'mypluginscript', SMAILY_PLUGIN_URL . 'assets/javascript.js', array( 'jquery' ), '1.0.0', true );
		wp_enqueue_style( 'mypluginstyle', SMAILY_PLUGIN_URL . 'assets/admin-style.css', array(), '1.0.0' );
	}

	/**
	 * Enqueue .css and .js for front-end
	 *
	 * @return void
	 */
	public function enqueue_front_scripts() {
		// enque css and js.
		wp_enqueue_style( 'mypluginstyle', SMAILY_PLUGIN_URL . 'assets/front-style.css', array(), '1.0.0' );

	}



}
