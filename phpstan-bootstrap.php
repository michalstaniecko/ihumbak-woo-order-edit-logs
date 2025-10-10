<?php
/**
 * PHPStan Bootstrap File
 *
 * This file provides stubs and definitions for WordPress and WooCommerce
 * to help PHPStan analyze the code without having WordPress installed.
 *
 * @package IHumBak\WooOrderEditLogs
 */

// Define WordPress constants if not already defined
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
}

if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
	define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
}

// Mock WordPress database object
if ( ! isset( $GLOBALS['wpdb'] ) ) {
	$GLOBALS['wpdb'] = new stdClass();
	$GLOBALS['wpdb']->prefix = 'wp_';
}
