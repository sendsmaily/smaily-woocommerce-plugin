<?php
/**
 * @package smaily_woocommerce_plugin
 */
namespace Inc\Base;

class Deactivate {

	public static function deactivate() {
		// Flush rewrite rules
		flush_rewrite_rules();
		// Stop Cron
		wp_clear_scheduled_hook( 'smaily_chron_event' );
	}
}
