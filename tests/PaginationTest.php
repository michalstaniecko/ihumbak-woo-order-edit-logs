<?php
/**
 * Tests for Order Change History Pagination
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

use PHPUnit\Framework\TestCase;

/**
 * Class PaginationTest
 *
 * Tests the pagination functionality for order logs.
 */
class PaginationTest extends TestCase {

	/**
	 * Test that count_logs_by_order method exists.
	 */
	public function test_count_logs_by_order_method_exists() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Log_Database', 'count_logs_by_order' ),
			'count_logs_by_order method should exist in Log_Database class'
		);
	}

	/**
	 * Test that get_logs_by_order method exists.
	 */
	public function test_get_logs_by_order_method_exists() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Log_Database', 'get_logs_by_order' ),
			'get_logs_by_order method should exist in Log_Database class'
		);
	}

	/**
	 * Test that ajax_get_order_logs method exists in Admin_Interface.
	 */
	public function test_ajax_get_order_logs_method_exists() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Admin_Interface', 'ajax_get_order_logs' ),
			'ajax_get_order_logs method should exist in Admin_Interface class'
		);
	}

	/**
	 * Test that get_logs_by_order default order is DESC (newest first).
	 */
	public function test_get_logs_by_order_default_order() {
		// Use reflection to access the method and check defaults
		$reflection = new ReflectionMethod( 'IHumBak\WooOrderEditLogs\Log_Database', 'get_logs_by_order' );
		
		// Get the method source
		$filename = $reflection->getFileName();
		$start_line = $reflection->getStartLine();
		$end_line = $reflection->getEndLine();
		$length = $end_line - $start_line;
		
		$source = file( $filename );
		$method_source = implode( '', array_slice( $source, $start_line, $length ) );
		
		// Check if default order is DESC
		$this->assertStringContainsString(
			"'order'      => 'DESC'",
			$method_source,
			'Default order should be DESC (newest to oldest)'
		);
	}

	/**
	 * Test that pagination CSS classes exist in admin styles.
	 */
	public function test_pagination_css_exists() {
		$css_file = dirname( __DIR__ ) . '/assets/css/admin-styles.css';
		$this->assertFileExists( $css_file, 'Admin styles CSS file should exist' );
		
		$css_content = file_get_contents( $css_file );
		
		$this->assertStringContainsString(
			'.ihumbak-logs-pagination',
			$css_content,
			'Pagination CSS class should exist'
		);
		
		$this->assertStringContainsString(
			'.current-page',
			$css_content,
			'Current page CSS class should exist'
		);
		
		$this->assertStringContainsString(
			'.ihumbak-logs-page-link',
			$css_content,
			'Page link CSS class should exist'
		);
	}

	/**
	 * Test that JavaScript pagination handler exists.
	 */
	public function test_pagination_javascript_exists() {
		$js_file = dirname( __DIR__ ) . '/assets/js/admin-scripts.js';
		$this->assertFileExists( $js_file, 'Admin scripts JS file should exist' );
		
		$js_content = file_get_contents( $js_file );
		
		$this->assertStringContainsString(
			'initOrderMetaBoxPagination',
			$js_content,
			'Pagination initialization function should exist'
		);
		
		$this->assertStringContainsString(
			'loadOrderLogs',
			$js_content,
			'Load order logs function should exist'
		);
		
		$this->assertStringContainsString(
			'ihumbak_get_order_logs',
			$js_content,
			'AJAX action name should exist'
		);
		
		$this->assertStringContainsString(
			'.ihumbak-logs-page-link',
			$js_content,
			'Pagination link selector should exist'
		);
	}
}
