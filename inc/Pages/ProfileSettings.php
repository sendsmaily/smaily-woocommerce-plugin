<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Pages;

use Smaily_Inc\Base\DataHandler;
/**
 * Adds and controlls WooCommerce Register and Account Details fields.
 * Adds and controlls WordPress User Profile and Admin Profile fields.
 */
class ProfileSettings {

	/**
	 * Adds additional profile settings fields to user froms.
	 *
	 * @return void
	 */
	public function register() {

		// Show additional profile fields only if credentials are validated.
		$result = DataHandler::get_smaily_results();

		// No subdomain before successful credential validation.
		if ( ! empty( $result['result']['subdomain'] ) ) {
			// Add fields to registration form and account area.
			add_action( 'woocommerce_register_form', array( $this, 'smaily_print_user_frontend_fields' ), 10 );
			add_action( 'woocommerce_edit_account_form', array( $this, 'smaily_print_user_frontend_fields' ), 10 );

			// Show fields in checkout area.
			add_filter( 'woocommerce_checkout_fields', array( $this, 'smaily_checkout_fields' ), 10, 1 );

			// Add checkbox to admin preferred location.
			$cb_location = self::generate_checkbox_location();
			add_action( $cb_location, array( $this, 'smaily_checkout_newsletter_checkbox' ) );

			// Add fields to admin area.
			add_action( 'show_user_profile', array( $this, 'smaily_print_user_admin_fields' ), 30 ); // admin: edit profile.
			add_action( 'edit_user_profile', array( $this, 'smaily_print_user_admin_fields' ), 30 ); // admin: edit other users.

			// Save registration fields.
			add_action( 'woocommerce_created_customer', array( $this, 'smaily_save_account_fields' ) ); // register/checkout.
			add_action( 'personal_options_update', array( $this, 'smaily_save_account_fields' ) ); // edit own account admin.
			add_action( 'edit_user_profile_update', array( $this, 'smaily_save_account_fields' ) ); // edit other account admin.
			add_action( 'woocommerce_save_account_details', array( $this, 'smaily_save_account_fields' ) ); // edit WC account.
		}

	}

	/**
	 * Generates correctly formatted woocommerce hook based of plugin settings.
	 *
	 * @return string
	 */
	public static function generate_checkbox_location() {
		$settings = DataHandler::get_smaily_results()['result'];

		$order    = $settings['checkbox_order'];
		$location = $settings['checkbox_location'];

		// Syntax - woocommerce_before_checkout_billing_form.
		$location = 'woocommerce_' . $order . '_' . $location;

		return $location;
	}

	/**
	 * Add newsletter subscribe button to admin preferred place in checkout page.
	 *
	 * @return void
	 */
	public function smaily_checkout_newsletter_checkbox() {
		$settings = DataHandler::get_smaily_results()['result'];
		$checked  = intval( $settings['checkbox_auto_checked'] );
		$enabled  = intval( $settings['enable_checkbox'] );
		if ( $enabled ) {
			$checkbox  = '<p class="form-row form-row-wide smaily-for-woocommerce-newsletter">';
			$checkbox .= '<label class="checkbox woocommerce-form__label woocommerce-form__label-for-checkbox">';
			$checkbox .= '<input type="checkbox" class="input-checkbox woocommerce-form__input woocommerce-form__input-checkbox" name="user_newsletter" id="smaily-checkout-subscribe" value="1"' . checked( $checked, 1, false ) . ' />';
			$checkbox .= '<span>' . __( 'Subscribe to newsletter', 'smaily' ) . '</span>';
			$checkbox .= '</label>';
			$checkbox .= '</p>';

			echo $checkbox;
		}
	}

	/**
	 * Add fields to registration area and account area
	 *
	 * @return void
	 */
	public function smaily_print_user_frontend_fields() {
		// Get new fileds.
		$fields            = $this->smaily_get_account_fields();
		$is_user_logged_in = is_user_logged_in();

		foreach ( $fields as $key => $field_args ) {
			$value = null;

			// Conditionals to show fields based on hide-settings.
			if ( $is_user_logged_in && ! empty( $field_args['hide_in_account'] ) ) {
				continue;
			}

			if ( ! $is_user_logged_in && ! empty( $field_args['hide_in_registration'] ) ) {
				continue;
			}

			// Get user information and save in value.
			if ( $is_user_logged_in ) {
				$user_id = $this->smaily_get_edit_user_id();
				$value   = $this->smaily_get_userdata( $user_id, $key );
			}

			$value = isset( $field_args['value'] ) ? $field_args['value'] : $value;

			woocommerce_form_field( $key, $field_args, $value );
		}
	}

