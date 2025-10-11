<?php
/**
 * Test Stage 3 Implementation
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

namespace IHumBak\WooOrderEditLogs\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Class Stage3Test
 *
 * Tests Stage 3 implementation: Hook files for automatic change tracking.
 */
class Stage3Test extends TestCase {

	/**
	 * Test that order hooks file exists and has correct structure.
	 */
	public function test_order_hooks_file_exists() {
		$file_path = IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/order-hooks.php';
		$this->assertFileExists( $file_path );
		
		// Check file has PHP opening tag and namespace.
		$content = file_get_contents( $file_path );
		$this->assertStringContainsString( 'namespace IHumBak\WooOrderEditLogs\Hooks;', $content );
		$this->assertStringContainsString( 'function init_order_hooks()', $content );
	}

	/**
	 * Test that product hooks file exists and has correct structure.
	 */
	public function test_product_hooks_file_exists() {
		$file_path = IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/product-hooks.php';
		$this->assertFileExists( $file_path );
		
		$content = file_get_contents( $file_path );
		$this->assertStringContainsString( 'namespace IHumBak\WooOrderEditLogs\Hooks;', $content );
		$this->assertStringContainsString( 'function init_product_hooks()', $content );
	}

	/**
	 * Test that address hooks file exists and has correct structure.
	 */
	public function test_address_hooks_file_exists() {
		$file_path = IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/address-hooks.php';
		$this->assertFileExists( $file_path );
		
		$content = file_get_contents( $file_path );
		$this->assertStringContainsString( 'namespace IHumBak\WooOrderEditLogs\Hooks;', $content );
		$this->assertStringContainsString( 'function init_address_hooks()', $content );
	}

	/**
	 * Test that payment hooks file exists and has correct structure.
	 */
	public function test_payment_hooks_file_exists() {
		$file_path = IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/payment-hooks.php';
		$this->assertFileExists( $file_path );
		
		$content = file_get_contents( $file_path );
		$this->assertStringContainsString( 'namespace IHumBak\WooOrderEditLogs\Hooks;', $content );
		$this->assertStringContainsString( 'function init_payment_hooks()', $content );
	}

	/**
	 * Test that order hooks file has required functions.
	 */
	public function test_order_hooks_has_required_functions() {
		$file_path = IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/order-hooks.php';
		$content = file_get_contents( $file_path );
		
		$required_functions = array(
			'init_order_hooks',
			'store_order_snapshot',
			'detect_order_changes',
			'compare_and_log_field',
			'log_order_created',
			'log_status_change',
		);
		
		foreach ( $required_functions as $function ) {
			$this->assertStringContainsString( 
				'function ' . $function, 
				$content,
				"Order hooks should contain function: {$function}"
			);
		}
	}

	/**
	 * Test that product hooks file has required functions.
	 */
	public function test_product_hooks_has_required_functions() {
		$file_path = IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/product-hooks.php';
		$content = file_get_contents( $file_path );
		
		$required_functions = array(
			'init_product_hooks',
			'log_order_item_added',
			'log_order_item_updated',
			'store_order_item_before_delete',
			'log_order_item_deleted',
		);
		
		foreach ( $required_functions as $function ) {
			$this->assertStringContainsString( 
				'function ' . $function, 
				$content,
				"Product hooks should contain function: {$function}"
			);
		}
	}

	/**
	 * Test that payment hooks file has required functions.
	 */
	public function test_payment_hooks_has_required_functions() {
		$file_path = IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/payment-hooks.php';
		$content = file_get_contents( $file_path );
		
		$required_functions = array(
			'init_payment_hooks',
			'log_coupon_added',
			'log_coupon_removed',
			'log_order_refunded',
			'log_note_added',
			'log_note_deleted',
			'log_fee_added',
			'log_shipping_added',
			'get_current_order_id',
		);
		
		foreach ( $required_functions as $function ) {
			$this->assertStringContainsString( 
				'function ' . $function, 
				$content,
				"Payment hooks should contain function: {$function}"
			);
		}
	}

