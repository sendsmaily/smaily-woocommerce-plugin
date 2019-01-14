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
	 * Adds cron schedules.
	 * 
	 * @return void
	 */
	public static function activate() {
		// Create both databases.
		self::create_database();
		// Write enable->off to first one.
		self::add_enable();
		// Flush rewrite rules.
		flush_rewrite_rules();
		// Add Cron job.
		if ( ! wp_next_scheduled( 'smaily_cron_sync_contacts' ) ) {
			wp_schedule_event( time(), 'daily', 'smaily_cron_sync_contacts' );
		}
		if ( ! wp_next_scheduled( 'smaily_cron_abandoned_carts' ) ) {
			wp_schedule_event( time(), 'hourly', 'smaily_cron_abandoned_carts' );
		}
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
		$smaily     = "CREATE TABLE $table_name (
				id int(11) NOT NULL AUTO_INCREMENT,
				enable tinyint(1) DEFAULT NULL,
				subdomain varchar(255) DEFAULT NULL,
				username varchar(255) DEFAULT NULL,
				password varchar(255) DEFAULT NULL,
				autoresponder varchar(255) DEFAULT NULL,
				autoresponder_id int(10) DEFAULT NULL,
				syncronize_additional varchar(255) DEFAULT NULL,
				enable_cart tinyint(1) DEFAULT NULL,
				cart_autoresponder varchar(255) DEFAULT NULL,
				cart_autoresponder_id int(10) DEFAULT NULL,
				cart_delay int(10) DEFAULT NULL,
				cart_options varchar(255) DEFAULT NULL,
				PRIMARY KEY  (id)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1;
				";
		dbDelta( $smaily );

		// Check if column time_created exists in session table.
		$time_created = $wpdb->get_results(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name = '{$wpdb->prefix}woocommerce_sessions' AND column_name = 'time_created'"
		);

		// Add time_created column to woocommerce session table.
		if ( empty( $time_created ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_sessions ADD time_created DATETIME NULL" );
		}

		// Check if column mail_sent exists in session table.
		$mail_sent = $wpdb->get_results(
			"SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			WHERE table_name = '{$wpdb->prefix}woocommerce_sessions' AND column_name = 'mail_sent'"
		);

		// Add mail_sent column to woocommerce session table.
		if ( empty( $mail_sent ) ) {
			$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_sessions ADD mail_sent TINYINT DEFAULT 0" );
		}
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
