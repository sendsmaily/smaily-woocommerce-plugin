<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

use Smaily_Inc\Base\DataHandler;

/**
 * Responsible for upgrading plugin between version changes.
 */
class Upgrade {

	/**
	 * Initialize upgrade functionality.
	 *
	 * @return void
	 */
	public function register() {
		// Add upgrade hooks.
		add_action( 'upgrader_process_complete', array( $this, 'upgrade' ), 10, 2 );
		// Add admin notices.
		add_action( 'admin_notices', array( $this, 'smaily_woocommerce_upgraded_notice' ) );
	}

	/**
	 * This function runs when WordPress completes its upgrade process.
	 * It iterates through each plugin updated to see if ours is included.
	 *
	 * @param Plugin_Upgrader $upgrader_object WordPress Plugin Upgrader instance.
	 * @param array           $options         Upgrade options array.
	 *
	 * @return void
	 */
	public function upgrade( $upgrader_object, $options ) {
		$name    = SMAILY_PLUGIN_NAME;
		$version = SMAILY_PLUGIN_VERSION;

		if ( $options['action'] == 'update' &&
			$options['type'] == 'plugin' &&
			isset( $options['plugins'] )
			) {
			foreach ( $options['plugins'] as $plugin ) {
				if ($plugin == $name ) {
					if ( version_compare( $version, '1.4.0', '=' ) ) {
						set_transient( 'smaily_woocommerce_upgrade_1_4_0_notice', 1 );
						$this->upgrade_1_4_0();
					}
					if ( version_compare( $version, '1.5.0', '=' ) ) {
						set_transient( 'smaily_woocommerce_upgrade_1_5_0_notice', 1 );
					}
				}
			}
		}
	}

	/**
	 * Upgrade script for 1.4.0 version migrations.
	 *
	 * @return void
	 */
	public function upgrade_1_4_0() {

		// Standardize abandoned cart fields.
		$results      = DataHandler::get_smaily_results();
		$cart_options = $results['cart_options'];

		// Remove product_description_short.
		$product_description_short = array_search( 'product_description_short', $cart_options, true );
		if ( $product_description_short !== false ) {
			unset( $cart_options[ $product_description_short ] );
		}

		// Switch product_subtotal to product_price.
		$product_subtotal = array_search( 'product_subtotal', $cart_options, true );
		if ( $product_subtotal !== false ) {
			unset( $cart_options[ $product_subtotal ] );
			if ( ! in_array( 'product_price', $cart_options, true ) ) {
				$cart_options[] = 'product_price';
			}
		}

		// Write settings  back to db.
		global $wpdb;
		$table_name = $wpdb->prefix . 'smaily';
		$wpdb->update(
			$table_name,
			array(
				'cart_options' => ! empty( $cart_options ) ? implode( ',', $cart_options ) : null,
			),
			array( 'id' => 1 )
		);
	}

	/**
	 * Add admin notices during upgrade process.
	 *
	 * @return void
	 */
	public function smaily_woocommerce_upgraded_notice() {

		if ( get_transient( 'smaily_woocommerce_upgrade_1_4_0_notice' ) ) {
			$message = __(
				'Smaily for Woocommerce plugin has changed abandoned cart exported fields.'
				. ' Please check your plugin settings!',
				'smaily'
			);
			echo ( '<div class="notice notice-warning"><p>' . esc_html( $message ) . '</p></div>' );
			delete_transient( 'smaily_woocommerce_upgrade_1_4_0_notice' );
		}

		if ( get_transient( 'smaily_woocommerce_upgrade_1_5_0_notice' ) ) {
			$message = __(
				'Smaily for Woocommerce plugin has changed checkout subscription checkbox behaviour.'
				. ' Please check your plugin settings!',
				'smaily'
			);
			echo ( '<div class="notice notice-warning"><p>' . esc_html( $message ) . '</p></div>' );
			delete_transient( 'smaily_woocommerce_upgrade_1_5_0_notice' );
		}
	}

}
