<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

/**
 * Manages status of user cart in smaily_abandoned_carts table.
 */
class Cart {
	/**
	 * Class initialization
	 *
	 * @return void
	 */
	public function register() {
		// Update cart status.
		add_action( 'woocommerce_cart_updated', array( $this, 'smaily_update_cart_details' ) );
		// Delete cart when customer orders.
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'smaily_checkout_delete_cart' ) );
	}

	/**
	 * Clears cart from smaily_abandoned_carts table for that user, when customer makes order.
	 */
	public function smaily_checkout_delete_cart() {
		if ( is_user_logged_in() ) {
			global $wpdb;
			$user_id    = get_current_user_id();
			$table_name = $wpdb->prefix . 'smaily_abandoned_carts';
			$wpdb->delete(
				$table_name,
				array(
					'customer_id' => $user_id,
				)
			);
		}
	}

	/**
	 * Updates smaily_abandoned_carts table with user data.
	 *
	 * @return void
	 */
	public function smaily_update_cart_details() {
		// Don't run if on admin screen.
		if ( is_admin() ) {
			return;
		}
		// Continue if user is logged in.
		if ( is_user_logged_in() ) {
			global $wpdb;
			// Customer data.
			$user_id = get_current_user_id();
			// Customer cart.
			$cart = WC()->cart->get_cart();
			// Time.
			$current_time      = gmdate( 'Y-m-d\TH:i:s\Z' );
			$cart_status       = 'open';
			$table             = $wpdb->prefix . 'smaily_abandoned_carts';
			$has_previous_cart = $this->has_previous_cart( $user_id );
			// If customer doesn't have active cart, create one.
			if ( ! $has_previous_cart ) {
				// Insert new row to table.
				if ( ! WC()->cart->is_empty() ) {
					$insert_query = $wpdb->insert(
						$table,
						array(
							'customer_id'  => $user_id,
							'cart_updated' => $current_time,
							'cart_status'  => $cart_status,
							'cart_content' => serialize( $cart ),
						)
					);
				}
			} else {
				// If customer has items update cart contents and time.
				if ( ! WC()->cart->is_empty() ) {
					$update_query = $wpdb->update(
						$table,
						array(
							'cart_updated' => $current_time,
							'cart_content' => serialize( $cart ),
							'cart_status'  => $cart_status,
						),
						array( 'customer_id' => $user_id )
					);
				} else {
					// Delete cart if empty.
					$wpdb->delete(
						$table,
						array(
							'customer_id' => $user_id,
						)
					);
				}
			}
		}
	}

	/**
	 * Check if customer has active cart in database.
	 *
	 * @param int $user_id Customer id.
	 * @return boolean
	 */
	private function has_previous_cart( $customer_id ) {
		global $wpdb;
		// Get row with user id.
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}smaily_abandoned_carts WHERE customer_id=%d",
				$customer_id
			),
			'ARRAY_A'
		);
		if ( empty( $row ) ) {
			return false;
		} else {
			return true;
		}
	}
}