	/**
	 * Test that Order_Logger has init_woocommerce_hooks method.
	 */
	public function test_order_logger_has_init_woocommerce_hooks() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Order_Logger', 'init_woocommerce_hooks' ),
			'Order_Logger should have init_woocommerce_hooks method'
		);
	}

	/**
	 * Test that hook files use correct action types.
	 */
	public function test_hooks_use_correct_action_types() {
		$order_logger = \IHumBak\WooOrderEditLogs\Order_Logger::get_instance();
		$action_types = $order_logger->get_action_types();
		
		// Verify that action types used in hooks are defined.
		$this->assertArrayHasKey( 'order_created', $action_types );
		$this->assertArrayHasKey( 'status_changed', $action_types );
		$this->assertArrayHasKey( 'product_added', $action_types );
		$this->assertArrayHasKey( 'product_removed', $action_types );
		$this->assertArrayHasKey( 'product_quantity_changed', $action_types );
		$this->assertArrayHasKey( 'product_price_changed', $action_types );
		$this->assertArrayHasKey( 'billing_address_changed', $action_types );
		$this->assertArrayHasKey( 'shipping_address_changed', $action_types );
		$this->assertArrayHasKey( 'email_changed', $action_types );
		$this->assertArrayHasKey( 'phone_changed', $action_types );
		$this->assertArrayHasKey( 'payment_method_changed', $action_types );
		$this->assertArrayHasKey( 'shipping_added', $action_types );
		$this->assertArrayHasKey( 'shipping_removed', $action_types );
		$this->assertArrayHasKey( 'shipping_cost_changed', $action_types );
		$this->assertArrayHasKey( 'shipping_method_changed', $action_types );
		$this->assertArrayHasKey( 'coupon_added', $action_types );
		$this->assertArrayHasKey( 'coupon_removed', $action_types );
		$this->assertArrayHasKey( 'fee_added', $action_types );
		$this->assertArrayHasKey( 'fee_removed', $action_types );
		$this->assertArrayHasKey( 'fee_changed', $action_types );
		$this->assertArrayHasKey( 'note_added', $action_types );
		$this->assertArrayHasKey( 'note_deleted', $action_types );
		$this->assertArrayHasKey( 'total_changed', $action_types );
		$this->assertArrayHasKey( 'tax_changed', $action_types );
		$this->assertArrayHasKey( 'currency_changed', $action_types );
	}

	/**
	 * Test that hook files use Log_Tracker for snapshot comparison.
	 */
	public function test_hooks_use_log_tracker() {
		$order_hooks_content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/order-hooks.php' );
		
		$this->assertStringContainsString( 'use IHumBak\WooOrderEditLogs\Log_Tracker;', $order_hooks_content );
		$this->assertStringContainsString( 'Log_Tracker::store_snapshot', $order_hooks_content );
		$this->assertStringContainsString( 'Log_Tracker::get_snapshot', $order_hooks_content );
		$this->assertStringContainsString( 'Log_Tracker::delete_snapshot', $order_hooks_content );
		$this->assertStringContainsString( 'Log_Tracker::compare_scalar', $order_hooks_content );
		$this->assertStringContainsString( 'Log_Tracker::compare_addresses', $order_hooks_content );
	}

	/**
	 * Test that hook files use Order_Logger for logging.
	 */
	public function test_hooks_use_order_logger() {
		$hooks_files = array(
			'order-hooks.php',
			'product-hooks.php',
			'address-hooks.php',
			'payment-hooks.php',
		);
		
		foreach ( $hooks_files as $file ) {
			$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/' . $file );
			$this->assertStringContainsString( 
				'use IHumBak\WooOrderEditLogs\Order_Logger;', 
				$content,
				"{$file} should use Order_Logger"
			);
			$this->assertStringContainsString( 
				'Order_Logger::get_instance()', 
				$content,
				"{$file} should get Order_Logger instance"
			);
			$this->assertStringContainsString( 
				'->log_change(', 
				$content,
				"{$file} should call log_change method"
			);
		}
	}

	/**
	 * Test that hook files use HPOS_Compatibility where needed.
	 */
	public function test_hooks_use_hpos_compatibility() {
		$order_hooks_content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/order-hooks.php' );
		
		$this->assertStringContainsString( 
			'use IHumBak\WooOrderEditLogs\HPOS_Compatibility;', 
			$order_hooks_content,
			'Order hooks should use HPOS_Compatibility'
		);
		$this->assertStringContainsString( 
			'HPOS_Compatibility::get_order_data_for_comparison', 
			$order_hooks_content,
			'Order hooks should use get_order_data_for_comparison'
		);
	}

	/**
	 * Test that order hooks register WooCommerce hooks.
	 */
	public function test_order_hooks_register_woocommerce_hooks() {
		$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/order-hooks.php' );
		
		$this->assertStringContainsString( 'woocommerce_before_order_object_save', $content );
		$this->assertStringContainsString( 'woocommerce_after_order_object_save', $content );
		$this->assertStringContainsString( 'woocommerce_new_order', $content );
		$this->assertStringContainsString( 'woocommerce_order_status_changed', $content );
	}

	/**
	 * Test that product hooks register WooCommerce hooks.
	 */
	public function test_product_hooks_register_woocommerce_hooks() {
		$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/product-hooks.php' );
		
		$this->assertStringContainsString( 'woocommerce_new_order_item', $content );
		$this->assertStringContainsString( 'woocommerce_update_order_item', $content );
		$this->assertStringContainsString( 'woocommerce_before_delete_order_item', $content );
		$this->assertStringContainsString( 'woocommerce_delete_order_item', $content );
	}

	/**
	 * Test that payment hooks register WooCommerce hooks.
	 */
	public function test_payment_hooks_register_woocommerce_hooks() {
		$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/payment-hooks.php' );
		
		$this->assertStringContainsString( 'woocommerce_applied_coupon', $content );
		$this->assertStringContainsString( 'woocommerce_removed_coupon', $content );
		$this->assertStringContainsString( 'woocommerce_order_refunded', $content );
		$this->assertStringContainsString( 'woocommerce_new_order_note', $content );
		$this->assertStringContainsString( 'woocommerce_delete_order_note', $content );
	}

	/**
	 * Test that all hook files have proper exit security check.
	 */
	public function test_hooks_have_security_check() {
		$hooks_files = array(
			'order-hooks.php',
			'product-hooks.php',
			'address-hooks.php',
			'payment-hooks.php',
		);
		
		foreach ( $hooks_files as $file ) {
			$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/' . $file );
			$this->assertStringContainsString( 
				"if ( ! defined( 'ABSPATH' ) )", 
				$content,
				"{$file} should have ABSPATH security check"
			);
		}
	}

	/**
	 * Test that hook files are properly namespaced.
	 */
	public function test_hooks_are_namespaced() {
		$hooks_files = array(
			'order-hooks.php',
			'product-hooks.php',
			'address-hooks.php',
			'payment-hooks.php',
		);
		
		foreach ( $hooks_files as $file ) {
			$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/' . $file );
			$this->assertStringContainsString( 
				'namespace IHumBak\WooOrderEditLogs\Hooks;', 
				$content,
				"{$file} should be in Hooks namespace"
			);
		}
	}

	/**
	 * Test that product hooks handle transient storage for deletions.
	 */
	public function test_product_hooks_handle_deletion_transients() {
		$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/product-hooks.php' );
		
		$this->assertStringContainsString( 'set_transient', $content );
		$this->assertStringContainsString( 'get_transient', $content );
		$this->assertStringContainsString( 'delete_transient', $content );
		$this->assertStringContainsString( 'ihumbak_deleting_item_', $content );
	}

	/**
	 * Test that payment hooks handle fee and shipping items.
	 */
	public function test_payment_hooks_handle_fees_and_shipping() {
		$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/payment-hooks.php' );
		
		$this->assertStringContainsString( 'WC_Order_Item_Fee', $content );
		$this->assertStringContainsString( 'WC_Order_Item_Shipping', $content );
		$this->assertStringContainsString( 'log_fee_added', $content );
		$this->assertStringContainsString( 'log_shipping_added', $content );
	}

	/**
	 * Test that payment hooks handle refunds.
	 */
	public function test_payment_hooks_handle_refunds() {
		$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/payment-hooks.php' );
		
		$this->assertStringContainsString( 'log_order_refunded', $content );
		$this->assertStringContainsString( 'refund_id', $content );
		$this->assertStringContainsString( 'get_amount', $content );
		$this->assertStringContainsString( 'get_reason', $content );
	}

	/**
	 * Test that address hooks track email and phone changes.
	 */
	public function test_address_hooks_track_email_phone() {
		$content = file_get_contents( IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/address-hooks.php' );
		
		$this->assertStringContainsString( 'billing_email', $content );
		$this->assertStringContainsString( 'billing_phone', $content );
		$this->assertStringContainsString( 'email_changed', $content );
		$this->assertStringContainsString( 'phone_changed', $content );
	}
}
