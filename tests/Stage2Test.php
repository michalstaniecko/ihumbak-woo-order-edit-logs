<?php
/**
 * Test Stage 2 Implementation
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

namespace IHumBak\WooOrderEditLogs\Tests;

use PHPUnit\Framework\TestCase;
use IHumBak\WooOrderEditLogs\Log_Tracker;
use IHumBak\WooOrderEditLogs\Log_Formatter;
use IHumBak\WooOrderEditLogs\Order_Logger;

/**
 * Class Stage2Test
 *
 * Tests Stage 2 implementation: Log Tracker, Log Formatter, and Order Logger enhancements.
 */
class Stage2Test extends TestCase {

	/**
	 * Test Log_Tracker::compare_scalar with equal values.
	 */
	public function test_compare_scalar_equal() {
		$this->assertFalse( Log_Tracker::compare_scalar( 'test', 'test' ) );
		$this->assertFalse( Log_Tracker::compare_scalar( 123, 123 ) );
		$this->assertFalse( Log_Tracker::compare_scalar( '', '' ) );
		$this->assertFalse( Log_Tracker::compare_scalar( null, null ) );
	}

	/**
	 * Test Log_Tracker::compare_scalar with different values.
	 */
	public function test_compare_scalar_different() {
		$this->assertTrue( Log_Tracker::compare_scalar( 'test1', 'test2' ) );
		$this->assertTrue( Log_Tracker::compare_scalar( 123, 456 ) );
		$this->assertTrue( Log_Tracker::compare_scalar( 'test', '' ) );
		$this->assertTrue( Log_Tracker::compare_scalar( null, 'test' ) );
	}

	/**
	 * Test Log_Tracker::compare_scalar with numeric values (loose comparison).
	 */
	public function test_compare_scalar_numeric_loose() {
		$this->assertFalse( Log_Tracker::compare_scalar( '123', 123 ) );
		$this->assertFalse( Log_Tracker::compare_scalar( 123.0, 123 ) );
		$this->assertTrue( Log_Tracker::compare_scalar( 123, 124 ) );
	}

	/**
	 * Test Log_Tracker::compare_array with equal arrays.
	 */
	public function test_compare_array_equal() {
		$arr1 = array( 'a' => 1, 'b' => 2 );
		$arr2 = array( 'a' => 1, 'b' => 2 );
		$this->assertFalse( Log_Tracker::compare_array( $arr1, $arr2 ) );
	}

	/**
	 * Test Log_Tracker::compare_array with different arrays.
	 */
	public function test_compare_array_different() {
		$arr1 = array( 'a' => 1, 'b' => 2 );
		$arr2 = array( 'a' => 1, 'b' => 3 );
		$this->assertTrue( Log_Tracker::compare_array( $arr1, $arr2 ) );

		$arr3 = array( 'a' => 1 );
		$arr4 = array( 'a' => 1, 'b' => 2 );
		$this->assertTrue( Log_Tracker::compare_array( $arr3, $arr4 ) );
	}

	/**
	 * Test Log_Tracker::compare_array with nested arrays.
	 */
	public function test_compare_array_nested() {
		$arr1 = array( 'a' => array( 'x' => 1, 'y' => 2 ) );
		$arr2 = array( 'a' => array( 'x' => 1, 'y' => 2 ) );
		$this->assertFalse( Log_Tracker::compare_array( $arr1, $arr2 ) );

		$arr3 = array( 'a' => array( 'x' => 1, 'y' => 2 ) );
		$arr4 = array( 'a' => array( 'x' => 1, 'y' => 3 ) );
		$this->assertTrue( Log_Tracker::compare_array( $arr3, $arr4 ) );
	}

	/**
	 * Test Log_Tracker::compare_addresses with no changes.
	 */
	public function test_compare_addresses_no_changes() {
		$addr1 = array(
			'first_name' => 'John',
			'last_name'  => 'Doe',
			'address_1'  => '123 Main St',
			'city'       => 'New York',
			'postcode'   => '10001',
			'country'    => 'US',
		);
		$addr2 = $addr1;
		$changes = Log_Tracker::compare_addresses( $addr1, $addr2 );
		$this->assertEmpty( $changes );
	}

