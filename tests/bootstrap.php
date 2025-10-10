<?php
/**
 * PHPUnit Bootstrap File
 *
 * This file is run before the test suite starts.
 * Use it to set up any necessary dependencies or configuration.
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

// Composer autoloader
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// Load PHPUnit Polyfills
if ( file_exists( dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php' ) ) {
	require_once dirname( __DIR__ ) . '/vendor/yoast/phpunit-polyfills/phpunitpolyfills-autoload.php';
}

// Define ABSPATH for plugin compatibility
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

// Define plugin constants
define( 'IHUMBAK_ORDER_LOGS_PATH', dirname( __DIR__ ) . '/' );

// Mock WordPress functions needed for tests
if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
		// Mock implementation - does nothing
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value ) {
		return $value;
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return strip_tags( $str );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $data ) {
		return $data;
	}
}

/**
 * PSR-4 Autoloader for plugin classes (same as in main plugin file).
 *
 * @param string $class The fully-qualified class name.
 */
function ihumbak_order_logs_test_autoloader( $class ) {
	// Project-specific namespace prefix.
	$prefix = 'IHumBak\\WooOrderEditLogs\\';

	// Base directory for the namespace prefix.
	$base_dir = dirname( __DIR__ ) . '/includes/';

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// No, move to the next registered autoloader.
		return;
	}

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Convert namespace separators to directory separators.
	// Convert class name to file name (e.g., Order_Logger to class-order-logger.php).
	$relative_class = str_replace( '\\', '/', $relative_class );
	
	// Split by last slash to get the class name.
	$parts = explode( '/', $relative_class );
	$class_name = array_pop( $parts );
	
	// Convert CamelCase or Snake_Case to kebab-case and add class- prefix.
	$class_name = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', $class_name ) );
	$class_name = str_replace( '_', '-', $class_name );
	$file_name = 'class-' . $class_name . '.php';
	
	// Rebuild the path.
	if ( ! empty( $parts ) ) {
		$relative_path = implode( '/', $parts ) . '/' . $file_name;
	} else {
		$relative_path = $file_name;
	}

	// Build the full file path.
	$file = $base_dir . $relative_path;

	// If the file exists, require it.
	if ( file_exists( $file ) ) {
		require $file;
	}
}

// Register the custom autoloader
spl_autoload_register( 'ihumbak_order_logs_test_autoloader' );

// Note: For full WordPress integration tests, you would typically load WordPress test suite here.
// For now, we'll use basic unit tests that don't require WordPress to be installed.

echo "PHPUnit Bootstrap loaded\n";

