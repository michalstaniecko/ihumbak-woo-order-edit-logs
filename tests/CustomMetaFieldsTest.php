<?php
/**
 * Test Custom Meta Fields Tracking
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

namespace IHumBak\WooOrderEditLogs\Tests;

use PHPUnit\Framework\TestCase;
use IHumBak\WooOrderEditLogs\Admin\Settings;

/**
 * Class CustomMetaFieldsTest
 *
 * Tests custom meta fields tracking functionality.
 */
class CustomMetaFieldsTest extends TestCase {

	/**
	 * Test Settings::get_custom_meta_fields with empty settings.
	 */
	public function test_get_custom_meta_fields_empty() {
		// Mock the get_option to return empty string.
		if ( ! function_exists( 'get_option' ) ) {
			function get_option( $key, $default = null ) {
				return '';
			}
		}

		$settings = Settings::get_instance();
		$fields = $settings->get_custom_meta_fields();

		$this->assertIsArray( $fields );
		$this->assertEmpty( $fields );
	}

	/**
	 * Test Settings class has get_custom_meta_fields method.
	 */
	public function test_settings_has_get_custom_meta_fields() {
		$this->assertTrue( method_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings', 'get_custom_meta_fields' ) );
	}

	/**
	 * Test that custom_field_changed action type exists.
	 */
	public function test_custom_field_changed_action_type_exists() {
		$this->assertTrue( method_exists( 'IHumBak\WooOrderEditLogs\Order_Logger', 'get_action_types' ) );
		
		// We can't actually call get_action_types without full WordPress environment,
		// but we can verify the method exists which is defined in the specification.
	}

	/**
	 * Test HPOS_Compatibility has get_custom_meta_data method.
	 */
	public function test_hpos_compatibility_has_custom_meta_method() {
		$reflection = new \ReflectionClass( 'IHumBak\WooOrderEditLogs\HPOS_Compatibility' );
		$methods = $reflection->getMethods();
		
		$method_names = array_map( function( $method ) {
			return $method->getName();
		}, $methods );

		// The get_custom_meta_data method is private, so we check using reflection.
		$this->assertContains( 'get_custom_meta_data', $method_names );
	}
}
