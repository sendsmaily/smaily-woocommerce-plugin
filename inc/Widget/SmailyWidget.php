<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Widget;

use Smaily_Inc\Base\DataHandler;

/**
 * Handles communication between Smaily API and WordPress.
 */
class SmailyWidget extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'smaily_widget', // Base ID.
			esc_html__( 'Smaily for WooCommerce Form', 'smaily' ), // Name.
			array( 'description' => esc_html__( 'Smaily for WooCommerce Newsletter Widget', 'smaily' ) ) // Args.
		);
	}

	/**
	 * Action hooks for initializing widget.
	 *
	 * @return void
	 */
	public function register() {

		// Register widget for WordPress.
		add_action(
			'widgets_init',
			function() {
				register_widget( 'Smaily_Inc\Widget\SmailyWidget' );
			}
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		// Get results from database.
		$result = DataHandler::get_smaily_results();
		if ( isset( $result ) ) {
			$result = $result['result'];
		}

		// Get autoresponder id from instance if saved.
		$autoresponder_id = '';
		if ( isset( $instance['autoresponder'] ) && ! empty( $instance['autoresponder'] ) ) {
			$autoresponder    = json_decode( $instance['autoresponder'], true );
			$autoresponder_id = $autoresponder['id'];
		}

		// Get current url.
		$current_url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		// Remove params from redirect links.
		$params     = array( 'code', 'message' );
		$return_url = remove_query_arg( $params, $current_url );

		// Language code if using WPML.
		$lang = '';
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			$lang = ICL_LANGUAGE_CODE;
			// Language code if using polylang.
		} elseif ( function_exists( 'pll_current_language' ) ) {
			$lang = pll_current_language();
		} else {
			$lang = get_locale();
			if ( strlen( $lang ) > 0 ) {
				// Remove any value past underscore if exists.
				$lang = explode( '_', $lang )[0];
			}
		}

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		// Widget front-end.
		// Echo messages if available.
		if ( isset( $_GET['code'] ) ) {
			switch ( $_GET['code'] ) {
				case 101:
					$message = __( 'Subscription registration successful.', 'smaily' );
					break;
				case 204:
					$message = __( 'Subscription does not contain a valid email address.', 'smaily' );
					break;
				default:
					$message = __( 'Subscription registration failed.', 'smaily' );
					break;
			}

			echo '
				<div class="smaily-newsletter-alert">
				<p>' . esc_html( $message ) . '
				<span class="smaily-newsletter-closebtn" onclick="this.parentElement.style.display=\'none\'">&times;</span>
				</p>
				</div>
				';
		}
		// Main form.
		echo '<form class="smaily-newsletter-form" action="https://' . esc_html( $result['subdomain'] ) . '.sendsmaily.net/api/opt-in/" method="post" autocomplete="off">
				<div>
				<input type="hidden" name="success_url" value="' . esc_url( $return_url ) . '" />
				<input type="hidden" name="failure_url" value="' . esc_url( $return_url ) . '" />
				<input type="hidden" name="language" value="' . esc_html( $lang ) . '" />
		';

		// Optional autoresponder when selected.
		echo $autoresponder_id ? '<input type="hidden" name="autoresponder" value="' . esc_html( $autoresponder_id ) . '" />' : '';
		echo '</div>
				<p>
					<label>' . esc_html__( 'Email', 'smaily' ) . '</label>
					<input type="text" name="email" value="" />
				</p>
				<p>
					<label>' . esc_html__( 'Name', 'smaily' ) . '</label>
					<input type="text" name="name" value="" />
				</p>
				<p>
					<button class="ui pink basic button" type="submit">' .
						esc_html__( 'Subscribe', 'smaily' ) .
					'</button>
				</p>
				<div style="overflow:hidden;height:0px;">
					<input type="text" name="re-email" value="" />
				</div>
			</form>
		';

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$autoresponder_list = DataHandler::get_autoresponder_list();

		$title                             = isset( $instance['title'] ) ? $instance['title'] : __( 'Smaily for WooCommerce Form', 'smaily' );
		$form_layout                       = isset( $instance['form_layout'] ) ? $instance['form_layout'] : 'layout-1';
		$email_field_placeholder           = isset( $instance['email_field_placeholder'] ) ? $instance['email_field_placeholder'] : __( 'Email', 'smaily' );
		$name_field_placeholder            = isset( $instance['name_field_placeholder'] ) ? $instance['name_field_placeholder'] : __( 'Name', 'smaily' );
		$submit_button_text                = isset( $instance['submit_button_text'] ) ? $instance['submit_button_text'] : __( 'Send', 'smaily' );
		$submit_button_color               = isset( $instance['submit_button_color'] ) ? $instance['submit_button_color'] : '';
		$submit_button_text_color          = isset( $instance['submit_button_text_color'] ) ? $instance['submit_button_text_color'] : '';
		$use_site_submit_button_color      = isset( $instance['use_site_submit_button_color'] ) ? $instance['use_site_submit_button_color'] : true;
		$use_site_submit_button_text_color = isset( $instance['use_site_submit_button_text_color'] ) ? $instance['use_site_submit_button_text_color'] : true;
		$current_autoresponder             = isset( $instance['autoresponder'] ) ? json_decode( $instance['autoresponder'], true ) : array( 'id' => null );
		?>

		<div class="smaily">
			<!-- Title. -->
			<div class="section">
				<h2>
					<?php esc_html_e( 'Title', 'smaily' ); ?>
				</h2>
				<input
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
					type="text"
					value="<?php echo esc_attr( $title ); ?>" />
			</div>

			<!-- Autoresponder. -->
			<?php if ( ! empty( $autoresponder_list ) && ! array_key_exists( 'empty', $autoresponder_list ) ) : ?>
			<div class="section">
				<h2>
					<?php esc_html_e( 'Autoresponder', 'smaily' ); ?>
				</h2>
				<select
					id="<?php echo esc_attr( $this->get_field_id( 'autoresponder' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'autoresponder' ) ); ?>"
					class="widefat"
					style="width:100%;">
					<option value=""><?php esc_html_e( '-No Autoresponder-', 'smaily' ); ?></option>
					<?php if ( ! empty( $autoresponder_list ) && ! array_key_exists( 'empty', $autoresponder_list ) ) : ?>
						<?php foreach ( $autoresponder_list as $autoresponder ) : ?>
						<option value="<?php echo htmlentities( json_encode( $autoresponder ) ); ?>"
											<?php
											if ( $autoresponder['id'] === $current_autoresponder['id'] ) :
												?>
							selected<?php endif; ?>><?php echo $autoresponder['name']; ?></option>
					<?php endforeach; ?>
					<?php endif; ?>
				</select>
				<p>
					<?php esc_html_e( 'Select a suitable form-submitted trigger automation workflow from the list or create a new autoresponder on your Smaily account to be added to the list.', 'smaily' ); ?>
				</p>
			</div>
			<?php endif; ?>

			<!-- Layouts. -->
			<div class="section">
				<h2>
					<?php esc_html_e( 'Layout', 'smaily' ); ?>
				</h2>
				<p>
					<?php esc_html_e( 'Choose a preferred layout.', 'smaily' ); ?>
				</p>

				<div class="smaily-layout-container">
					<p> <!-- Select layout. -->
						<input
							class="radio"
							id="<?php echo $this->get_field_id( 'layout-1' ); ?>"
							name="<?php echo $this->get_field_name( 'form_layout' ); ?>"
							type="radio"
							value="layout-1"
							<?php
							if ( $form_layout === 'layout-1' ) {
								echo 'checked="checked"'; }
							?>
							/>
						<label for="<?php echo $this->get_field_id( 'layout-1' ); ?>">
							<strong><?php esc_html_e( 'Layout 1', 'smaily' ); ?></strong> &minus; <?php esc_html_e( 'email address with spaced button', 'smaily' ); ?>
						</label>
					</p>
					<p>
						<img src="<?php echo SMAILY_PLUGIN_URL . 'static/layouts/email-layout1.svg'; ?>" alt="" title="<?php esc_attr_e( 'Layout 1', 'smaily' ); ?> &minus; <?php esc_attr_e( 'email address with spaced button', 'smaily' ); ?>" class="smaily-layouts">
					</p>
				</div>
				<div class="smaily-layout-container">
					<p>
						<input
							class="radio"
							id="<?php echo $this->get_field_id( 'layout-2' ); ?>"
							name="<?php echo $this->get_field_name( 'form_layout' ); ?>"
							type="radio"
							value="layout-2"
							<?php
							if ( $form_layout === 'layout-2' ) {
								echo 'checked="checked"'; }
							?>
							/>
						<label for="<?php echo $this->get_field_id( 'layout-2' ); ?>">
							<strong><?php esc_html_e( 'Layout 2', 'smaily' ); ?></strong> &minus; <?php esc_html_e( 'email address with attached button', 'smaily' ); ?>
						</label>
					</p>
					<p>
						<img src="<?php echo SMAILY_PLUGIN_URL . 'static/layouts/email-layout2.svg'; ?>" alt="" title="<?php esc_attr_e( 'Layout 2', 'smaily' ); ?> &minus; <?php esc_attr_e( 'email address with attached button', 'smaily' ); ?>" class="smaily-layouts">
					</p>
				</div>
				<div class="smaily-layout-container">
					<p>
						<input
							class="radio"
							id="<?php echo $this->get_field_id( 'layout-3' ); ?>"
							name="<?php echo $this->get_field_name( 'form_layout' ); ?>"
							type="radio"
							value="layout-3"
							<?php
							if ( $form_layout === 'layout-3' ) {
								echo 'checked="checked"'; }
							?>
							/>
						<label for="<?php echo $this->get_field_id( 'layout-3' ); ?>">
							<strong><?php esc_html_e( 'Layout 3', 'smaily' ); ?></strong> &minus; <?php esc_html_e( 'email address', 'smaily' ); ?>
						</label>
					</p>
					<p>
						<img src="<?php echo SMAILY_PLUGIN_URL . 'static/layouts/email-layout3.svg'; ?>" alt="" title="<?php esc_attr_e( 'Layout 3', 'smaily' ); ?> &minus; <?php esc_attr_e( 'email address', 'smaily' ); ?>" class="smaily-layouts">
					</p>
				</div>
				<div class="smaily-layout-container">
					<p>
						<input
							class="radio"
							id="<?php echo $this->get_field_id( 'layout-4' ); ?>"
							name="<?php echo $this->get_field_name( 'form_layout' ); ?>"
							type="radio"
							value="layout-4"
							<?php
							if ( $form_layout === 'layout-4' ) {
								echo 'checked="checked"'; }
							?>
							/>
						<label for="<?php echo $this->get_field_id( 'layout-4' ); ?>">
							<strong><?php esc_html_e( 'Layout 4', 'smaily' ); ?></strong> &minus; <?php esc_html_e( 'email address and name', 'smaily' ); ?>
						</label>
					</p>
					<p>
						<img src="<?php echo SMAILY_PLUGIN_URL . 'static/layouts/email-layout4.svg'; ?>" alt="" title="<?php esc_attr_e( 'Layout 4', 'smaily' ); ?> &minus; <?php esc_attr_e( 'email address and name', 'smaily' ); ?>" class="smaily-layouts">
					</p>
				</div>
				<div class="smaily-layout-container">
					<p>
						<input
							class="radio"
							id="<?php echo $this->get_field_id( 'layout-5' ); ?>"
							name="<?php echo $this->get_field_name( 'form_layout' ); ?>"
							type="radio"
							value="layout-5"
							<?php
							if ( $form_layout === 'layout-5' ) {
								echo 'checked="checked"'; }
							?>
							/>
						<label for="<?php echo $this->get_field_id( 'layout-5' ); ?>">
							<strong><?php esc_html_e( 'Layout 5', 'smaily' ); ?></strong> &minus; <?php esc_html_e( 'stacked email address and name', 'smaily' ); ?>
						</label>
					</p>
					<p>
						<!-- Read URL starting from the static folder. Doesn`t matter where in your computer plugin is located. -->
						<img src="<?php echo SMAILY_PLUGIN_URL . 'static/layouts/email-layout5.svg'; ?>" alt="" title="<?php esc_attr_e( 'Layout 5', 'smaily' ); ?> &minus; <?php esc_attr_e( 'stacked email address and name', 'smaily' ); ?>" class="smaily-layouts">
					</p>
				</div>
			</div>

			<!-- Customize layout. -->
			<div class="section">
				<h2>
					<!-- Layout customization section. -->
					<?php echo esc_html_e( 'Customize layout', 'smaily' ); ?>
				</h2>

				<!-- Email field placeholder. -->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'email_field_placeholder' ) ); ?>">
						<b><?php esc_html_e( 'Email field text', 'smaily' ); ?></b>
					</label>
					<input
						class="widefat"
						id="<?php echo esc_attr( $this->get_field_id( 'email_field_placeholder' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'email_field_placeholder' ) ); ?>"
						type="text"
						value="<?php echo esc_attr( $email_field_placeholder ); ?>" />
				</p>

				<!-- Name field placeholder. -->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'name_field_placeholder' ) ); ?>">
						<b><?php esc_html_e( 'Name field text', 'smaily' ); ?></b>
					</label>
					<input
						class="widefat"
						id="<?php echo esc_attr( $this->get_field_id( 'name_field_placeholder' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'name_field_placeholder' ) ); ?>"
						type="text"
						value="<?php echo esc_attr( $name_field_placeholder ); ?>" />
				</p>

				<!-- Button field text. -->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'submit_button_text' ) ); ?>">
						<b><?php esc_html_e( 'Text on the button', 'smaily' ); ?></b>
					</label>
					<input
						class="widefat"
						id="<?php echo esc_attr( $this->get_field_id( 'submit_button_text' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'submit_button_text' ) ); ?>"
						type="text"
						value="<?php echo esc_attr( $submit_button_text ); ?>" />
				</p>

				<!-- Customize button color and text. -->
				<div class="row">
					<div class="column" id="button-color-container">
						<!-- Add color picker HTML. -->
						<div>
							<label for="<?php echo esc_attr( $this->get_field_id( 'submit_button_color' ) ); ?>">
								<b><?php esc_html_e( 'Button color', 'smaily' ); ?></b>
							</label>
							<!-- Jscolor required:false is used to clear the input value. -->
							<input
								data-jscolor="{required:false}"
								class="button-style"
								<?php
								if ( $use_site_submit_button_color === true ) :
									?>
									disabled<?php endif; ?>
								id="<?php echo esc_attr( $this->get_field_id( 'submit_button_color' ) ); ?>"
								name="<?php echo esc_attr( $this->get_field_name( 'submit_button_color' ) ); ?>"
								type="text"
								value="<?php echo esc_attr( $submit_button_color ); ?>" />
						</div>
						<div class="default-value-checkbox">
							<input
								class="smaily-checkbox default_background_color"
								<?php
								if ( $use_site_submit_button_color === true ) :
									?>
									checked<?php endif; ?>
								id="<?php echo $this->get_field_id( 'default_background_color' ); ?>"
								name="<?php echo $this->get_field_name( 'use_site_submit_button_color' ); ?>"
								type="checkbox"
								value="1" />
							<label for="<?php echo $this->get_field_id( 'use_site_submit_button_color' ); ?>" ><?php esc_html_e( 'Use default button color?', 'smaily' ); ?></label>
						</div>
					</div>

					<div class="column" id="button-text-container">
						<div>
							<label for="<?php echo esc_attr( $this->get_field_id( 'submit_button_text_color' ) ); ?>">
								<b><?php esc_html_e( 'Button text color', 'smaily' ); ?></b>
							</label>
							<!--Jscolor required: false is used to clear the input value. -->
							<input
								data-jscolor="{required:false}"
								class="button-style"
								type="text"
								<?php
								if ( $use_site_submit_button_text_color === true ) :
									?>
									disabled<?php endif; ?>
								id="<?php echo esc_attr( $this->get_field_id( 'submit_button_text_color' ) ); ?>"
								name="<?php echo esc_attr( $this->get_field_name( 'submit_button_text_color' ) ); ?>"
								value="<?php echo esc_attr( $submit_button_text_color ); ?>" />
						</div>
						<div class="default-value-checkbox">
							<input
								class="smaily-checkbox default_text_color"
								<?php
								if ( $use_site_submit_button_text_color === true ) :
									?>
									checked<?php endif; ?>
								id="<?php echo $this->get_field_id( 'default_text_color' ); ?>"
								name="<?php echo $this->get_field_name( 'use_site_submit_button_text_color' ); ?>"
								type="checkbox"
								value="1" />
							<label for="<?php echo $this->get_field_id( 'use_site_submit_button_text_color' ); ?>" ><?php esc_html_e( 'Use default text color?', 'smaily' ); ?></label>
						</div>
					</div>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		// Sanitize user input.
		$instance['title']                             = isset( $new_instance['title'] ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['form_layout']                       = isset( $new_instance['form_layout'] ) ? sanitize_text_field( $new_instance['form_layout'] ) : '';
		$instance['autoresponder']                     = isset( $new_instance['autoresponder'] ) ? sanitize_text_field( $new_instance['autoresponder'] ) : '';
		$instance['email_field_placeholder']           = isset( $new_instance['email_field_placeholder'] ) ? sanitize_text_field( $new_instance['email_field_placeholder'] ) : '';
		$instance['name_field_placeholder']            = isset( $new_instance['name_field_placeholder'] ) ? sanitize_text_field( $new_instance['name_field_placeholder'] ) : '';
		$instance['submit_button_text']                = isset( $new_instance['submit_button_text'] ) ? sanitize_text_field( $new_instance['submit_button_text'] ) : '';
		$instance['submit_button_color']               = isset( $new_instance['submit_button_color'] ) ? sanitize_text_field( $new_instance['submit_button_color'] ) : '';
		$instance['submit_button_text_color']          = isset( $new_instance['submit_button_text_color'] ) ? sanitize_text_field( $new_instance['submit_button_text_color'] ) : '';
		$instance['use_site_submit_button_color']      = isset( $new_instance['use_site_submit_button_color'] ) ? (bool) (int) sanitize_text_field( $new_instance['use_site_submit_button_color'] ) : false;
		$instance['use_site_submit_button_text_color'] = isset( $new_instance['use_site_submit_button_text_color'] ) ? (bool) (int) sanitize_text_field( $new_instance['use_site_submit_button_text_color'] ) : false;

		// Validate that input isn't empty, set default value, if it is.
		$instance['title']                   = ( empty( $instance['title'] ) ) ? '' : $instance['title'];
		$instance['form_layout']             = ( empty( $instance['form_layout'] ) ) ? 'layout-1' : $instance['form_layout'];
		$instance['email_field_placeholder'] = ( empty( $instance['email_field_placeholder'] ) ) ? '' : $instance['email_field_placeholder'];
		$instance['name_field_placeholder']  = ( empty( $instance['name_field_placeholder'] ) ) ? '' : $instance['name_field_placeholder'];
		$instance['submit_button_text']      = ( empty( $instance['submit_button_text'] ) ) ? __( 'Send', 'smaily' ) : $instance['submit_button_text'];

		// If button color isn't set, check default checkbox.
		if ( empty( $instance['submit_button_color'] ) ) {
			$instance['use_site_submit_button_color'] = true;
		}

		// If text color isn't set, check default checkbox.
		if ( empty( $instance['submit_button_text_color'] ) ) {
			$instance['use_site_submit_button_text_color'] = true;
		}

		return $instance;
	}
}