	/**
	 * Test Log_Tracker::compare_addresses with changes.
	 */
	public function test_compare_addresses_with_changes() {
		$addr1 = array(
			'first_name' => 'John',
			'last_name'  => 'Doe',
			'city'       => 'New York',
		);
		$addr2 = array(
			'first_name' => 'Jane',
			'last_name'  => 'Doe',
			'city'       => 'Boston',
		);
		$changes = Log_Tracker::compare_addresses( $addr1, $addr2 );
		
		$this->assertArrayHasKey( 'first_name', $changes );
		$this->assertArrayHasKey( 'city', $changes );
		$this->assertArrayNotHasKey( 'last_name', $changes );
		
		$this->assertEquals( 'John', $changes['first_name']['old'] );
		$this->assertEquals( 'Jane', $changes['first_name']['new'] );
	}

	/**
	 * Test Log_Formatter::format_for_storage with different value types.
	 */
	public function test_format_for_storage() {
		$formatter = new Log_Formatter();

		// Null.
		$this->assertNull( $formatter->format_for_storage( null ) );

		// String.
		$this->assertEquals( 'test', $formatter->format_for_storage( 'test' ) );

		// Number.
		$this->assertEquals( '123', $formatter->format_for_storage( 123 ) );
		$this->assertEquals( '123.45', $formatter->format_for_storage( 123.45 ) );

		// Boolean.
		$this->assertEquals( '1', $formatter->format_for_storage( true ) );
		$this->assertEquals( '0', $formatter->format_for_storage( false ) );

		// Array.
		$arr = array( 'a' => 1, 'b' => 2 );
		$result = $formatter->format_for_storage( $arr );
		$this->assertIsString( $result );
		$this->assertEquals( $arr, json_decode( $result, true ) );
	}

	/**
	 * Test Log_Formatter::format_price.
	 */
	public function test_format_price() {
		$formatter = new Log_Formatter();

		// Numeric value.
		$result = $formatter->format_price( 123.45 );
		$this->assertIsString( $result );
		$this->assertStringContainsString( '123.45', $result );

		// Non-numeric value.
		$result = $formatter->format_price( 'invalid' );
		$this->assertEquals( 'invalid', $result );
	}

	/**
	 * Test Log_Formatter::get_action_type_label.
	 */
	public function test_get_action_type_label() {
		$formatter = new Log_Formatter();

		// Should return translated label for known action type.
		$label = $formatter->get_action_type_label( 'status_changed' );
		$this->assertIsString( $label );
		$this->assertNotEmpty( $label );

		// Unknown action type should return the action type itself.
		$label = $formatter->get_action_type_label( 'unknown_action' );
		$this->assertEquals( 'unknown_action', $label );
	}

	/**
	 * Test Log_Formatter::get_field_label.
	 */
	public function test_get_field_label() {
		$formatter = new Log_Formatter();

		// Known field.
		$label = $formatter->get_field_label( 'status' );
		$this->assertIsString( $label );
		$this->assertNotEmpty( $label );

		// Unknown field should return formatted version.
		$label = $formatter->get_field_label( 'custom_field_name' );
		$this->assertEquals( 'Custom Field Name', $label );
	}

	/**
	 * Test Order_Logger::get_action_types returns array.
	 */
	public function test_get_action_types() {
		// Since Order_Logger uses singleton pattern, we need to get instance.
		// For this basic test, we'll just verify the class has the method.
		$this->assertTrue( method_exists( 'IHumBak\WooOrderEditLogs\Order_Logger', 'get_action_types' ) );
	}

	/**
	 * Test Order_Logger has log_change method.
	 */
	public function test_order_logger_has_log_change() {
		$this->assertTrue( method_exists( 'IHumBak\WooOrderEditLogs\Order_Logger', 'log_change' ) );
	}
}
