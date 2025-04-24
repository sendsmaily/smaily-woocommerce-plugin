<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Widget;

class Register {
	/**
	 * Register plugin widgets.
	 *
	 * @return void
	 */
	public function register() {
		add_action( 'widgets_init', array( $this, 'register_widgets' ) );
	}

	/**
	 * Register the Smaily widget.
	 *
	 * @return void
	 */
	public function register_widgets() {
		register_widget( SmailyWidget::class );
	}
}
