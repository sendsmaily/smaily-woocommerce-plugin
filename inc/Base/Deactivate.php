<?php
/**
 * @package smaily_for_woocommerce
 */

namespace Smaily_Inc\Base;

/**
 * Deactivates plugin Smaily cron.
 */
class Deactivate {
	/**
	 * Deactivates Smaily plugin.
	 *
	 * @return void
	 */
	public static function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();
		// Stop Cron.
		wp_clear_scheduled_hook( 'smaily_cron_sync_contacts' );
		wp_clear_scheduled_hook( 'smaily_cron_abandoned_carts_email' );
		wp_clear_scheduled_hook( 'smaily_cron_abandoned_carts_status' );
	}
}
