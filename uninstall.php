<?php
/**
 * Uninstall Script
 *
 * This file is executed when the plugin is uninstalled.
 *
 * @package IHumBak\WooOrderEditLogs
 */

// Exit if accessed directly or not uninstalling.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete plugin options.
 */
function ihumbak_order_logs_delete_options() {
	delete_option( 'ihumbak_order_logs_version' );
	delete_option( 'ihumbak_order_logs_db_version' );
	delete_option( 'ihumbak_order_logs_settings' );
	delete_option( 'ihumbak_order_logs_retention_days' );
	delete_option( 'ihumbak_order_logs_delete_on_uninstall' );
}

/**
 * Delete plugin database tables.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 */
function ihumbak_order_logs_delete_tables() {
	global $wpdb;

	$table_name = $wpdb->prefix . 'ihumbak_order_logs';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange
	$wpdb->query( "DROP TABLE IF EXISTS {$table_name}" );
}

/**
 * Main uninstall routine.
 */
function ihumbak_order_logs_uninstall() {
	// Check if user wants to delete data on uninstall.
	$delete_data = get_option( 'ihumbak_order_logs_delete_on_uninstall', false );

	if ( $delete_data ) {
		// Delete database tables.
		ihumbak_order_logs_delete_tables();
	}

	// Always delete options.
	ihumbak_order_logs_delete_options();
}

// Run the uninstall routine.
ihumbak_order_logs_uninstall();