	/**
	 * Add additional account fields data
	 *
	 * @return array $smaily_account_fields New fields to add in forms.
	 */
	public function smaily_get_account_fields() {

		// Get fields from sync_additional.
		$result = DataHandler::get_smaily_results();
		if ( ! empty( $result['syncronize_additional'] ) ) {
			// All custom fields available.
			$fields_available = array(
				'user_gender' => array(
					'type'                 => 'radio',
					'label'                => __( 'Gender', 'smaily' ),
					'required'             => false,
					'class'                => array( 'tog' ),
					'options'              => array(
						1 => __( 'Male', 'smaily' ),
						2 => __( 'Female', 'smaily' ),
					),
					'hide_in_account'      => false,
					'hide_in_admin'        => false,
					'hide_in_checkout'     => false,
					'hide_in_registration' => false,
				),
				'user_phone'  => array(
					'type'                 => 'tel',
					'label'                => __( 'Phone', 'smaily' ),
					'placeholder'          => __( 'Enter phone number', 'smaily' ),
					'required'             => false,
					'class'                => array( 'regular-text' ),
					'hide_in_account'      => false,
					'hide_in_admin'        => false,
					'hide_in_checkout'     => true,
					'hide_in_registration' => false,
				),
				'user_dob'    => array(
					'type'                 => 'date',
					'label'                => __( 'Birthday', 'smaily' ),
					'placeholder'          => __( 'Enter birthday', 'smaily' ),
					'required'             => false,
					'class'                => array( 'regular-text' ),
					'hide_in_account'      => false,
					'hide_in_admin'        => false,
					'hide_in_checkout'     => false,
					'hide_in_registration' => false,

				),
			);

			$add_fields = array(
				'user_newsletter' => array(
					'type'                 => 'checkbox',
					'label'                => __( 'Subscribe to newsletter', 'smaily' ),
					'required'             => false,
					'hide_in_account'      => false,
					'hide_in_admin'        => false,
					'hide_in_checkout'     => false,
					'hide_in_registration' => false,
				),
			);

			// Add only new fields selected from syncronize_additional.
			$syncronize_additional = $result['syncronize_additional'];
			foreach ( $syncronize_additional as $key ) {
				if ( array_key_exists( $key, $fields_available ) ) {
					$add_fields[ $key ] = $fields_available[ $key ];
				}
			}
			return apply_filters(
				'smaily_account_fields',
				$add_fields
			);
		} else {
			// If no additional fields selected, show only newsletter subscribe option.
			return apply_filters(
				'smaily_account_fields',
				array(
					'user_newsletter' => array(
						'type'                 => 'checkbox',
						'label'                => __( 'Subscribe newsletter', 'smaily' ),
						'required'             => false,
						'hide_in_account'      => false,
						'hide_in_admin'        => false,
						'hide_in_checkout'     => false,
						'hide_in_registration' => false,
					),
				)
			);
		}
	}

	/**
	 * Show fields at checkout form
	 *
	 * @param array $checkout_fields Old checkout fields.
	 * @return array $checkout_fields Updated checkout fields
	 */
	public function smaily_checkout_fields( $checkout_fields ) {
		// Get available account fields.
		$fields = $this->smaily_get_account_fields();
		// Fields to append to billing information.
		$billing_details_list = array( 'user_gender', 'user_phone', 'user_dob' );

		foreach ( $fields as $key => $field_args ) {

			if ( ! empty( $field_args['hide_in_checkout'] ) ) {
				continue;
			}

			// Append billing details to customer billing details list.
			if ( in_array( $key, $billing_details_list, true ) ) {
				$checkout_fields['billing'][ $key ] = $field_args;
			}
		}

		return $checkout_fields;
	}

