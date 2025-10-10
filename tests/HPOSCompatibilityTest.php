<?php
/**
 * Test HPOS Compatibility Declaration
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

namespace IHumBak\WooOrderEditLogs\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class HPOSCompatibilityTest
 *
 * Tests HPOS compatibility declaration in the main plugin file.
 */
class HPOSCompatibilityTest extends TestCase {

	/**
	 * Test that HPOS compatibility declaration function exists.
	 */
	public function test_hpos_compatibility_function_exists() {
		$plugin_file = dirname( __DIR__ ) . '/ihumbak-woo-order-edit-logs.php';
		$content     = file_get_contents( $plugin_file );

		// Check that the function exists.
		$this->assertStringContainsString(
			'function ihumbak_order_logs_declare_hpos_compatibility()',
			$content,
			'HPOS compatibility function not found'
		);
	}

	/**
	 * Test that FeaturesUtil is used to declare compatibility.
	 */
	public function test_features_util_declaration() {
		$plugin_file = dirname( __DIR__ ) . '/ihumbak-woo-order-edit-logs.php';
		$content     = file_get_contents( $plugin_file );

		// Check for FeaturesUtil usage.
		$this->assertStringContainsString(
			'FeaturesUtil::declare_compatibility',
			$content,
			'FeaturesUtil::declare_compatibility not found'
		);
	}

	/**
	 * Test that the custom_order_tables feature is declared.
	 */
	public function test_custom_order_tables_feature() {
		$plugin_file = dirname( __DIR__ ) . '/ihumbak-woo-order-edit-logs.php';
		$content     = file_get_contents( $plugin_file );

		// Check for custom_order_tables feature.
		$this->assertStringContainsString(
			"'custom_order_tables'",
			$content,
			'custom_order_tables feature not declared'
		);
	}

	/**
	 * Test that the before_woocommerce_init hook is used.
	 */
	public function test_before_woocommerce_init_hook() {
		$plugin_file = dirname( __DIR__ ) . '/ihumbak-woo-order-edit-logs.php';
		$content     = file_get_contents( $plugin_file );

		// Check for before_woocommerce_init hook.
		$this->assertStringContainsString(
			"add_action( 'before_woocommerce_init', 'ihumbak_order_logs_declare_hpos_compatibility' )",
			$content,
			'before_woocommerce_init hook not found'
		);
	}

	/**
	 * Test that compatibility is declared as true.
	 */
	public function test_compatibility_declared_true() {
		$plugin_file = dirname( __DIR__ ) . '/ihumbak-woo-order-edit-logs.php';
		$content     = file_get_contents( $plugin_file );

		// Find the declare_compatibility call.
		$pattern = '/declare_compatibility\s*\(\s*[\'"]custom_order_tables[\'"]\s*,\s*__FILE__\s*,\s*true\s*\)/';
		$this->assertMatchesRegularExpression(
			$pattern,
			$content,
			'Compatibility not declared as true'
		);
	}
}
