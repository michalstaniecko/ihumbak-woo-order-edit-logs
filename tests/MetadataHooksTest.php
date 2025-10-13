<?php
/**
 * Test Metadata Hooks
 *
 * Tests that metadata hooks properly track direct update_post_meta() calls.
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

namespace IHumBak\WooOrderEditLogs\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class MetadataHooksTest
 *
 * Tests metadata hooks functionality.
 */
class MetadataHooksTest extends TestCase {

	/**
	 * Test that metadata-hooks.php file exists.
	 */
	public function test_metadata_hooks_file_exists() {
		$file = IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertFileExists( $file );
	}

	/**
	 * Test that init_metadata_hooks function exists.
	 */
	public function test_init_metadata_hooks_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\init_metadata_hooks' ) );
	}

	/**
	 * Test that is_tracked_meta_key function exists.
	 */
	public function test_is_tracked_meta_key_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\is_tracked_meta_key' ) );
	}

	/**
	 * Test that is_order_post_type function exists.
	 */
	public function test_is_order_post_type_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\is_order_post_type' ) );
	}

	/**
	 * Test that capture_meta_update function exists.
	 */
	public function test_capture_meta_update_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\capture_meta_update' ) );
	}

	/**
	 * Test that track_post_meta_add function exists.
	 */
	public function test_track_post_meta_add_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\track_post_meta_add' ) );
	}

	/**
	 * Test that track_post_meta_delete function exists.
	 */
	public function test_track_post_meta_delete_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\track_post_meta_delete' ) );
	}

	/**
	 * Test that capture_hpos_meta_update function exists (for HPOS support).
	 */
	public function test_capture_hpos_meta_update_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\capture_hpos_meta_update' ) );
	}

	/**
	 * Test that track_hpos_meta_add function exists (for HPOS support).
	 */
	public function test_track_hpos_meta_add_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\track_hpos_meta_add' ) );
	}

	/**
	 * Test that track_hpos_meta_update_action function exists (for HPOS support).
	 */
	public function test_track_hpos_meta_update_action_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\track_hpos_meta_update_action' ) );
	}

	/**
	 * Test that track_hpos_meta_delete function exists (for HPOS support).
	 */
	public function test_track_hpos_meta_delete_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\track_hpos_meta_delete' ) );
	}

	/**
	 * Test that is_order helper function exists (for HPOS support).
	 */
	public function test_is_order_function_exists() {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/metadata-hooks.php';
		$this->assertTrue( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\is_order' ) );
	}
}
