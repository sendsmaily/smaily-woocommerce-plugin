<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Widget;

use Smaily_Inc\Base\DataHandler;

/**
 * Handles communication between Smaily API and WordPress
 */
class SmailyWidget extends \WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'smaily_widget', // Base ID.
			esc_html__( 'Smaily Newsletter (WooCommerce)', 'smaily' ), // Name.
			array( 'description' => esc_html__( 'Smaily for WooCommerce Newsletter Widget', 'smaily' ) ) // Args.
		);
	}

	/**
	 * Action hooks for initializing widget
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
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$autoresponder_list = DataHandler::get_autoresponder_list();

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Smaily Newsletter (WooCommerce)', 'smaily' );

		?>
		<!--  Title -->
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
			<?php esc_attr_e( 'Title:', 'smaily' ); ?>
		</label> 
		<input 
			class="widefat" 
			id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
			name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
			type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>

		<!-- Autoresponder -->
		<?php if ( ! empty( $autoresponder_list ) && ! array_key_exists( 'empty', $autoresponder_list ) ) : ?>
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'autoresponder' ) ); ?>">
			<?php esc_attr_e( 'Autoresponder:', 'smaily' ); ?>
		</label>
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
		<?php endif; ?>
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

		$instance['title']         = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['autoresponder'] = ( ! empty( $new_instance['autoresponder'] ) ) ? sanitize_text_field( $new_instance['autoresponder'] ) : '';

		return $instance;
	}

}
