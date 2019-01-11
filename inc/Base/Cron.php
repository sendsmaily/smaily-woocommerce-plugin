<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

use Smaily_Inc\Api\Api;
use Smaily_Inc\Base\DataHandler;
use Smaily_Inc\Base\Cart;
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
		add_action( 'smaily_cron_sync_contacts', array( $this, 'smaily_sync_contacts' ) );
		add_action( 'smaily_cron_abandoned_carts', array( $this, 'smaily_abandoned_carts' ) );

	}

	/**
	 * Synchronizes contact information between Smaily and WooCommerce.
	 * Logs response from Smaily to smaily-cron file.
	 *
	 * @return void
	 */
	public function smaily_sync_contacts() {

		$results = DataHandler::get_smaily_results();
		// Check if contact sync is enabled.
		if ( (int) $results['result']['enable'] === 1 ) {

				// List value 2  = unsubscribers list.
			$data = array(
				'list' => 2,
			);

			// Make API call to Smaily to get unsubscribers.
			$unsubscribers = Api::ApiCall( 'contact', [ 'body' => $data ] );
			// List of unsubscribed emails.
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
				$this->log_to_file( SMAILY_PLUGIN_PATH . 'smaily-cron.txt', 'No subscribers' );
				return;
			}
			// Prepare data to update Subscribers list in Smaily.
			$list = [];

			foreach ( $users as $user ) {
				$subscriber = DataHandler::get_user_data( $user->ID );
				array_push( $list, $subscriber );
			}

			// Update all subscribers to Smaily.
			$response = Api::ApiCall( 'contact', [ 'body' => $list ], 'POST' );

			$this->log_to_file( SMAILY_PLUGIN_PATH . 'smaily-cron.txt', $response['message'] );
		}

	}

	/**
	 * Abandoned carts synchronization to Smaily API
	 *
	 * @return void
	 */
	public function smaily_abandoned_carts() {
		// WordPress Database handler.
		global $wpdb;
		// Get Smaily settings.
		$results = DataHandler::get_smaily_results();
		// Check if contact sync is enabled.
		if ( (int) $results['result']['enable_cart'] === 1 ) {
			// Get delay time.
			$delay = (int) $results['result']['cart_delay'];
			// Get all saved woocommerce sessions.
			$customer_session = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_sessions WHERE mail_sent='0' AND time_created IS NOT NULL", 'ARRAY_A' );
			foreach ( $customer_session as $session ) {
				// Get session details and cart data from session.
				$session_value = unserialize( $session['session_value'] );
				$cart_data     = unserialize( $session_value['cart'] );

				// Continue with sending data to Smaily if there are items in customer cart.
				if ( empty( $cart_data ) ) {
					continue;
				}

				// Continue with data gathering only if there is an email value to send data to.
				$customer = unserialize( $session_value['customer'] );
				if ( empty( $customer['email'] ) ) {
					continue;
				}
				// Check if delay time has passed from cart update.
				$cart_updated_time = strtotime( $session['time_created'] );
				$reminder_time     = strtotime( '+' . $delay . ' hours', $cart_updated_time);
				$current_time      = strtotime( date( 'Y-m-d H:i' ) . ':00');

				if ( $current_time >= $reminder_time ) {
					// Data to send to smail API.
					$addresses = [];

					// Gather customer data.
					$customer_data = [];
					$sync_values   = [ 'first_name', 'last_name', 'email' ];
					foreach ( $sync_values as $sync_value ) {
						if ( isset( $customer[ $sync_value ] ) ) {
							$addresses[ $sync_value ] = $customer[ $sync_value ];
						}
					}

					// Get cart page url.
					$cart_page_id          = wc_get_page_id( 'cart' );
					$cart_page_url         = $cart_page_id ? get_permalink( $cart_page_id ) : '';
					$addresses['cart_url'] = $cart_page_url;

					// Gather products data.
					$products_data = [];
					foreach ( $cart_data as $cart_item ) {
						$product                      = [];
						$details                      = wc_get_product( $cart_item['product_id'] ); // Get product by ID.
						$product['name']              = htmlspecialchars( $details->get_name() );
						$product['description']       = htmlspecialchars( $details->get_description() );
						$product['sku']               = htmlspecialchars( $details->get_sku() );
						$product['description_short'] = htmlspecialchars( $details->get_short_description() );
						$product['quantity']          = htmlspecialchars( $cart_item['quantity'] );
						$product['subtotal']          = htmlspecialchars( $cart_item['line_subtotal'] );
						$products_data[]              = $product;
					}

					// Append products array to API api call. Up to 10 product details.
					$i = 1;
					foreach ( $products_data as $product ) {
						if ( $i <= 10 ) {
							foreach ( $product as $key => $value) {
								$addresses[ 'product_' . $key . '_' . $i ] = htmlspecialchars( $value );
							}
							$i++;
						}
					}

					$query = [
						'autoresponder' => $results['result']['cart_autoresponder_id'], // autoresponder ID.
						'addresses'     => [ $addresses ],
					];

					// Send data to Smaily.
					$response = API::ApiCall( 'autoresponder', [ 'body' => $query ], 'POST' );

					// If data sent successfully update mail_sent status in database.
					if ( array_key_exists( 'code', $response ) && $response['code'] === 101 ) {
						// Add sent status to cart data.
						Cart::set_mail_sent_status( $session['session_key'] );
					} else {
						// Log to file if errors.
						$this->log_to_file( SMAILY_PLUGIN_PATH . 'smaily-cart.txt', wp_json_encode( $response ) );
					}
				}
			}
		}
	}

	/**
	 * Log API response to text-file.
	 *
	 * @param string $filename  Name of the file created.
	 * @param string $msg       Text response from api.
	 * @return void
	 */
	private function log_to_file( $filename, $msg ) {
		$fd  = fopen( $filename, 'a' );
		$str = '[' . current_time( 'mysql', 1 ) . '] ' . $msg;
		fwrite( $fd, $str . "\r\n" );
		fclose( $fd );
	}
}
