<?php

namespace Smaily_Inc;

/**
 * Define all the logic related to plugin activation, upgrade and uninstall logic.
 */
class Lifecycle {
	/**
	 * Action hooks for initializing plugin.
	 *
	 * @return void
	 */
	public function register() {
		register_activation_hook( SMAILY_PLUGIN_FILE, array( $this, 'activate' ) );
		register_deactivation_hook( SMAILY_PLUGIN_FILE, array( $this, 'deactivate' ) );
		register_uninstall_hook( SMAILY_PLUGIN_FILE, array( 'Smaily_Inc\Lifecycle', 'uninstall' ) );
		add_action( 'plugins_loaded', array( $this, 'update' ) );
		add_action( 'upgrader_process_complete', array( $this, 'check_for_update' ), 10, 2 );
	}

	/**
	 * Callback for plugin activation hook.
	 */
	public function activate() {
		$this->run_migrations();

		// Add Cron job to sync customers.
		wp_schedule_event( time(), 'daily', 'smaily_cron_sync_contacts' );

		// Keeping track of abandoned statuses.
		wp_schedule_event( time(), 'smaily_15_minutes', 'smaily_cron_abandoned_carts_status' );

		// Sending emails.
		wp_schedule_event( time(), 'smaily_15_minutes', 'smaily_cron_abandoned_carts_email' );

		// Flush rewrite rules.
		flush_rewrite_rules();
	}

	/**
	 * Callback for plugin deactivation hook.
	 */
	public function deactivate() {
		// Flush rewrite rules.
		flush_rewrite_rules();
		// Stop Cron.
		wp_clear_scheduled_hook( 'smaily_cron_sync_contacts' );
		wp_clear_scheduled_hook( 'smaily_cron_abandoned_carts_email' );
		wp_clear_scheduled_hook( 'smaily_cron_abandoned_carts_status' );
	}

	/**
	 * Callback for plugins_loaded hook.
	 *
	 * Start migrations if plugin was updated.
	 */
	public function update() {
		if ( get_transient( 'smailyforwc_plugin_updated' ) !== true ) {
			return;
		}
		$this->run_migrations();
		delete_transient( 'smailyforwc_plugin_updated' );
	}

	/**
	 * Callback for plugin uninstall hook.
	 *
	 * Clean up plugin's database entities.
	 */
	public static function uninstall() {
		global $wpdb;
		// Delete Smaily plugin settings table.
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily" );
		// Delete Smaily plugin abandoned cart table.
		$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_abandoned_carts" );
		delete_option( 'widget_smaily_widget' );
		delete_option( 'smailyforwc_db_version' );
		delete_transient( 'smailyforwc_plugin_updated' );
	}

	/**
	 * Callback for upgrader_process_complete hook.
	 *
	 * Check if our plugin was updated, make a transient option if so.
	 * This alows us to trigger a DB upgrade script if necessary.
	 *
	 * @param Plugin_Upgrader $upgrader_object Instance of WP_Upgrader.
	 * @param array           $options         Array of bulk item update data.
	 */
	public function check_for_update( $upgrader_object, $options ) {
		$smaily_basename = plugin_basename( SMAILY_PLUGIN_FILE );

		$plugin_was_updated = $options['action'] === 'update' && $options['type'] === 'plugin';
		if ( ! isset( $options['plugins'] ) || ! $plugin_was_updated ) {
			return;
		}

		// $options['plugins'] is string during single update, array if multiple plugins updated.
		$updated_plugins = (array) $options['plugins'];

		foreach ( $updated_plugins as $plugin_basename ) {
			if ( $smaily_basename === $plugin_basename ) {
				return set_transient( 'smailyforwc_plugin_updated', true );
			}
		}
	}

	/**
	 * Get plugin's DB version, run any migrations the database requires.
	 * Update DB version with current plugin version.
	 *
	 * @access private
	 */
	private function run_migrations() {
		$plugin_version = SMAILY_PLUGIN_VERSION;
		$db_version     = get_option( 'smailyforwc_db_version', '0.0.0' );

		if ( $plugin_version === $db_version ) {
			return;
		}

		$migrations = array(
			'1.0.0' => 'upgrade-1-0-0.php',
			'1.8.0' => 'upgrade-1-8-0.php',
		);

		foreach ( $migrations as $migration_version => $migration_file ) {
			// Database is up-to-date with plugin version.
			if ( version_compare( $db_version, $migration_version, '>=' ) ) {
				continue;
			}

			$migration_file = SMAILY_PLUGIN_PATH . 'migrations/' . $migration_file;
			if ( ! file_exists( $migration_file ) ) {
				continue;
			}

			$upgrade = null;
			require_once $migration_file;
			if ( is_callable( $upgrade ) ) {
				$upgrade();
			}
		}

		// Migrations finished.
		update_option( 'smailyforwc_db_version', $plugin_version );
	}
}
