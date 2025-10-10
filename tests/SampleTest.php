<?php
/**
 * Sample Test
 *
 * This is a basic example test to verify the testing infrastructure works.
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

namespace IHumBak\WooOrderEditLogs\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Sample test class
 */
class SampleTest extends TestCase {

	/**
	 * Test that PHPUnit is working
	 *
	 * @return void
	 */
	public function test_phpunit_is_working() {
		$this->assertTrue( true );
	}

	/**
	 * Test basic assertions
	 *
	 * @return void
	 */
	public function test_basic_assertions() {
		$this->assertEquals( 1, 1 );
		$this->assertIsString( 'test' );
		$this->assertIsArray( array() );
	}
}
