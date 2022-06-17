<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Api;

use Smaily_Inc\Base\DataHandler;

/**
 * Handles communication between Smaily API and WordPress
 */
class Api {

	/**
	 * Startup function to register Ajax call hooks
	 *
	 * @return void
	 */
	public function register() {

		// Ajax call handlers to save Smaily autoresponder info to database.
		add_action( 'wp_ajax_update_api_database', array( $this, 'save_api_information' ) );
		add_action( 'wp_ajax_nopriv_update_api_database', array( $this, 'save_api_information' ) );

	}

	/**
	 * Save settings to WordPress database.
	 *
	 * @return void
	 */
	public function save_api_information() {
		global $wpdb;

		// Ensure user has permissions to update API information.
		if ( ! current_user_can( 'manage_options' ) ) {
			echo wp_json_encode(
				array(
					'error' => __( 'You are not authorized to edit settings!', 'smaily' ),
				)
			);
			wp_die();
		}

		// Ensure expected form data is submitted.
		if ( ! isset( $_POST['payload'] ) ) {
			echo wp_json_encode(
				array(
					'error' => __( 'Missing form data!', 'smaily' ),
				)
			);
			wp_die();
		}

		// Parse posted form data.
		$payload = array();
		parse_str( $_POST['payload'], $payload );

		// Ensure nonce is valid.
		$nonce = isset( $payload['nonce'] ) ? $payload['nonce'] : '';
		if ( ! wp_verify_nonce( sanitize_key( $nonce ), 'smaily-settings-nonce' ) ) {
			echo wp_json_encode(
				array(
					'error' => __( 'Nonce verification failed!', 'smaily' ),
				)
			);
			wp_die();
		}

		// Collect and normalize form data.
		$abandoned_cart    = $this->collect_abandoned_cart_data( $payload );
		$api_credentials   = $this->collect_api_credentials_data( $payload );
		$checkout_checkbox = $this->collect_checkout_checkbox_data( $payload );
		$customer_sync     = $this->collect_customer_sync_data( $payload );
		$rss               = $this->collect_rss_data( $payload );

		// Validate abandoned cart data.
		if ( $abandoned_cart['enabled'] === true ) {
			// Ensure abandoned cart autoresponder is selected.
			if ( empty( $abandoned_cart['autoresponder'] ) ) {
				echo wp_json_encode(
					array(
						'error' => __( 'Select autoresponder for abandoned cart!', 'smaily' ),
					)
				);
				wp_die();
			}

			// Ensure abandoned cart delay is valid.
			if ( $abandoned_cart['delay'] < 10 ) {
				echo wp_json_encode(
					array(
						'error' => __( 'Abandoned cart cutoff time value must be 10 or higher!', 'smaily' ),
					)
				);
				wp_die();
			}
		}

		// Validate API credentials data.
		if ( $api_credentials['subdomain'] === '' ) {
			echo wp_json_encode(
				array(
					'error' => __( 'Please enter subdomain!', 'smaily' ),
				)
			);
			wp_die();
		} elseif ( $api_credentials['username'] === '' ) {
			echo wp_json_encode(
				array(
					'error' => __( 'Please enter username!', 'smaily' ),
				)
			);
			wp_die();
		} elseif ( $api_credentials['password'] === '' ) {
			echo wp_json_encode(
				array(
					'error' => __( 'Please enter password!', 'smaily' ),
				)
			);
			wp_die();
		}

		// Verify API credentials actually work.
		$api_call = wp_remote_get(
			'https://' . $api_credentials['subdomain'] . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted',
			array(
				'headers'    => array(
					'Authorization' => 'Basic ' . base64_encode( $api_credentials['username'] . ':' . $api_credentials['password'] ),
				),
				'user-agent' => $this->get_user_agent(),
			)
		);

		// Handle Smaily API response.
		$http_code = wp_remote_retrieve_response_code( $api_call );
		if ( $http_code === 401 ) {
			echo wp_json_encode(
				array(
					'error' => __( 'Invalid API credentials, no connection!', 'smaily' ),
				)
			);
			wp_die();
		} elseif ( $http_code === 404 ) {
			echo wp_json_encode(
				array(
					'error' => __( 'Invalid subdomain, no connection!', 'smaily' ),
				)
			);
			wp_die();
		} elseif ( is_wp_error( $api_call ) ) {
			echo wp_json_encode( array( 'error' => $api_call->get_error_message() ) );
			wp_die();
		}

		// Validate RSS form data.
		if ( $rss['limit'] > 250 || $rss['limit'] < 1 ) {
			echo wp_json_encode(
				array(
					'error' => __( 'RSS product limit value must be between 1 and 250!', 'smaily' ),
				)
			);
			wp_die();
		}

		// Compile settings update values.
		$update_values = array(
			'enable'                => (int) $customer_sync['enabled'],
			'subdomain'             => $api_credentials['subdomain'],
			'username'              => $api_credentials['username'],
			'password'              => $api_credentials['password'],
			'syncronize_additional' => ! empty( $customer_sync['fields'] ) ? implode( ',', $customer_sync['fields'] ) : null,
			'enable_cart'           => (int) $abandoned_cart['enabled'],
			'enable_checkbox'       => (int) $checkout_checkbox['enabled'],
			'checkbox_auto_checked' => (int) $checkout_checkbox['auto_check'],
			'checkbox_order'        => $checkout_checkbox['position'],
			'checkbox_location'     => $checkout_checkbox['location'],
			'rss_category'          => $rss['category'],
			'rss_limit'             => $rss['limit'],
			'rss_order_by'          => $rss['sort_field'],
			'rss_order'             => $rss['sort_order'],
		);

		if ( $abandoned_cart['enabled'] === true ) {
			$update_values = array_merge(
				$update_values,
				array(
					'cart_autoresponder'    => '',
					'cart_autoresponder_id' => $abandoned_cart['autoresponder'],
					'cart_cutoff'           => $abandoned_cart['delay'],
					'cart_options'          => ! empty( $abandoned_cart['fields'] ) ? implode( ',', $abandoned_cart['fields'] ) : null,
				)
			);
		}

		$result = $wpdb->update(
			$wpdb->prefix . 'smaily',
			$update_values,
			array( 'id' => 1 )
		);

		if ( $result === false ) {
			echo wp_json_encode(
				array(
					'error' => __( 'Something went wrong saving settings!', 'smaily' ),
				)
			);
			wp_die();
		}

		$response = array();
		$body     = json_decode( wp_remote_retrieve_body( $api_call ), true );
		foreach ( $body as $autoresponder ) {
			array_push(
				$response,
				array(
					'name' => $autoresponder['title'],
					'id'   => (int) $autoresponder['id'],
				)
			);
		}

		echo wp_json_encode( $response );
		wp_die();
	}

