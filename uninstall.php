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
$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}smaily`" );
$wpdb->query( "DROP TABLE IF EXISTS `{$wpdb->prefix}smaily_newsletter`" );
