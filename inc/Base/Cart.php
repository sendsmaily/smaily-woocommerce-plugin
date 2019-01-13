<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

/**
 * Update Cart with timestamp and prevent net Crawlers from updating cart time value.
 */
class Cart {
	/**
	 * Class initialization
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'woocommerce_add_to_cart', array( $this, 'smaily_update_cart_time' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'smaily_checkout_update_mail' ) ); // Checkout newsletter checkbox.
	}

	/**
	 * Updates time created value in woocommerce session table when cart item is added.
	 *
	 * @return void
	 */
	public function smaily_update_cart_time() {
		// Don't create a record unless a user has something in their cart.
		if ( ! WC()->cart->cart_contents ) {
			return;
		}
		// Get session.
		$session = WC()->session;

		// Determine if user is quest or customer.
		if ( is_user_logged_in() ) {
			$customer_id = get_current_user_id();
		} else {
			$customer_id = WC()->session->get_customer_id();
		}

		// TODO Implement crawler.
		/*
		$crawler_detect = new CrawlerDetect();
		if ($crawler_detect->isCrawler()) {
			return;
		}
		*/

		// Update database with time value.
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'woocommerce_sessions',
			array(
				'time_created' => date( 'Y-m-d H:i:s' ),
			),
			array(
				'session_key' => $customer_id,
			)
		);
	}

	/**
	 * Update mail_sent status in database when abandoned cart email has been sent to customer.
	 *
	 * @param string $session_key Customer session key.
	 */
	public static function set_mail_sent_status( string $session_key ) {
		// Update database with time value.
		global $wpdb;
		$wpdb->update(
			$wpdb->prefix . 'woocommerce_sessions',
			array(
				'mail_sent' => '1',
			),
			array(
				'session_key' => $session_key,
			)
		);
	}

	/**
	 * Sets mail_sent value to 0 if customer checks out with their cart.
	 */
	public function smaily_checkout_update_mail() {
		$wc = WC();
		if ( $wc->session->has_session() ) {
			$session_key = $wc->session->generate_customer_id();
			// Update database mail_sent value to 0.
			global $wpdb;
			$wpdb->update(
				$wpdb->prefix . 'woocommerce_sessions',
				array(
					'mail_sent' => '0',
				),
				array(
					'session_key' => $session_key,
				)
			);
		}
	}
}
