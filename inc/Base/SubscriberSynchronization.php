<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

use Smaily_Inc\Api\Api;
/**
 * Newsletter subscriber sync with Smaily contacts
 */

use Smaily_Inc\Base\DataHandler;

/**
 * Send subscriber to Smaily mailing list when user updates profile
 */
class SubscriberSynchronization {
	/**
	 * Class initialization
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'woocommerce_created_customer', array( $this, 'smaily_newsletter_subscribe_update' ) ); // register/checkout.
		add_action( 'personal_options_update', array( $this, 'smaily_newsletter_subscribe_update' ) ); // edit own account admin.
		add_action( 'edit_user_profile_update', array( $this, 'smaily_newsletter_subscribe_update' ) ); // edit other account admin.
		add_action( 'woocommerce_save_account_details', array( $this, 'smaily_newsletter_subscribe_update' ) ); // edit WC account.
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'smaily_checkout_subscribe_customer' ) ); // Checkout newsletter checkbox.
	}
	/**
	 * Make Api call with subscriber data when updating settings.
	 *
	 * @param [type] $user_id Id of the user being updated.
	 * @return void
	 */
	public function smaily_newsletter_subscribe_update( $user_id ) {

		// Make API call for user transfer only if user is subscribed.
		if ( ! isset( $_POST['user_newsletter'] ) ) {
			return;
		}

		// Get user data from WordPress, WooCommerce and Custom fields.
		$data = DataHandler::get_user_data( $user_id );

		// Make API call to Smaily for subscriber update.
		Api::ApiCall( 'contact', '', [ 'body' => $data ], 'POST' );
		// Subscribed to newsletter.
	}

	/**
	 * Subscribes customer in checkout form when subscribe newsletter box is checked.
	 *
	 * @param int $order_id Order ID
	 * @return void
	 */
	public function smaily_checkout_subscribe_customer( $order_id ) {

		if ( ! isset( $_POST['user_newsletter'] ) ) {
			return;
		}

		// Data to sent to Smaily API.
		$data = [];

		// Ensure subscriber's unsubscribed status is reset.
		// Note! We are using 'user_newsletter' property value just a precaution to cover
		// cases where site provides a default value for the field.
		$data['is_unsubscribed'] = (int) $_POST['user_newsletter'] === 1 ? 0 : 1;

		// Add store url for refrence in Smaily database.
		$data['store'] = get_site_url();

		// Language code if using WPML.
		$lang = '';
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$lang = ICL_LANGUAGE_CODE;
			// Language code if using polylang.
		} elseif ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();
		} else {
			$lang = get_locale();
			if ( strlen( $lang ) > 0 ) {
				// Remove any value past underscore if exists.
				$lang = explode( '_', $lang )[0];
			}
		}
		// Add language code.
		$data['language'] = $lang;

		// Append fields to data array when available.
		// Add first name.
		if ( isset( $_POST['billing_first_name'] ) ) {
			$data['first_name'] = sanitize_text_field( wp_unslash( $_POST['billing_first_name'] ) );
		}
		// Add last name.
		if ( isset( $_POST['billing_last_name'] ) ) {
			$data['last_name'] = sanitize_text_field( wp_unslash( $_POST['billing_last_name'] ) );
		}
		// Add email.
		if ( isset( $_POST['billing_email'] ) ) {
			$data['email'] = sanitize_text_field( wp_unslash( $_POST['billing_email'] ) );
		}

		// Make API call  to Smaily for subscriber update.
		if ( isset( $data['email'] ) ) {
			Api::ApiCall( 'contact', '', [ 'body' => $data ], 'POST' );
		}
		// Subscribed to newsletter.
	}
}
