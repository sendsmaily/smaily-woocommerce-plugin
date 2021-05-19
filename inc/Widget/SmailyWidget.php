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
	 * @see WP_Widget::widget().
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
		$params = array( 'code', 'message' );
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
					<label>' . esc_html__( 'Email', 'smaily' ) .'</label>
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
	 * @see WP_Widget::form().
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$autoresponder_list = DataHandler::get_autoresponder_list();

		$title                             = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Smaily for WooCommerce Form', 'smaily' );
		$smaily_layout                     = ! empty( $instance['smaily_layout'] ) ? $instance['smaily_layout'] : esc_html__( '', 'smaily' );
		$email_title                       = ! empty( $instance['email_title'] ) ? $instance['email_title'] : esc_html__( 'Email', 'smaily' );
		$name_title                        = ! empty( $instance['name_title'] ) ? $instance['name_title'] : esc_html__( 'Name', 'smaily' );
		$button_title                      = ! empty( $instance['button_title'] ) ? $instance['button_title'] : esc_html__( 'Send', 'smaily' );
		$button_color                      = ! empty( $instance['button_color'] ) ? $instance['button_color'] : esc_html__( '', 'smaily' );
		$button_text_color                 = ! empty( $instance['button_text_color'] ) ? $instance['button_text_color'] : esc_html__( '', 'smaily' );
		$smaily_default_background_color   = ! empty( $instance['smaily_default_background_color'] ) ? $instance['smaily_default_background_color'] : esc_html__( '', 'smaily' );
		$smaily_default_text_color         = ! empty( $instance['smaily_default_text_color'] ) ? $instance['smaily_default_text_color'] : esc_html__( '', 'smaily' );

		?>

		<div class="smaily">
			<!-- Title. -->
			<div class="section">
				<h2>
					<?php esc_attr_e( 'Title', 'smaily' ); ?>
				</h2>
				<input
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
					type="text" value="<?php echo esc_attr( $title ); ?>">
			</div>

			<!-- Autoresponder. -->
			<?php if ( ! empty( $autoresponder_list ) && ! array_key_exists( 'empty', $autoresponder_list ) ) : ?>
			<div class="section">
				<h2>
					<?php esc_attr_e( 'Autoresponder', 'smaily' ); ?>
				</h2>
				<select id="<?php echo esc_attr( $this->get_field_id( 'autoresponder' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'autoresponder' ) ); ?>" class="widefat" style="width:100%;">
					<?php
					// Show selected autoresponder.
					if ( ! empty( $instance['autoresponder'] ) ) {
						$current_autoresponder = json_decode( $instance['autoresponder'], true );
						echo '<option value="' . $instance['autoresponder'] . '">' . $current_autoresponder['name'] . '</option>';
					}
					// Show all autoresponders from Smaily.
					if ( ! empty( $autoresponder_list ) && ! array_key_exists( 'empty', $autoresponder_list ) ) {
						echo '<option value="">' . esc_html__( '-No Autoresponder-', 'smaily' ) . ' </option>';
						foreach ( $autoresponder_list as $autoresponder ) {
							echo '<option value="' . htmlentities( json_encode( $autoresponder ) ) . '">' . $autoresponder['name'] . '</option>';
						}
					}
					?>
				</select>
				<p>
					<?php esc_attr_e( 'Select a suitable form-submitted trigger automation workflow from the list or create a new autoresponder on your Smaily account to be added to the list.', 'smaily' ); ?>
				</p>
			</div>
			<?php endif; ?>

			<!-- Write layout outside of autoresponders, so it would be always displayed, regardless autoresponders are created or not. -->
			<!-- Layouts. -->
			<div class="section">
				<h2>
					<?php esc_attr_e( 'Layout', 'smaily' ); ?>
				</h2>
				<p>
					<?php esc_attr_e( 'Choose a preferred layout.', 'smaily' ); ?>
				</p>

				<div class="smaily-layout-container">
					<p> <!-- Make the selected layout radiobutton active. -->
						<input class="radio" id="<?php echo $this->get_field_id( 'smaily-layout-1' ); ?>" name="<?php echo $this->get_field_name('smaily_layout'); ?>"
						type="radio" value="smaily-layout-1" <?php if($smaily_layout === 'smaily-layout-1'){ echo 'checked="checked"'; } ?> />
						<label for="<?php echo $this->get_field_id( 'smaily-layout-1' ); ?>">
						<strong>Email (Layout 1)</strong></label>
					</p>
					<p>
						<img src="<?php echo SMAILY_PLUGIN_URL . "static/layouts/email-layout1.svg"; ?>" alt="smaily layout 1" class="smaily-layouts">
					</p>
				</div>
				<div class="smaily-layout-container">
					<p>
						<input class="radio" id="<?php echo $this->get_field_id( 'smaily-layout-2' ); ?>" name="<?php echo $this->get_field_name('smaily_layout'); ?>"
						type="radio" value="smaily-layout-2" <?php if($smaily_layout === 'smaily-layout-2'){ echo 'checked="checked"'; } ?> />
						<label for="<?php echo $this->get_field_id( 'smaily-layout-2' ); ?>">
						<strong>Email (Layout 2)</strong></label>
					</p>
					<p>
						<img src="<?php echo SMAILY_PLUGIN_URL . "static/layouts/email-layout2.svg"; ?>" alt="smaily layout 2" class="smaily-layouts">
					</p>
				</div>
				<div class="smaily-layout-container">
					<p>
						<input class="radio" id="<?php echo $this->get_field_id( 'smaily-layout-3' ); ?>" name="<?php echo $this->get_field_name('smaily_layout'); ?>"
						type="radio" value="smaily-layout-3" <?php if($smaily_layout === 'smaily-layout-3'){ echo 'checked="checked"'; } ?> />
						<label for="<?php echo $this->get_field_id( 'smaily-layout-3' ); ?>">
						<strong>Email (Layout 3)</strong></label>
					</p>
					<p>
						<img src="<?php echo SMAILY_PLUGIN_URL . "static/layouts/email-layout3.svg"; ?>" alt="smaily layout 3" class="smaily-layouts">
					</p>
				</div>
				<div class="smaily-layout-container">
					<p>
						<input class="radio" id="<?php echo $this->get_field_id( 'smaily-layout-4' ); ?>" name="<?php echo $this->get_field_name('smaily_layout'); ?>"
						type="radio" value="smaily-layout-4" <?php if($smaily_layout === 'smaily-layout-4'){ echo 'checked="checked"'; } ?> />
						<label for="<?php echo $this->get_field_id( 'smaily-layout-4' ); ?>">
						<strong>Email (Layout 4)</strong></label>
					</p>
					<p>
						<img src="<?php echo SMAILY_PLUGIN_URL . "static/layouts/email-layout4.svg"; ?>" alt="smaily layout 4" class="smaily-layouts">
					</p>
				</div>
				<div class="smaily-layout-container">
					<p>
						<input class="radio" id="<?php echo $this->get_field_id( 'smaily-layout-5' ); ?>" name="<?php echo $this->get_field_name('smaily_layout'); ?>"
						type="radio" value="smaily-layout-5" <?php if($smaily_layout === 'smaily-layout-5'){ echo 'checked="checked"'; } ?> />
						<label for="<?php echo $this->get_field_id( 'smaily-layout-5' ); ?>">
						<strong>Email (Layout 5)</strong></label>
					</p>
					<p>
						<!-- Read URL starting from the static folder. Doesn`t matter where in your computer plugin is located. -->
						<img src="<?php echo SMAILY_PLUGIN_URL . "static/layouts/email-layout5.svg"; ?>" alt="smaily layout 5" class="smaily-layouts">
					</p>
				</div>
			</div>

			<!-- Customize layout. -->
			<div class="section">
				<h2>
					<!-- Add layout customization section. -->
					<?php esc_attr_e( 'Customize layout', 'smaily' ); ?> 
				</h2>

				<!-- Email field placeholder. -->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'email_title' ) ); ?>">
						<b><?php esc_attr_e( 'Email field text', 'smaily' ); ?></b>
					</label>
					<input
						class="widefat"
						id="<?php echo esc_attr( $this->get_field_id( 'email_title' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'email_title' ) ); ?>"
						type="text" value="<?php echo esc_attr( $email_title ); ?>">
				</p>

				<!-- Name field placeholder. -->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'name_title' ) ); ?>">
						<b><?php esc_attr_e( 'Name field text', 'smaily' ); ?></b>
					</label>
					<input
						class="widefat"
						id="<?php echo esc_attr( $this->get_field_id( 'name_title' ) ); ?>"
						name="<?php echo esc_attr( $this->get_field_name( 'name_title' ) ); ?>"
						type="text" value="<?php echo esc_attr( $name_title ); ?>">
				</p>

				<!-- Button field text. -->
				<p>
					<label for="<?php echo esc_attr( $this->get_field_id( 'button_title' ) ); ?>">
						<b><?php esc_attr_e( 'Text on the button', 'smaily' ); ?></b>
					</label>
				<input
					class="widefat"
					id="<?php echo esc_attr( $this->get_field_id( 'button_title' ) ); ?>"
					name="<?php echo esc_attr( $this->get_field_name( 'button_title' ) ); ?>"
					type="text" value="<?php echo esc_attr( $button_title ); ?>" >
				</p>

				<!-- Customize button color and text. -->
				<div class="row">
					<div class="column" id="button-color-container">
						<!-- Add color picker HTML. -->
						<div>
							<label for="<?php echo esc_attr( $this->get_field_id( 'button_color' ) ); ?>">
								<b><?php esc_attr_e( 'Button color', 'smaily' ); ?></b>
							</label>
							<!-- Jscolor required:false is used to clear the input value. -->
							<input 
								data-jscolor= "{required:false}"
								class="button-color" 
								disabled
								id="<?php echo esc_attr( $this->get_field_id( 'button_color' ) ); ?>"
								name="<?php echo esc_attr( $this->get_field_name( 'button_color' ) ); ?>"
								type="text" 
								value="<?php echo esc_attr( $button_color ); ?>" >
						</div>
						<div class="default-value-checkbox">
							<input 
								class="smaily-checkbox default_background_color" 
								checked 
								id="<?php echo $this->get_field_id( 'default_background_color' ); ?>"
								name="<?php echo $this->get_field_name( 'smaily_default_background_color' ); ?>" 
								type="checkbox" 
								value="default_background_color">
							<label for="<?php echo $this->get_field_id( 'smaily_default_background_color' ); ?>" >Use default button color? </label>
						</div>
					</div>
					
					<div class="column" id="button-text-container">
						<div>
							<label for="<?php echo esc_attr( $this->get_field_id( 'button_text_color' ) ); ?>">
								<b><?php esc_attr_e( 'Button text color', 'smaily' ); ?></b>
							</label>
							<!--Jscolor required: false is used to clear the input value. -->
							<input 
								data-jscolor="{required:false}"
								class="button-text-color" type="text" 
								disabled
								id="<?php echo esc_attr( $this->get_field_id( 'button_text_color' ) ); ?>"
								name="<?php echo esc_attr( $this->get_field_name( 'button_text_color' ) ); ?>"
								value="<?php echo esc_attr( $button_text_color ); ?>" >
						</div>
						<div class="default-value-checkbox">
							<input 
								class="smaily-checkbox default_text_color" 
								checked 
								id="<?php echo $this->get_field_id( 'default_text_color' ); ?>"
								name="<?php echo $this->get_field_name( 'smaily_default_text_color' ); ?>" 
								type="checkbox" 
								value="default_text_color">
							<label for="<?php echo $this->get_field_id( 'smaily_default_text_color' ); ?>" >Use default text color? </label>
						</div>
					</div>
				</div>
			</div>
		</div>

		<script>
        // Run jQuery in no-conflict mode for WP development.
        jQuery( document ).ready(function($) {

			// Initate jscolor.install on widget-added.
			$( document ).on( 'widget-added', function ( event, widget ) {
				// Load jscolor.
				jscolor.install();
            });

            // Initate jscolor.install and input state check on widget update(save.
            $( document ).on( 'widget-updated', function ( event, widget ) {
				// Load jscolor.
                jscolor.install();
				
				// If button color is not empty and default color is not checked -> then input isn`t disabled.
				if ( "<?php echo $button_color; ?>" != "" && "<?php echo $smaily_default_background_color; ?>" == "" ) {
					$( 'div#button-color-container input[type="text"]' ).prop( "disabled", false );
					$( 'div#button-color-container input[type="checkbox"]' ).prop( "checked", false );
				} else {
					$( 'div#button-color-container input[type="text"]' ).prop( "disabled", true );
					$( 'div#button-color-container input[type="checkbox"]' ).prop( "checked", true );
				} 
				
				if ( "<?php echo $button_text_color; ?>" != "" && "<?php echo $smaily_default_text_color; ?>" == "" ) {
					$( 'div#button-text-container input[type="text"]' ).prop( "disabled", false );
					$( 'div#button-text-container input[type="checkbox"]' ).prop( "checked", false );
				} else {
					$( 'div#button-text-container input[type="text"]' ).prop( "disabled", true );
					$( 'div#button-text-container input[type="checkbox"]' ).prop( "checked", true );
				}

            });

			/* This check happens all the time, not only on update or add.
               If background color checkbox is checked, then disable background color input. */
            $( document ).on('change','.default_background_color', function (  ) {
				$( 'div#button-color-container input[type="text"]' ).prop( "disabled", this.checked ? true : false );
				console.log(this.value);
            });
            // If text color checkbox is checked, then disable text color input.
			$( document ).on('change','.default_text_color', function (  ) {
				$( 'div#button-text-container input[type="text"]' ).prop( "disabled", this.checked ? true : false );
				console.log(this.value);
            });


			/* After page refresh, check if button color value has been chosen. If yes, then remove check from checkbox and enable button color input field.
			   This check is done, because otherwise hardcoded HTML properties force over saved values. */
			if ( "<?php echo $button_color; ?>" != "" && "<?php echo $smaily_default_background_color; ?>" == "" ) {
				$( 'div#button-color-container input[type="text"]' ).prop( "disabled", false );
				$( 'div#button-color-container input[type="checkbox"]' ).prop( "checked", false );
			} else {
				$( 'div#button-color-container input[type="text"]' ).prop( "disabled", true );
				$( 'div#button-color-container input[type="checkbox"]' ).prop( "checked", true );
			} 

			/* After page refresh, check if text color value has been chosen. If yes, then remove check from checkbox and enable button text color input field.
			   This check is done, because otherwise hardcoded HTML properties force over saved values. */
			if ( "<?php echo $button_text_color; ?>" != "" && "<?php echo $smaily_default_text_color; ?>" == "" ) {
				$( 'div#button-text-container input[type="text"]' ).prop( "disabled", false );
				$( 'div#button-text-container input[type="checkbox"]' ).prop( "checked", false );
			} else {
				$( 'div#button-text-container input[type="text"]' ).prop( "disabled", true );
				$( 'div#button-text-container input[type="checkbox"]' ).prop( "checked", true );
			}
        });
		</script>

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

		$instance['title']                             = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['smaily_layout']                     = ( ! empty( $new_instance['smaily_layout'] ) ) ? sanitize_text_field( $new_instance['smaily_layout'] ) : '';
		$instance['autoresponder']                     = ( ! empty( $new_instance['autoresponder'] ) ) ? sanitize_text_field( $new_instance['autoresponder'] ) : '';
		$instance['email_title']                       = ( ! empty( $new_instance['email_title'] ) ) ? sanitize_text_field( $new_instance['email_title'] ) : '';
		$instance['name_title']                        = ( ! empty( $new_instance['name_title'] ) ) ? sanitize_text_field( $new_instance['name_title'] ) : '';
		$instance['button_title']                      = ( ! empty( $new_instance['button_title'] ) ) ? sanitize_text_field( $new_instance['button_title'] ) : '';
		$instance['button_color']                      = ( ! empty( $new_instance['button_color'] ) ) ? sanitize_text_field( $new_instance['button_color'] ) : sanitize_text_field( $old_instance['button_color'] );
		$instance['button_text_color']                 = ( ! empty( $new_instance['button_text_color'] ) ) ? sanitize_text_field( $new_instance['button_text_color'] ) : sanitize_text_field( $old_instance['button_text_color'] );
		$instance['smaily_default_background_color']   = ( ! empty( $new_instance['smaily_default_background_color'] ) ) ? sanitize_text_field( $new_instance['smaily_default_background_color'] ) : '';
		$instance['smaily_default_text_color']         = ( ! empty( $new_instance['smaily_default_text_color'] ) ) ? sanitize_text_field( $new_instance['smaily_default_text_color'] ) : '';

		return $instance;
	}

}
