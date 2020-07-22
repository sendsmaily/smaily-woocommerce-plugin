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

		wp_register_script(
			'smailypluginscript',
			SMAILY_PLUGIN_URL . 'static/javascript.js',
			array(
				'jquery',
				'jquery-ui-tabs',
			),
			SMAILY_PLUGIN_VERSION,
			true
		);

		// enque css and js.
		wp_enqueue_script(
			'smailypluginscript',
			SMAILY_PLUGIN_URL . 'static/javascript.js',
			array(
				'jquery',
				'jquery-ui-tabs',
			),
			SMAILY_PLUGIN_VERSION,
			true
		);
		wp_enqueue_style(
			'smailypluginstyle',
			SMAILY_PLUGIN_URL . 'static/admin-style.css',
			array(),
			SMAILY_PLUGIN_VERSION
		);

		$translations = array(
			'went_wrong' => __( 'Something went wrong connecting to Smaily!', 'smaily' ),
			'validated'  => __( 'Smaily credentials sucessfully validated!', 'smaily' ),
			'data_error' => __( 'Something went wrong with saving data!', 'smaily' ),
		);
		// Translations for frontend JS.
		wp_localize_script(
			'smailypluginscript',
			'smaily_translations',
			$translations,
		);

		// Settings for frontend JS.
		$default_settings = array();
		$settings         = apply_filters( 'smaily_settings', $default_settings );
		wp_add_inline_script(
			'smailypluginscript',
			'var smaily_settings = ' . wp_json_encode( $settings ) . ';'
		);
	}

	/**
	 * Enqueue .css and .js for front-end
	 *
	 * @return void
	 */
	public function enqueue_front_scripts() {
		// enque css and js.
		wp_enqueue_style( 'smailypluginstyle', SMAILY_PLUGIN_URL . 'static/front-style.css', array(), SMAILY_PLUGIN_VERSION );
	}

}
