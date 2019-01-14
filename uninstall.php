<?php
/**
 * Trigger this file when uninstalling Smaily extension
 *
 * @package Smaily
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	die;
}

global $wpdb;
// Delete Smaily plugin Databases.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily" );
// Delete time_created row from sessions table.
$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_sessions DROP time_created" );
// Delete mail_sent row from sessions table.
$wpdb->query( "ALTER TABLE {$wpdb->prefix}woocommerce_sessions DROP mail_sent" );
