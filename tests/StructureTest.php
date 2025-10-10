<?php
/**
 * Test Plugin Structure
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

namespace IHumBak\WooOrderEditLogs\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class StructureTest
 *
 * Tests basic plugin structure and autoloading.
 */
class StructureTest extends TestCase {

	/**
	 * Test that main plugin file exists.
	 */
	public function test_main_plugin_file_exists() {
		$plugin_file = dirname( dirname( __DIR__ ) ) . '/ihumbak-woo-order-edit-logs.php';
		$this->assertFileExists( $plugin_file );
	}

	/**
	 * Test that uninstall file exists.
	 */
	public function test_uninstall_file_exists() {
		$uninstall_file = dirname( dirname( __DIR__ ) ) . '/uninstall.php';
		$this->assertFileExists( $uninstall_file );
	}

	/**
	 * Test that core class files exist.
	 */
	public function test_core_class_files_exist() {
		$base_dir = dirname( dirname( __DIR__ ) ) . '/includes/';
		
		$required_files = array(
			'class-order-logger.php',
			'class-log-database.php',
			'class-log-tracker.php',
			'class-log-formatter.php',
			'class-log-exporter.php',
			'class-hpos-compatibility.php',
		);

		foreach ( $required_files as $file ) {
			$this->assertFileExists( $base_dir . $file, "Missing file: {$file}" );
		}
	}

	/**
	 * Test that admin class files exist.
	 */
	public function test_admin_class_files_exist() {
		$base_dir = dirname( dirname( __DIR__ ) ) . '/includes/admin/';
		
		$required_files = array(
			'class-admin-interface.php',
			'class-log-viewer.php',
			'class-settings.php',
		);

		foreach ( $required_files as $file ) {
			$this->assertFileExists( $base_dir . $file, "Missing file: {$file}" );
		}
	}

	/**
	 * Test that hook files exist.
	 */
	public function test_hook_files_exist() {
		$base_dir = dirname( dirname( __DIR__ ) ) . '/includes/hooks/';
		
		$required_files = array(
			'order-hooks.php',
			'product-hooks.php',
			'address-hooks.php',
			'payment-hooks.php',
		);

		foreach ( $required_files as $file ) {
			$this->assertFileExists( $base_dir . $file, "Missing file: {$file}" );
		}
	}

	/**
	 * Test that views directory exists.
	 */
	public function test_views_directory_exists() {
		$views_dir = dirname( dirname( __DIR__ ) ) . '/includes/admin/views/';
		$this->assertDirectoryExists( $views_dir );
	}
}
