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
		if ( ! isset( $_POST['form_data'] ) && ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// Parse form data out of the serialization.
		$params = array();
		parse_str( $_POST['form_data'], $params ); // Ajax serialized string, sanitizing data before usage below.

		// Check for nonce-verification and sanitize user input.
		if ( ! wp_verify_nonce( sanitize_key( $params['nonce'] ), 'settings-nonce' ) ) {
			return;
		}

		// Sanitize fields.
		$sanitized = array(
			'subdomain' => '',
			'username'  => '',
			'password'  => '',
		);
		if ( is_array( $params ) ) {
			foreach ( $params as $key => $value ) {
				$sanitized[ $key ] = wp_unslash( sanitize_text_field( $value ) );
			}
		}

		// Normalize subdomain.
		// First, try to parse as full URL. If that fails, try to parse as subdomain.sendsmaily.net, and
		// if all else fails, then clean up subdomain and pass as is.
		if ( filter_var( $sanitized['subdomain'], FILTER_VALIDATE_URL ) ) {
			$url                    = wp_parse_url( $sanitized['subdomain'] );
			$parts                  = explode( '.', $url['host'] );
			$sanitized['subdomain'] = count( $parts ) >= 3 ? $parts[0] : '';
		} elseif ( preg_match( '/^[^\.]+\.sendsmaily\.net$/', $sanitized['subdomain'] ) ) {
			$parts                  = explode( '.', $sanitized['subdomain'] );
			$sanitized['subdomain'] = $parts[0];
		}

		$sanitized['subdomain'] = preg_replace( '/[^a-zA-Z0-9]+/', '', $sanitized['subdomain'] );

		// Show error messages to user if no data is entered to form.
		if ( $sanitized['subdomain'] === '' ) {
			echo wp_json_encode(
				array(
					'error' => esc_html__( 'Please enter subdomain!', 'smaily' ),
				)
			);
			wp_die();
		} elseif ( $sanitized['username'] === '' ) {
			echo wp_json_encode(
				array(
					'error' => esc_html__( 'Please enter username!', 'smaily' ),
				)
			);
			wp_die();
		} elseif ( $sanitized['password'] === '' ) {
			echo wp_json_encode(
				array(
					'error' => esc_hmtl__( 'Please enter password!', 'smaily' ),
				)
			);
			wp_die();
		}

		// If all fields are set make api call.
		$api_call = wp_remote_get(
			'https://' . $sanitized['subdomain'] . '.sendsmaily.net/api/workflows.php?trigger_type=form_submitted',
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
			echo wp_json_encode(
				array(
					'error' => esc_html__( 'Invalid API credentials, no connection !', 'smaily' ),
				)
			);
			wp_die();
		} elseif ( $http_code === 404 ) {
			echo wp_json_encode(
				array(
					'error' => esc_html__( 'Invalid subdomain, no connection !', 'smaily' ),
				)
			);
			wp_die();
		} elseif ( is_wp_error( $api_call ) ) {
			echo wp_json_encode( array( 'error' => $api_call->get_error_message() ) );
			wp_die();
		}

		// Return autoresponders list back to front end for selection.
		$response = array();
		$body = json_decode( wp_remote_retrieve_body( $api_call ), true );
		// Add autoresponders as a response to Ajax-call for updating autoresponders list.
		foreach ( $body as $autoresponder ) {
			array_push(
				$response,
				array(
					'name' => $autoresponder['title'],
					'id'   => $autoresponder['id'],
				)
			);
		}
		// Add validated autoresponders to settings.
		global $wpdb;
		// Smaily table name.
		$table_name = $wpdb->prefix . 'smaily';
		$wpdb->update(
			$table_name,
			array(
				'subdomain' => $sanitized['subdomain'],
				'username'  => $sanitized['username'],
				'password'  => $sanitized['password'],
			),
			array( 'id' => 1 )
		);
		// Return response to ajax call.
		echo wp_json_encode( $response );
		wp_die();
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
			// phpcs:ignore Ajax serialized data, sanitization below.
			parse_str( $_POST['autoresponder_data'], $autoresponders );
			$cart_autoresponder = json_decode( $autoresponders['cart_autoresponder'], true);

			// Check for nonce-verification and sanitize user input.
			if ( wp_verify_nonce( sanitize_key( $user['nonce'] ), 'settings-nonce' ) ) {
				$sanitized_user                  = [];
				$sanitized_cart_autoresponder    = [];
				$sanitized_syncronize_additional = [];
				$sanitized_cart_options          = [];
				if ( is_array( $user ) ) {
					foreach ( $user as $key => $value ) {
						$sanitized_user[ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				if ( is_array( $cart_autoresponder ) ) {
					foreach ( $cart_autoresponder as $key => $value ) {
						$sanitized_cart_autoresponder [ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				if ( isset( $autoresponders['syncronize_additional'] ) &&
					is_array( $autoresponders['syncronize_additional'] ) ) {
					foreach ( $autoresponders['syncronize_additional'] as $key => $value ) {
						$sanitized_syncronize_additional[ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				if ( isset( $autoresponders['cart_options'] ) &&
					is_array( $autoresponders['cart_options'] ) ) {
					foreach ( $autoresponders['cart_options'] as $key => $value) {
						$sanitized_cart_options [ $key ] = wp_unslash( sanitize_text_field( $value ) );
					}
				}

				// Sanitize Abandoned cart delay, cutoff time and enabled status.
				$cart_cutoff_time      = (int) wp_unslash( sanitize_text_field( $autoresponders['cart_cutoff'] ) );
				$cart_enabled          = isset( $autoresponders['enable_cart'] ) ? 1 : 0;
				$enabled               = isset( $autoresponders['enable'] ) ? 1 : 0;
				$syncronize_additional = ( $sanitized_syncronize_additional ) ? implode( ',', $sanitized_syncronize_additional ) : null;
				$cart_options          = isset( $sanitized_cart_options ) ? implode( ',', $sanitized_cart_options ) : null;

				// Save data to database.
				global $wpdb;
				// Smaily table name.
				$table_name = $wpdb->prefix . 'smaily';

				// Check if abandoned cart is enabled.
				if ( $cart_enabled ) {
					// Check if autoresponder for cart is selected.
					if ( empty( $sanitized_cart_autoresponder ) ) {
						// Return error if no autoresponder for abandoned cart.
						echo wp_json_encode(
							array(
								'error' => esc_html__( 'Select autoresponder for abandoned cart!', 'smaily' ),
							)
						);
						wp_die();
					}
					// Check if cart cutoff time is valid.
					if ( $cart_cutoff_time < 10 ) {
						echo wp_json_encode(
							array(
								'error' => esc_html__( 'Abandoned cart cutoff time value must be 10 or higher!', 'smaily' ),
							)
						);
						wp_die();
					}
				}

				// Update DB with user values if autoresponder selected.
				$result = $wpdb->update(
					$table_name,
					array(
						'enable'                => $enabled,
						'syncronize_additional' => $syncronize_additional,
						'enable_cart'           => $cart_enabled,
						'cart_autoresponder'    => $sanitized_cart_autoresponder['name'],
						'cart_autoresponder_id' => $sanitized_cart_autoresponder['id'],
						'cart_cutoff'           => $cart_cutoff_time,
						'cart_options'          => $cart_options,
					),
					array( 'id' => 1 )
				);
				if ( $result > 0 ) {
					$response = array(
						'success' => esc_html__( 'Settings updated!', 'smaily' ),
					);
				} elseif ( $result === 0 ) {
					$response = array(
						'success' => esc_html__( 'Settings saved!', 'smaily' ),
					);
				} else {
					$response = array(
						'error' => esc_html__( 'Something went wrong saving settings!', 'smaily' ),
					);
				}
			}
		}
		// Return message to user.
		echo wp_json_encode( $response );
		wp_die();

	}

	/**
	 * Api call to Smaily with different parameters
	 *
	 * @param string $endpoint  Api endpont without .php.
	 * @param string $params    Additional params to attatch to ApiCall for workflows.
	 * @param array  $data      Data to send to Smaily. Authentication info is received from Smaily settings.
	 * @param string $method    GET or POST method.
	 * @return array $response  Response from Smaily API
	 */
	public static function ApiCall( string $endpoint, string $params = '', array $data = [], $method = 'GET' ) {
		// Response.
		$response = [];
		// Smaily settings from database.
		$db_user_info = DataHandler::get_smaily_results();
		$result       = $db_user_info['result'];

		// Add authorization to data of request.
		$data = array_merge( $data, [ 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $result['username'] . ':' . $result['password'] ) ) ] );

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

		// Show error message if no access.
		if ( $http_code === 401 ) {
			$response = array(
				'error' => esc_html__( 'Check details, no connection !', 'smaily' ),
			);
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
