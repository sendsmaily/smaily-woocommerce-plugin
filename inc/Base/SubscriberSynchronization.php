<?php
/**
 * @package smaily_woocommerce_plugin
 */

namespace Inc\Base;

use Inc\Api\Api;
 /**
 * Newsletter subscriber sync with Smaily contacts
 */

use Inc\Base\DataHandler;

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
		Api::ApiCall( 'contact', [ 'body' => $data ], 'POST' );
		// Subscribed to newsletter.
	}

}
