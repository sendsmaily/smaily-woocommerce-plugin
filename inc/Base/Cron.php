<?php
/**
 * @package smaily_woocommerce_plugin
 */
namespace Inc\Base;

use Inc\Api\Api;
use Inc\Base\DataHandler;
/**
 * Data syncronization between Smaily and WooCommerce
 */
class Cron {
	/**
	 * Cron initialization.
	 *
	 * @return void
	 */
	public function register() {
		// Action hook for contact syncronization.
		add_action( 'smaily_chron_event', array( $this, 'smaily_sync_contacts' ) );

	}

	public function smaily_sync_contacts() {

		$results = DataHandler::get_smaily_results();
		// Check if contact sync is enabled.
		if ( $results['result']['enable'] == 'on' ) {

				// List value 2  = unsubscribers list.
			$data = array(
				'list' => 2,
			);

			// Make API call to Smaily to get unsubscribers.
			$unsubscribers = Api::ApiCall( 'contact', [ 'body' => $data ] );
			// Subscribed to newsletter.
			$unsubscribers_emails = [];
			foreach ( $unsubscribers as $key => $value ) {
				array_push( $unsubscribers_emails, $value['email'] );
			}

			// Change WooCommerce subscriber status based on Smaily unsubscribers.
			foreach ( $unsubscribers_emails as $user_email ) {

				// get user by email from unsubscribers list.
				$wordpress_unsubscriber = get_user_by( 'email', $user_email );
				// set user subscribed status to 0.
				if ( isset( $wordpress_unsubscriber ) ) {
					update_user_meta( $wordpress_unsubscriber->ID, 'user_newsletter', 0, 1 );
				}
			}

			// Get all users with subscribed status.
			$users = get_users(
				array(
					'meta_key'   => 'user_newsletter',
					'meta_value' => 1,
				)
			);
			// If no subscribers.
			if ( empty( $users ) ) {
				$this->logToFile( PLUGIN_PATH . 'smaily-cron.txt', 'No subscribers' );
				return;
			}
			// Prepare data to update Subscribers list in Smaily.
			$list = [];

			foreach ( $users as $user ) {
				$subscriber = DataHandler::getUserData( $user->ID );
				array_push( $list, $subscriber );
			}

			// Update all subscribers to Smaily.
			$response = Api::ApiCall( 'contact', [ 'body' => $list ], 'POST' );

			$this->logToFile( PLUGIN_PATH . 'smaily-cron.txt', $response['message'] );
		}

	}
	/**
	 * Log API response to text-file.
	 *
	 * @param string $filename  Name of the file created.
	 * @param string $msg       Text response from api.
	 * @return void
	 */
	private function logToFile( $filename, $msg ) {
		$fd  = fopen( $filename, 'a' );
		$str = '[' . current_time( 'mysql', 1 ) . '] ' . $msg;
		fwrite( $fd, $str . "\r\n" );
		fclose( $fd );
	}
}
