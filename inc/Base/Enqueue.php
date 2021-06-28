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
		// Must have low priority to dequeue successfully.
		add_action( 'admin_enqueue_scripts', array( $this, 'dequeue_admin_styles' ), 100 );
	}

	/**
	 * Enqueue .css and .js for admin
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {

		wp_register_script(
			'smaily_for_woocommerce-admin_settings',
			SMAILY_PLUGIN_URL . 'static/javascript.js',
			array(
				'jquery',
				'jquery-ui-tabs',
			),
			SMAILY_PLUGIN_VERSION,
			true
		);

		wp_register_script(
			'smaily_for_woocommerce-admin_widget',
			SMAILY_PLUGIN_URL . 'static/admin-widget.js',
			array(
				'jquery',
				'smaily_for_woocommerce-jscolor',
			),
			SMAILY_PLUGIN_VERSION,
			true
		);

		// Register jscolor.min.js.
		wp_register_script(
			'smaily_for_woocommerce-jscolor',
			SMAILY_PLUGIN_URL . 'static/jscolor.min.js',
			array(),
			SMAILY_PLUGIN_VERSION,
			true
		);

		// Register styles.
		wp_register_style(
			'smaily_for_woocommerce-admin_settings',
			SMAILY_PLUGIN_URL . 'static/admin-style.css',
			array(),
			SMAILY_PLUGIN_VERSION
		);
		wp_register_style(
			'smaily_for_woocommerce-admin_widget',
			SMAILY_PLUGIN_URL . 'static/admin-widget-style.css',
			array(),
			SMAILY_PLUGIN_VERSION
		);

		// Enqueue scripts.
		wp_enqueue_script( 'smaily_for_woocommerce-jscolor' );
		wp_enqueue_script( 'smaily_for_woocommerce-admin_settings' );
		wp_enqueue_script( 'smaily_for_woocommerce-admin_widget' );

		// Enqueue styles.
		wp_enqueue_style( 'smaily_for_woocommerce-admin_settings' );
		wp_enqueue_style( 'smaily_for_woocommerce-admin_widget' );

		$translations = array(
			'went_wrong' => __( 'Something went wrong connecting to Smaily!', 'smaily' ),
			'validated'  => __( 'Smaily credentials successfully validated!', 'smaily' ),
			'data_error' => __( 'Something went wrong with saving data!', 'smaily' ),
		);
		// Translations for frontend JS.
		wp_localize_script(
			'smaily_for_woocommerce-admin_settings',
			'smaily_translations',
			$translations
		);

		// Settings for frontend JS.
		$default_settings = array();
		$settings         = apply_filters( 'smaily_settings', $default_settings );
		wp_add_inline_script(
			'smaily_for_woocommerce-inline',
			'var smaily_settings = ' . wp_json_encode( $settings ) . ';'
		);
	}

	/**
	 * Enqueue .css and .js for front-end
	 *
	 * @return void
	 */
	public function enqueue_front_scripts() {
		// Register style
		wp_register_style(
			'smaily_for_woocommerce-front_style',
			SMAILY_PLUGIN_URL . 'static/front-style.css',
			array(),
			SMAILY_PLUGIN_VERSION
		);
		// Enqueue CSS.
		wp_enqueue_style( 'smaily_for_woocommerce-front_style' );
	}

	/**
	 * Dequeues all 3rd party styles on Smaily module settings page.
	 * Note! This function can be removed once we decide to rework tabs to something other than jQuery UI
	 *
	 * @return void
	 */
	public function dequeue_admin_styles() {

		$screen = get_current_screen();
		if ( ! isset( $screen->base ) || $screen->base !== 'toplevel_page_smaily-settings' ) {
			return;
		}

		$plugins_dir_url = content_url( 'plugins' );
		$wp_styles       = wp_styles();
		foreach ( $wp_styles->queue as $style_handle ) {
			if ( strpos( $style_handle, 'smaily_for_woocommerce' ) === 0 ) {
				continue;
			}
			$style_src_path = $wp_styles->registered[ $style_handle ]->src;

			if ( strpos( $style_src_path, $plugins_dir_url ) === 0 ) {
				wp_dequeue_style( $style_handle );
			}
		}
	}

}
