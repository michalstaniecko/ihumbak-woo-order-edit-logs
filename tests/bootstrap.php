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

// Note: For full WordPress integration tests, you would typically load WordPress test suite here.
// For now, we'll use basic unit tests that don't require WordPress to be installed.

echo "PHPUnit Bootstrap loaded\n";