	/**
	 * Add fields to admin area
	 *
	 * @return void
	 */
	public function smaily_print_user_admin_fields() {
		// Get account fields.
		$fields = $this->smaily_get_account_fields();
		?>
		<h2><?php esc_html_e( 'Additional Information', 'smaily' ); ?></h2>
		<table class="form-table" id="smaily-additional-information">

			<?php foreach ( $fields as $key => $field_args ) { ?>
				<?php
				if ( ! empty( $field_args['hide_in_admin'] ) ) {
					continue;
				}

				$user_id = $this->smaily_get_edit_user_id();
				$value   = $this->smaily_get_userdata( $user_id, $key );
				?>
				<tr>
					<th>
						<label for="<?php echo esc_html( $key ); ?>"><?php echo esc_html( $field_args['label'] ); ?></label>
					</th>
					<td>
						<?php $field_args['label'] = false; ?>
						<?php woocommerce_form_field( $key, $field_args, $value ); ?>
					</td>
				</tr>
			<?php } ?>

		</table>
		<?php
	}

	/**
	 *  Save registration fields
	 *
	 * @param int $customer_id Customer id from WooCommerce.
	 * @return void
	 */
	public function smaily_save_account_fields( $customer_id ) {
		// Get account field data.
		$fields         = $this->smaily_get_account_fields();
		$sanitized_data = array();

		foreach ( $fields as $key => $field_args ) {
			if ( ! $this->smaily_is_field_visible( $field_args ) ) {
				continue;
			}

			$sanitize = isset( $field_args['sanitize'] ) ? $field_args['sanitize'] : 'wc_clean';
			$value    = isset( $_POST[ $key ] ) ? call_user_func( $sanitize, $_POST[ $key ] ) : '';

			if ( $this->smaily_is_userdata( $key ) ) {
				$sanitized_data[ $key ] = $value;
				continue;
			}

			update_user_meta( $customer_id, $key, $value );
		}

		if ( ! empty( $sanitized_data ) ) {
			$sanitized_data['ID'] = $customer_id;
			wp_update_user( $sanitized_data );
		}
	}


	/**
	 * Helper functions
	 */

	/**
	 * Get currently editing user ID (frontend account/edit profile/edit other user)
	 *
	 * @return int $user_id Current user ID
	 */
	public function smaily_get_edit_user_id() {
		return isset( $_GET['user_id'] ) ? (int) $_GET['user_id'] : get_current_user_id();
	}


	/**
	 * Get user data based on key
	 *
	 * @param int    $user_id User ID.
	 * @param string $key Key to search from user data.
	 * @return string $userdata User data based on key
	 */
	public function smaily_get_userdata( $user_id, $key ) {
		if ( ! $this->smaily_is_userdata( $key ) ) {
			return get_user_meta( $user_id, $key, true );
		}

		$userdata = get_userdata( $user_id );

		if ( ! $userdata || ! isset( $userdata->{$key} ) ) {
			return '';
		}

		return $userdata->{$key};
	}

	/**
	 * Check if the field is visible to user.
	 *
	 * @param array $field_args Form field.
	 * @return boolean $visible Visibility.
	 */
	public function smaily_is_field_visible( $field_args ) {
		$visible = true;
		$action  = filter_input( INPUT_POST, 'action' );

		if ( is_admin() && ! empty( $field_args['hide_in_admin'] ) ) {
			$visible = false;
		} elseif ( ( is_account_page() || $action === 'save_account_details' ) && is_user_logged_in() && ! empty( $field_args['hide_in_account'] ) ) {
			$visible = false;
		} elseif ( ( is_account_page() || $action === 'save_account_details' ) && ! is_user_logged_in() && ! empty( $field_args['hide_in_registration'] ) ) {
			$visible = false;
		} elseif ( is_checkout() && ! empty( $field_args['hide_in_checkout'] ) ) {
			$visible = false;
		}

		return $visible;
	}

	/**
	 *  Check if field is one of WordPress predefined fields.
	 *
	 * @param string $key Key to be checked.
	 * @return boolean $inUserdata True if is in predefined fields.
	 */
	private function smaily_is_userdata( $key ) {
		$userdata = array(
			'user_pass',
			'user_login',
			'user_nicename',
			'user_email',
			'display_name',
			'nickname',
			'first_name',
			'last_name',
			'description',
			'rich_editing',
			'user_registered',
			'role',
			'jabber',
			'aim',
			'yim',
			'show_admin_bar_front',
		);

		return in_array( $key, $userdata );
	}

}
