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
// Delete Smaily plugin settings table.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily" );
// Delete Smaily plugin abandoned cart table.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}smaily_abandoned_carts");
