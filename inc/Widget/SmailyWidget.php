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
			esc_html__( 'Smaily Newsletter', 'smaily_widget' ), // Name.
			array( 'description' => esc_html__( 'WooCommerce Smaily Newsletter Widget', 'smaily_widget' ) ) // Args.
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

		// Get current url.
		$current_url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		// Widget front-end.
		if ( isset( $_GET['message'] ) || isset( $_GET['code'] ) ) {
			echo '
				<div class="smaily-newsletter-alert">
				<p>' . esc_html( $_GET['message'] ) . '
				<span class="smaily-newsletter-closebtn" onclick="this.parentElement.style.display=\'none\'">&times;</span>
				</p>
				</div>
				<form class="smaily-newsletter-form" action="https://' . $result['subdomain'] . '.sendsmaily.net/api/opt-in/" method="post" autocomplete="off">
					<div>
					<input type="hidden" name="key" value="' . esc_html( $instance['api_key'] ) . '" />
					<input type="hidden" name="autoresponder" value="' . $result['autoresponder_id'] . '" />
					<input type="hidden" name="success_url" value="' . esc_url( $current_url ) . '" />
					<input type="hidden" name="failure_url" value="' . esc_url( $current_url ) . '" />
					</div>
					<p>
						<label>Email</label>
						<input type="text" name="email" value="" />
					</p>
					<p>
						<label>Name</label>
						<input type="text" name="name" value="" />
					</p>
					<p>
						<button class="ui pink basic button" type="submit">Subscribe</button>
					</p>
					<div style="overflow:hidden;height:0px;">
						<input type="text" name="re-email" value="" />
					</div>
				</form>
				';
		} else {
			echo '
			<form class="smaily-newsletter-form" action="https://' . $result['subdomain'] . '.sendsmaily.net/api/opt-in/" method="post" autocomplete="off">
				<div>
				<input type="hidden" name="key" value="' . esc_html( $instance['api_key'] ) . '" />
				<input type="hidden" name="autoresponder" value="' . $result['autoresponder_id'] . '" />
				<input type="hidden" name="success_url" value="' . esc_url( $current_url ) . '" />
				<input type="hidden" name="failure_url" value="' . esc_url( $current_url ) . '" />
				</div>
				<p>
					<label>Email</label>
					<input type="text" name="email" value="" />
				</p>
				<p>
					<label>Name</label>
					<input type="text" name="name" value="" />
				</p>
				<p>
					<button class="ui pink basic button" type="submit">Subscribe</button>
				</p>
				<div style="overflow:hidden;height:0px;">
					<input type="text" name="re-email" value="" />
				</div>
			</form>
			';
		}

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

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Smaily Newsletter', 'smaily_widget' );

		$api_key = ! empty( $instance['api_key'] ) ? $instance['api_key'] : esc_html__( 'Please insert API key', 'smaily_widget' );

		?>
		<!--  Title -->
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
			<?php esc_attr_e( 'Title:', 'smaily_widget' ); ?>
		</label> 
		<input 
			class="widefat" 
			id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
			name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
			type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>


		<!-- API Key -->
		<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'api_key' ) ); ?>">
			<?php esc_attr_e( 'API Key:', 'smaily_widget' ); ?>
		</label> 
		<input 
			class="widefat" 
			id="<?php echo esc_attr( $this->get_field_id( 'api_key' ) ); ?>" 
			name="<?php echo esc_attr( $this->get_field_name( 'api_key' ) ); ?>" 
			type="text" value="<?php echo esc_attr( $api_key ); ?>">
		</p>
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

		$instance['title']   = ( ! empty( $new_instance['title'] ) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['api_key'] = ( ! empty( $new_instance['api_key'] ) ) ? sanitize_text_field( $new_instance['api_key'] ) : '';

		return $instance;
	}

}
