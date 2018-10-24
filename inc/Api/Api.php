<?php
/**
 * @package smaily_woocommerce_plugin
 */
namespace Inc\Api;

use Inc\Base\DataHandler;

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

		// Parse form data out of the serialization.
		$params = array();
		parse_str( $_POST['form_data'], $params );
		// Show error messages to user if no data is entered to form.
		$response = array();
		if ( $params['subdomain'] === '' ) {
			$response = array( 'error' => 'Please enter subdomain!' );
		} elseif ( $params['username'] === '' ) {
			$response = array( 'error' => 'Please enter username!' );
		} elseif ( $params['password'] === '' ) {
			$response = array( 'error' => 'Please enter password!' );
		} else {
			// If all fields are set make api call.
			$api_call = wp_remote_get(
				'https://' . $params['subdomain'] . '.sendsmaily.net/api/autoresponder.php',
				[
					'page'    => 1,
					'limit'   => 100,
					'status'  => [ 'ACTIVE' ],
					'headers' => array(
						'Authorization' => 'Basic ' . base64_encode( $params['username'] . ':' . $params['password'] ),
					),
				]
			);

			// Response code from Smaily API.
			$http_code = wp_remote_retrieve_response_code( $api_call );
			// Show error message if no access.
			if ( $http_code == 401 ) {
				$response = array( 'error' => 'Invalid API credentials, no connection !' );
			}
			// Return autoresponders list back to front end for selection.
			if ( $http_code == 200 ) {
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
		echo json_encode( $response );
		wp_die();
	}

	/**
	 * Save user API information to WordPress database
	 *
	 * @return void
	 */
	public function save_api_information() {

		// Receive data from Settings form.
		$user = array();
		parse_str( $_POST['user_data'], $user );
		$autoresponders = array();
		parse_str( $_POST['autoresponder_data'], $autoresponders );
		$autoresponder = json_decode( $autoresponders['autoresponder'], true );

		// Response to front-end js.
		$response = array();

		// Save data to database.
		global $wpdb;
		// Smaily table name.
		$table_name = $wpdb->prefix . 'smaily';

		// Error if no autoresponders.
		if ( empty( $autoresponder ) ) {
			$response = array( 'error' => 'No autoresponder selected, please select autoresponder!' );
		} else {
			// Update DB with user values if autoresponder selected.
			$wpdb->update(
				$table_name,
				array(
					'enable'                => isset( $user['enable'] ) ? 'on' : 'off',
					'subdomain'             => $user['subdomain'],
					'username'              => $user['username'],
					'password'              => $user['password'],
					'autoresponder'         => $autoresponder['name'],
					'autoresponder_id'      => $autoresponder['id'],
					'syncronize_additional' => isset( $autoresponders['syncronize_additional'] ) ? implode( ',', $autoresponders['syncronize_additional'] ) : null,
					'syncronize'            => null,
				),
				array( 'id' => 1 )
			);

			$response = array( 'success' => 'Settings updated!' );
		}

		// Return message to user.
		echo json_encode( $response );
		wp_die();
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
		$dbUserInfo = DataHandler::get_smaily_results();
		$result     = $dbUserInfo['result'];

		// Add authorization to data of request.
		$data = array_merge( $data, [ 'headers' => array( 'Authorization' => 'Basic ' . base64_encode( $result['username'] . ':' . $result['password'] ) ) ] );

		// API call with GET request.
		if ( $method == 'GET' ) {
			$api_call = wp_remote_get( 'https://' . $result['subdomain'] . '.sendsmaily.net/api/' . $endpoint . '.php', $data );

			// Response code from Smaily API.
			$http_code = wp_remote_retrieve_response_code( $api_call );

			// Show error message if no access.
			if ( $http_code == 401 ) {
				$response = array( 'error' => 'Check details, no connection !' );
			}
			// Return autoresponders list back to front end for selection.
			if ( $http_code == 200 ) {
				$body     = json_decode( wp_remote_retrieve_body( $api_call ), true );
				$response = $body;
			}
			// If Method POST.
		} elseif ( $method == 'POST' ) {
			// Add authorization to data of request.
			$api_call = wp_remote_post( 'https://' . $result['subdomain'] . '.sendsmaily.net/api/' . $endpoint . '.php', $data );
			// Response code from Smaily API.
			$http_code = wp_remote_retrieve_response_code( $api_call );

			// Show error message if no access.
			if ( $http_code == 401 ) {
				$response = array( 'error' => 'Check details, no connection !' );
			}
			// Return autoresponders list back to front end for selection.
			if ( $http_code == 200 ) {
				$body     = json_decode( wp_remote_retrieve_body( $api_call ), true );
				$response = $body;
			}
		}

		 // Response from API call.
		 return $response;
	}

}
