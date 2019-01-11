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

		// Ajax call handlers to validate subdomain/username/password.
		add_action( 'wp_ajax_validate_api', array( $this, 'register_api_information' ) );
		add_action( 'wp_ajax_nopriv_validate_api', array( $this, 'register_api_information' ) );

		// Ajax call handlers to save Smaily autoresponder info to database.
		add_action( 'wp_ajax_update_api_database', array( $this, 'save_api_information' ) );
		add_action( 'wp_ajax_nopriv_update_api_database', array( $this, 'save_api_information' ) );

	}

	/**
	 * Validate Smaily API autoresponder list based on user information
	 *
	 * @return void
	 */
	public function register_api_information() {
		if ( isset( $_POST['form_data'] ) && current_user_can( 'manage_options' ) ) {
			// Parse form data out of the serialization.
			$params = array();
			parse_str( $_POST['form_data'], $params ); // Ajax serialized string, sanitizing data before usage below.

			// Check for nonce-verification and sanitize user input.
			if ( wp_verify_nonce( sanitize_key( $params['nonce'] ), 'settings-nonce' ) ) {
				$sanitized = [];
				if ( is_array( $params ) ) {
					foreach ( $params as $key => $value ) {
						$sanitized[ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				// Show error messages to user if no data is entered to form.
				$response = array();
				if ( $sanitized['subdomain'] === '' ) {
					$response = array( 'error' => 'Please enter subdomain!' );
				} elseif ( $sanitized['username'] === '' ) {
					$response = array( 'error' => 'Please enter username!' );
				} elseif ( $sanitized['password'] === '' ) {
					$response = array( 'error' => 'Please enter password!' );
				} else {
					// If all fields are set make api call.
					$api_call = wp_remote_get(
						'https://' . $sanitized['subdomain'] . '.sendsmaily.net/api/autoresponder.php',
						[
							'headers' => array(
								'Authorization' => 'Basic ' . base64_encode( $sanitized['username'] . ':' . $sanitized['password'] ),
							),
						]
					);

					// Response code from Smaily API.
					$http_code = wp_remote_retrieve_response_code( $api_call );
					// Show error message if no access.
					if ( $http_code === 401 ) {
						$response = array( 'error' => 'Invalid API credentials, no connection !' );
					}
					if ( is_wp_error( $api_call ) ) {
						$response = array( 'error' => $api_call->get_error_message() );
					}
					// Return autoresponders list back to front end for selection.
					if ( $http_code === 200 ) {
						$body = json_decode( wp_remote_retrieve_body( $api_call ), true );
						foreach ( $body as $autoresponder ) {
							array_push(
								$response,
								array(
									'name' => $autoresponder['name'],
									'id'   => $autoresponder['id'],
								)
							);
						}
					}
				}

				// Return response to ajax call.
				echo wp_json_encode( $response );
				wp_die();

			}
		}
	}

	/**
	 * Save user API information to WordPress database
	 *
	 * @return void
	 */
	public function save_api_information() {

		// Receive data from Settings form.
		if (
			isset( $_POST['user_data'] )
			&& isset( $_POST['autoresponder_data'] )
			&& current_user_can( 'manage_options' )
		) {
			// Response to front-end js.
			$response = array();
			// Parse form data out of the serialization.
			$user = array();
			parse_str( $_POST['user_data'], $user ); // Ajax serialized data, sanitization below.
			$autoresponders = array();
			parse_str( $_POST['autoresponder_data'], $autoresponders ); // Ajax serialized data, sanitization below.
			$autoresponder      = json_decode( $autoresponders['autoresponder'], true );
			$cart_autoresponder = json_decode( $autoresponders['cart_autoresponder'], true);

			// Check for nonce-verification and sanitize user input.
			if ( wp_verify_nonce( sanitize_key( $user['nonce'] ), 'settings-nonce' ) ) {
				$sanitized_user                  = [];
				$sanitized_autoresponder         = [];
				$sanitized_cart_autoresponder    = [];
				$sanitized_syncronize_additional = [];
				$sanitized_cart_options          = [];
				if ( is_array( $user ) ) {
					foreach ( $user as $key => $value ) {
						$sanitized_user[ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				if ( is_array( $autoresponder ) ) {
					foreach ( $autoresponder as $key => $value ) {
						$sanitized_autoresponder[ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				if ( is_array( $cart_autoresponder ) ) {
					foreach ( $cart_autoresponder as $key => $value ) {
						$sanitized_cart_autoresponder [ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				if ( is_array( $autoresponders['syncronize_additional'] ) ) {
					foreach ( $autoresponders['syncronize_additional'] as $key => $value ) {
						$sanitized_syncronize_additional[ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				if ( is_array( $autoresponders['cart_options'] ) ) {
					foreach ( $autoresponders['cart_options'] as $key => $value) {
						$sanitized_cart_options [ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				// Sanitize Abandoned cart delay time and enabled status.
				$cart_dely_time = (int) wp_unslash( sanitize_text_field( $autoresponders['cart_delay'] ) );
				$cart_enabled   = isset( $autoresponders['enable_cart'] ) ? wp_unslash( sanitize_text_field( $autoresponders['enable_cart'] ) ) : 0;

				// Save data to database.
				global $wpdb;
				// Smaily table name.
				$table_name = $wpdb->prefix . 'smaily';

				// Check if abandoned cart is enabled.
				if ( $cart_enabled ) {
					// Check if autoresponder for cart is selected.
					if ( empty( $sanitized_cart_autoresponder ) ) {
						// Return error if no autoresponder for abandoned cart.
						return array( 'error' => 'Select autoresponder for abandoned cart!' );
					}
				}
				// Error if no sync autoresponder.
				if ( empty( $autoresponder ) ) {
					$response = array( 'error' => 'No autoresponder selected, please select autoresponder!' );
				} elseif ( $cart_dely_time < 1 ) {
					$response = array( 'error' => 'Abandoned cart delay time value must be 1 or higher!' );
				} else {
					// Update DB with user values if autoresponder selected.
					$result = $wpdb->update(
						$table_name,
						array(
							'enable'                => isset( $sanitized_user['enable'] ) ? 1 : 0,
							'subdomain'             => $sanitized_user['subdomain'],
							'username'              => $sanitized_user['username'],
							'password'              => $sanitized_user['password'],
							'autoresponder'         => $sanitized_autoresponder['name'],
							'autoresponder_id'      => $sanitized_autoresponder['id'],
							'syncronize_additional' => isset( $sanitized_syncronize_additional ) ? implode( ',', $sanitized_syncronize_additional ) : null,
							'enable_cart'           => $cart_enabled === "on" ? 1 : 0,
							'cart_autoresponder'    => $sanitized_cart_autoresponder['name'],
							'cart_autoresponder_id' => $sanitized_cart_autoresponder['id'],
							'cart_delay'            => $cart_dely_time,
							'cart_options'          => isset( $sanitized_cart_options ) ? implode( ',', $sanitized_cart_options ) : null
						),
						array( 'id' => 1 )
					);
					if ( $result > 0 ) {
						$response = array( 'success' => 'Settings updated!' );
					} else if( $result === 0 ) {
						$response = array( 'success' => 'Settings saved!' );
					} else {
						$response = array( 'error' => 'Something went wrong saving settings!');
					}
				}
			}

			// Return message to user.
			echo wp_json_encode( $response );
			wp_die();

		}
	}


	/**
	 * Api call to Smaily with different parameters
	 *
	 * @param string $endpoint  Api endpont without .php.
	 * @param array  $data       Data to send to Smaily. Authentication info is received from Smaily settings.
	 * @param string $method    GET or POST method.
	 * @return array $response  Response from Smaily API
	 */
	public static function ApiCall( string $endpoint, array $data = [], $method = 'GET' ) {
		// Response.
		$response = [];
		// Smaily settings from database.
		$db_user_info = DataHandler::get_smaily_results();
		$result       = $db_user_info['result'];

		// Add authorization to data of request.
		$data = array_merge( $data, [ 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $result['username'] . ':' . $result['password'] ) ) ] );

		// API call with GET request.
		if ( $method === 'GET' ) {
			$api_call = wp_remote_get( 'https://' . $result['subdomain'] . '.sendsmaily.net/api/' . $endpoint . '.php', $data );

			// Response code from Smaily API.
			$http_code = wp_remote_retrieve_response_code( $api_call );

			// If Method POST.
		} elseif ( $method === 'POST' ) {
			// Add authorization to data of request.
			$api_call = wp_remote_post( 'https://' . $result['subdomain'] . '.sendsmaily.net/api/' . $endpoint . '.php', $data );
			// Response code from Smaily API.
			$http_code = wp_remote_retrieve_response_code( $api_call );

		}

		// Show error message if no access.
		if ( $http_code === 401 ) {
			$response = array( 'error' => 'Check details, no connection !' );
		}
		// Return autoresponders list back to front end for selection.
		if ( $http_code === 200 ) {
			$body     = json_decode( wp_remote_retrieve_body( $api_call ), true );
			$response = $body;
		}

		// Response from API call.
		return $response;
	}

}