	// TODO: This method should not manipulate data but only pass received results.
	// Let calling functions determine how to implement error handling.
	/**
	 * Api call to Smaily with different parameters
	 *
	 * @param string $endpoint  Api endpont without .php.
	 * @param string $params    Additional params to attatch to ApiCall for workflows.
	 * @param array  $data      Data to send to Smaily. Authentication info is received from Smaily settings.
	 * @param string $method    GET or POST method.
	 * @return array $response  Response from Smaily API
	 */
	public static function ApiCall( $endpoint, $params = '', array $data = array(), $method = 'GET' ) {
		// Response.
		$response = array();
		// Smaily settings from database.
		$db_user_info = DataHandler::get_smaily_results();
		$result       = $db_user_info['result'];

		// Add authorization to data of request.
		$data = array_merge( $data, array( 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $result['username'] . ':' . $result['password'] ) ) ) );

		// Add User-Agent string to data of request.
		$data = array_merge( $data, array( 'user-agent' => self::get_user_agent() ) );

		// API call with GET request.
		if ( $method === 'GET' ) {
			$api_call = wp_remote_get( 'https://' . $result['subdomain'] . '.sendsmaily.net/api/' . $endpoint . '.php' . $params, $data );

			// Response code from Smaily API.
			$http_code = wp_remote_retrieve_response_code( $api_call );

			// If Method POST.
		} elseif ( $method === 'POST' ) {
			// Add authorization to data of request.
			$api_call = wp_remote_post( 'https://' . $result['subdomain'] . '.sendsmaily.net/api/' . $endpoint . '.php' . $params, $data );
			// Response code from Smaily API.
			$http_code = wp_remote_retrieve_response_code( $api_call );

		}

		// Return error message.
		if ( $http_code !== 200 ) {
			return array(
				'error' => __( 'Check details, no connection!', 'smaily' ),
			);
		}

		$body     = json_decode( wp_remote_retrieve_body( $api_call ), true );
		$response = $body;

		// Response from API call.
		return $response;
	}

	/**
	 * Collect and normalize API credentials data.
	 *
	 * @param array $payload
	 * @return array
	 */
	protected function collect_api_credentials_data( array $payload ) {
		$api_credentials = array(
			'password'  => '',
			'subdomain' => '',
			'username'  => '',
		);

		if ( isset( $payload['api'] ) and is_array( $payload['api'] ) ) {
			$raw_api_credentials = $payload['api'];

			foreach ( $api_credentials as $key => $default ) {
				$api_credentials[ $key ] = isset( $raw_api_credentials[ $key ] ) ? wp_unslash( sanitize_text_field( $raw_api_credentials[ $key ] ) ) : $default;
			}

			// Normalize subdomain.
			// First, try to parse as full URL. If that fails, try to parse as subdomain.sendsmaily.net, and
			// if all else fails, then clean up subdomain and pass as is.
			if ( filter_var( $api_credentials['subdomain'], FILTER_VALIDATE_URL ) ) {
				$url                          = wp_parse_url( $api_credentials['subdomain'] );
				$parts                        = explode( '.', $url['host'] );
				$api_credentials['subdomain'] = count( $parts ) >= 3 ? $parts[0] : '';
			} elseif ( preg_match( '/^[^\.]+\.sendsmaily\.net$/', $api_credentials['subdomain'] ) ) {
				$parts                        = explode( '.', $api_credentials['subdomain'] );
				$api_credentials['subdomain'] = $parts[0];
			}

			$api_credentials['subdomain'] = preg_replace( '/[^a-zA-Z0-9]+/', '', $api_credentials['subdomain'] );

		}

		return $api_credentials;
	}

	/**
	 * Collect and normalize customer synchronization data.
	 *
	 * @param array $payload
	 * @return array
	 */
	protected function collect_customer_sync_data( array $payload ) {
		$customer_sync = array(
			'enabled' => false,
			'fields'  => array(),
		);

		if ( isset( $payload['customer_sync'] ) and is_array( $payload['customer_sync'] ) ) {
			$raw_customer_sync = $payload['customer_sync'];
			$allowed_fields    = array(
				'customer_group',
				'customer_id',
				'first_name',
				'first_registered',
				'last_name',
				'nickname',
				'site_title',
				'user_dob',
				'user_gender',
				'user_phone',
			);

			$customer_sync['enabled'] = isset( $raw_customer_sync['enabled'] ) ? (bool) (int) $raw_customer_sync['enabled'] : $customer_sync['enabled'];
			$customer_sync['fields']  = isset( $raw_customer_sync['fields'] ) ? array_values( (array) $raw_customer_sync['fields'] ) : $customer_sync['fields'];

			// Ensure only allowed fields are selected.
			$customer_sync['fields'] = array_values( array_intersect( $customer_sync['fields'], $allowed_fields ) );
		}

		return $customer_sync;
	}

	/**
	 * Collect and normalize abandoned cart data.
	 *
	 * @param array $payload
	 * @return array
	 */
	protected function collect_abandoned_cart_data( array $payload ) {
		$abandoned_cart = array(
			'autoresponder' => 0,
			'delay'         => 10,  // In minutes.
			'enabled'       => false,
			'fields'        => array(),
		);

		if ( isset( $payload['abandoned_cart'] ) and is_array( $payload['abandoned_cart'] ) ) {
			$raw_abandoned_cart = $payload['abandoned_cart'];
			$allowed_fields     = array(
				'first_name',
				'last_name',
				'product_base_price',
				'product_description',
				'product_name',
				'product_price',
				'product_quantity',
				'product_sku',
			);

			$abandoned_cart['autoresponder'] = isset( $raw_abandoned_cart['autoresponder'] ) ? (int) $raw_abandoned_cart['autoresponder'] : $abandoned_cart['autoresponder'];
			$abandoned_cart['delay']         = isset( $raw_abandoned_cart['delay'] ) ? (int) $raw_abandoned_cart['delay'] : $abandoned_cart['delay'];
			$abandoned_cart['enabled']       = isset( $raw_abandoned_cart['enabled'] ) ? (bool) (int) $raw_abandoned_cart['enabled'] : $abandoned_cart['enabled'];
			$abandoned_cart['fields']        = isset( $raw_abandoned_cart['fields'] ) ? array_values( (array) $raw_abandoned_cart['fields'] ) : $abandoned_cart['fields'];

			// Ensure only allowed fields are selected.
			$abandoned_cart['fields'] = array_values( array_intersect( $abandoned_cart['fields'], $allowed_fields ) );
		}

		return $abandoned_cart;
	}

	/**
	 * Collect and normalize checkout checkbox data.
	 *
	 * @param array $payload
	 * @return array
	 */
	protected function collect_checkout_checkbox_data( array $payload ) {
		$checkout_checkbox = array(
			'auto_check' => false,
			'enabled'    => false,
			'location'   => 'checkout_billing_form',
			'position'   => 'after',
		);

		if ( isset( $payload['checkout_checkbox'] ) and is_array( $payload['checkout_checkbox'] ) ) {
			$raw_checkout_checkbox = $payload['checkout_checkbox'];
			$allowed_locations     = array(
				'checkout_billing_form',
				'checkout_registration_form',
				'checkout_shipping_form',
				'order_notes',
			);
			$allowed_positions     = array(
				'before',
				'after',
			);

			$checkout_checkbox['auto_check'] = isset( $raw_checkout_checkbox['auto_check'] ) ? (bool) (int) $raw_checkout_checkbox['auto_check'] : $checkout_checkbox['auto_check'];
			$checkout_checkbox['enabled']    = isset( $raw_checkout_checkbox['enabled'] ) ? (bool) (int) $raw_checkout_checkbox['enabled'] : $checkout_checkbox['enabled'];
			$checkout_checkbox['location']   = isset( $raw_checkout_checkbox['location'] ) ? wp_unslash( sanitize_text_field( $raw_checkout_checkbox['location'] ) ) : $checkout_checkbox['location'];
			$checkout_checkbox['position']   = isset( $raw_checkout_checkbox['position'] ) ? wp_unslash( sanitize_text_field( $raw_checkout_checkbox['position'] ) ) : $checkout_checkbox['position'];

			// Ensure only an allowed location is selected.
			if ( ! in_array( $checkout_checkbox['location'], $allowed_locations, true ) ) {
				$checkout_checkbox['location'] = 'checkout_billing_form';
			}

			// Ensure only an allowed position is selected.
			if ( ! in_array( $checkout_checkbox['position'], $allowed_positions, true ) ) {
				$checkout_checkbox['position'] = 'after';
			}
		}

		return $checkout_checkbox;
	}

	/**
	 * Collect and normalize RSS data.
	 *
	 * @param array $payload
	 * @return array
	 */
	protected function collect_rss_data( array $payload ) {
		$rss = array(
			'category'   => '',
			'limit'      => 50,
			'sort_field' => 'modified',
			'sort_order' => 'DESC',
		);

		if ( isset( $payload['rss'] ) and is_array( $payload['rss'] ) ) {
			$raw_rss             = $payload['rss'];
			$allowed_sort_fields = array(
				'date',
				'id',
				'modified',
				'name',
				'rand',
				'type',
			);
			$allowed_sort_orders = array(
				'ASC',
				'DESC',
			);

			$rss['category']   = isset( $raw_rss['category'] ) ? wp_unslash( sanitize_text_field( $raw_rss['category'] ) ) : $rss['category'];
			$rss['limit']      = isset( $raw_rss['limit'] ) ? (int) $raw_rss['limit'] : $rss['limit'];
			$rss['sort_field'] = isset( $raw_rss['sort_field'] ) ? wp_unslash( sanitize_text_field( $raw_rss['sort_field'] ) ) : $rss['sort_field'];
			$rss['sort_order'] = isset( $raw_rss['sort_order'] ) ? wp_unslash( sanitize_text_field( $raw_rss['sort_order'] ) ) : $rss['sort_order'];

			// Ensure only an allowed sort field is selected.
			if ( ! in_array( $rss['sort_field'], $allowed_sort_fields, true ) ) {
				$rss['sort_field'] = 'modified';
			}

			// Ensure only an allowed sort order is selected.
			if ( ! in_array( $rss['sort_order'], $allowed_sort_orders, true ) ) {
				$rss['sort_order'] = 'DESC';
			}
		}

		return $rss;
	}

	/**
	 * Compile User-Agent header value for API requests.
	 *
	 * @return string
	 */
	protected static function get_user_agent() {
		return 'smaily-for-woocommerce/' . SMAILY_PLUGIN_VERSION . ' (WordPress/' . get_bloginfo( 'version' ) . '; WooCommerce/' . WC_VERSION . '; +' . get_bloginfo( 'url' ) . ')';
	}
}
