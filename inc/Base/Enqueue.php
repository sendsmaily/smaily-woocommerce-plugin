<?php
/**
 * @package smaily_woocommerce_plugin
 */
namespace Inc\Base;

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
		// add javascript and css files to plugin
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_front_scripts' ) );

	}

	/**
	 * Enqueue .css and .js for admin
	 *
	 * @return void
	 */
	function enqueue_admin_scripts() {
		// enque css and js
		wp_enqueue_script( 'mypluginscript', PLUGIN_URL . 'assets/javascript.js', array( 'jquery' ) );
		wp_enqueue_style( 'mypluginstyle', PLUGIN_URL . 'assets/admin-style.css' );

	}

	 /**
	  * Enqueue .css and .js for front-end
	  *
	  * @return void
	  */
	function enqueue_front_scripts() {
		// enque css and js
		wp_enqueue_style( 'mypluginstyle', PLUGIN_URL . 'assets/front-style.css' );

	}



}
