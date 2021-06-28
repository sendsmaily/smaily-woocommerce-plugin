<?php

/**
 * Migration to create Smaily settings and abandoned cart tables.
 */

// Required to use dbDelta.
require_once ABSPATH . 'wp-admin/includes/upgrade.php';

$upgrade = function() {
	global $wpdb;

	// Create smaily settigs table.
	$table_name      = $wpdb->prefix . 'smaily';
	$charset_collate = $wpdb->get_charset_collate();
	$smaily          = "CREATE TABLE $table_name (
		id int(11) NOT NULL AUTO_INCREMENT,
		enable tinyint(1) DEFAULT 0,
		subdomain varchar(255) DEFAULT NULL,
		username varchar(255) DEFAULT NULL,
		password varchar(255) DEFAULT NULL,
		syncronize_additional varchar(255) DEFAULT NULL,
		enable_cart tinyint(1) DEFAULT 0,
		cart_autoresponder varchar(255) DEFAULT NULL,
		cart_autoresponder_id int(10) DEFAULT NULL,
		cart_cutoff int(10) DEFAULT NULL,
		cart_options varchar(255) DEFAULT NULL,
		enable_checkbox tinyint(1) DEFAULT 0,
		checkbox_auto_checked tinyint(1) DEFAULT 0,
		checkbox_order varchar(255) DEFAULT 'after',
		checkbox_location varchar(255) DEFAULT 'checkout_billing_form',
		rss_limit smallint(3) DEFAULT 50,
		rss_category varchar(255) DEFAULT '',
		rss_order_by varchar(255) DEFAULT 'modified',
		rss_order varchar(255) DEFAULT 'DESC',
		PRIMARY KEY  (id)
	) $charset_collate;";
	dbDelta( $smaily );

	// Create smaily_abandoned_cart table.
	$abandoned_table_name = $wpdb->prefix . 'smaily_abandoned_carts';
	$abandoned            = "CREATE TABLE $abandoned_table_name (
		customer_id int(11) NOT NULL,
		cart_updated datetime DEFAULT '0000-00-00 00:00:00',
		cart_content longtext DEFAULT NULL,
		cart_status varchar(255) DEFAULT NULL,
		cart_abandoned_time datetime DEFAULT '0000-00-00 00:00:00',
		mail_sent tinyint(1) DEFAULT NULL,
		mail_sent_time datetime DEFAULT '0000-00-00 00:00:00',
		PRIMARY KEY  (customer_id)
	) $charset_collate;";
	dbDelta( $abandoned );

	// Write defaults.
	$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}smaily", 'ARRAY_A' );
	if ( empty( $result ) ) {
		$table_name = $wpdb->prefix . 'smaily';
		$wpdb->insert(
			$table_name,
			array(
				'enable'                => 0,
				'enable_cart'           => 0,
				'enable_checkbox'       => 0,
				'checkbox_auto_checked' => 0,
				'checkbox_order'        => 'after',
				'checkbox_location'     => 'checkout_billing_form',
			)
		);
	}
};
