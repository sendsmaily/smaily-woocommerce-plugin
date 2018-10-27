<?php
/**
 * @package smaily_woocommerce_plugin
 */
namespace Inc\Base;

// Required to use dbDelta.
require_once ABSPATH . 'wp-admin/includes/upgrade.php';
/**
 * Responsible for activate plugin Action
 */
class Activate {

	/**
	 * Creates 2 databases:
	 * Smaily               - for plugin autoresponder configuration
	 * Smaily_Newsletter    - for handling subscribers
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
	}

	/**
	 * Create databases for plugin
	 *
	 * @return void
	 */
	private static function create_database() {
		global $wpdb;

		$smaily = "
                CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}smaily` (
                `id` int(11) NOT NULL AUTO_INCREMENT ,
                `enable` tinyint(1) DEFAULT NULL,
                `subdomain` varchar(255) DEFAULT NULL,
                `username` varchar(255) DEFAULT NULL,
                `password` varchar(255) DEFAULT NULL,
                `autoresponder` varchar(255) DEFAULT NULL,
                `autoresponder_id` int(10) DEFAULT NULL,
                `syncronize_additional` varchar(255) DEFAULT NULL,
                `syncronize` varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
                ";

		dbDelta( $smaily );

	}

	/**
	 * Marks enable field to 0
	 *
	 * @return void
	 */
	private static function add_enable() {
		global $wpdb;
		$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}smaily", 'ARRAY_A' );
		if ( empty( $result ) ) {
			$table_name = $wpdb->prefix . 'smaily';
			$wpdb->insert( $table_name, array( 'enable' => 0 ) );
		}
	}


}
