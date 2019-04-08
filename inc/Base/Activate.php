<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

// Required to use dbDelta.
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
/**
 * Responsible for activate plugin Action
 */
class Activate {

	/**
	 * Creates database for plugin configuration.
	 * Creates database for abandoned carts.
	 * Adds cron schedules.
	 * 
	 * @return void
	 */
	public static function activate() {
		// Create both databases.
		self::create_database();
		// Write enable->off to first one.
		self::add_enable();
		// Add Cron job to sync customers.
		if ( ! wp_next_scheduled( 'smaily_cron_sync_contacts' ) ) {
			wp_schedule_event( time(), 'daily', 'smaily_cron_sync_contacts' );
		}
		// Keeping track of abandoned statuses.
		if ( ! wp_next_scheduled( 'smaily_cron_abandoned_carts_status' ) ) {
			wp_schedule_event( time(), 'smaily_15_minutes', 'smaily_cron_abandoned_carts_status' );
		}
		// Sending emails.
		if ( ! wp_next_scheduled( 'smaily_cron_abandoned_carts_email' ) ) {
			wp_schedule_event( time(), 'smaily_15_minutes', 'smaily_cron_abandoned_carts_email' );
		}

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Create databases for plugin
	 *
	 * @return void
	 */
	private static function create_database() {
		global $wpdb;
		// Create smaily settigs table.
		$table_name = $wpdb->prefix . 'smaily';
		$charset_collate = $wpdb->get_charset_collate();
		$smaily     = "CREATE TABLE $table_name (
				id int(11) NOT NULL AUTO_INCREMENT,
				enable tinyint(1) DEFAULT NULL,
				subdomain varchar(255) DEFAULT NULL,
				username varchar(255) DEFAULT NULL,
				password varchar(255) DEFAULT NULL,
				syncronize_additional varchar(255) DEFAULT NULL,
				enable_cart tinyint(1) DEFAULT NULL,
				cart_autoresponder varchar(255) DEFAULT NULL,
				cart_autoresponder_id int(10) DEFAULT NULL,
				cart_cutoff int(10) DEFAULT NULL,
				cart_options varchar(255) DEFAULT NULL,
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
	}

	/**
	 * Marks enable fields to 0.
	 *
	 * @return void
	 */
	private static function add_enable() {
		global $wpdb;
		$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}smaily", 'ARRAY_A' );
		if ( empty( $result ) ) {
			$table_name = $wpdb->prefix . 'smaily';
			$wpdb->insert(
				$table_name,
				array(
					'enable'      => 0,
					'enable_cart' => 0,
				)
			);
		}
	}
}
