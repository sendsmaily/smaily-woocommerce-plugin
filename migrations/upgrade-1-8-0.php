<?php

/**
 * Migration to create Smaily widget settings.
 */

$upgrade = function() {

	$widgets = get_option( 'widget_smaily_widget', array() ); // Array sets empty default value.
	foreach ( $widgets as &$widget ) {
		if ( ! is_array( $widget ) ) {
			continue;
		}
		$new_settings = array(
			'form_layout'                       => 'layout-5',
			'email_field_placeholder'           => __( 'Email', 'smaily' ),
			'name_field_placeholder'            => __( 'Name', 'smaily' ),
			'submit_button_text'                => __( 'Subscribe', 'smaily' ),
			'submit_button_color'               => '',
			'submit_button_text_color'          => '',
			'use_site_submit_button_color'      => true,
			'use_site_submit_button_text_color' => true,
		);
		// Apply new settings to existing widgets.
		$widget = array_merge( $widget, $new_settings );
	}

	// Update only if $widgets has values.
	if ( ! empty( $widgets ) ) {
		update_option( 'widget_smaily_widget', $widgets );
	}
};
