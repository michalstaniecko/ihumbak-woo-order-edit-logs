<?php
/**
 * Payment Hooks
 *
 * Handles payment method change hooks, shipping, coupons, fees, refunds, and notes.
 *
 * @package IHumBak\WooOrderEditLogs\Hooks
 */

namespace IHumBak\WooOrderEditLogs\Hooks;

use IHumBak\WooOrderEditLogs\Order_Logger;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize payment and related hooks.
 */
function init_payment_hooks() {
	// Coupon hooks.
	add_action( 'woocommerce_applied_coupon', __NAMESPACE__ . '\log_coupon_added', 10, 1 );
	add_action( 'woocommerce_removed_coupon', __NAMESPACE__ . '\log_coupon_removed', 10, 1 );
	
	// Refund hooks.
	add_action( 'woocommerce_order_refunded', __NAMESPACE__ . '\log_order_refunded', 10, 2 );
	
	// Note hooks.
	add_action( 'woocommerce_new_order_note', __NAMESPACE__ . '\log_note_added', 10, 2 );
	add_action( 'woocommerce_delete_order_note', __NAMESPACE__ . '\log_note_deleted', 10, 1 );
	
	// Fee hooks - track via order item hooks.
	add_action( 'woocommerce_new_order_item', __NAMESPACE__ . '\log_fee_added', 10, 3 );
	add_action( 'woocommerce_update_order_item', __NAMESPACE__ . '\log_fee_updated', 10, 3 );
	add_action( 'woocommerce_before_delete_order_item', __NAMESPACE__ . '\store_fee_before_delete', 10 );
	add_action( 'woocommerce_delete_order_item', __NAMESPACE__ . '\log_fee_deleted', 10 );
	
	// Shipping hooks - track via order item hooks.
	add_action( 'woocommerce_new_order_item', __NAMESPACE__ . '\log_shipping_added', 10, 3 );
	add_action( 'woocommerce_update_order_item', __NAMESPACE__ . '\log_shipping_updated', 10, 3 );
	add_action( 'woocommerce_before_delete_order_item', __NAMESPACE__ . '\store_shipping_before_delete', 10 );
	add_action( 'woocommerce_delete_order_item', __NAMESPACE__ . '\log_shipping_deleted', 10 );
}

/**
 * Log when a coupon is added.
 *
 * @param string $coupon_code Coupon code.
 */
function log_coupon_added( $coupon_code ) {
	if ( ! $coupon_code ) {
		return;
	}
	
	// Get current order ID from session/context.
	$order_id = get_current_order_id();
	
	if ( ! $order_id ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$order_id,
		'coupon_added',
		'coupon',
		null,
		$coupon_code
	);
}

/**
 * Log when a coupon is removed.
 *
 * @param string $coupon_code Coupon code.
 */
function log_coupon_removed( $coupon_code ) {
	if ( ! $coupon_code ) {
		return;
	}
	
	// Get current order ID from session/context.
	$order_id = get_current_order_id();
	
	if ( ! $order_id ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$order_id,
		'coupon_removed',
		'coupon',
		$coupon_code,
		null
	);
}

/**
 * Log when an order is refunded.
 *
 * @param int $order_id Order ID.
 * @param int $refund_id Refund ID.
 */
function log_order_refunded( $order_id, $refund_id ) {
	if ( ! $order_id || ! $refund_id ) {
		return;
	}
	
	$refund = wc_get_order( $refund_id );
	
	if ( ! $refund ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$refund_data = array(
		'refund_id'     => $refund_id,
		'amount'        => abs( $refund->get_amount() ),
		'reason'        => $refund->get_reason(),
		'refunded_by'   => $refund->get_refunded_by(),
	);
	
	$logger->log_change(
		$order_id,
		'order_refunded',
		'refund',
		null,
		wp_json_encode( $refund_data )
	);
}

/**
 * Log when a note is added.
 *
 * @param int   $note_id Note ID.
 * @param array $note_data Note data.
 */
function log_note_added( $note_id, $note_data ) {
	if ( ! $note_id || empty( $note_data['order_id'] ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$note_info = array(
		'note_id'      => $note_id,
		'content'      => isset( $note_data['content'] ) ? $note_data['content'] : '',
		'customer_note' => isset( $note_data['is_customer_note'] ) && $note_data['is_customer_note'],
	);
	
	$logger->log_change(
		$note_data['order_id'],
		'note_added',
		'note',
		null,
		wp_json_encode( $note_info )
	);
}

/**
 * Log when a note is deleted.
 *
 * @param int $note_id Note ID.
 */
function log_note_deleted( $note_id ) {
	if ( ! $note_id ) {
		return;
	}
	
	// Try to get note before it's deleted.
	global $wpdb;
	$note = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->comments} WHERE comment_ID = %d", $note_id ) );
	
	if ( ! $note || 'order_note' !== $note->comment_type ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$note->comment_post_ID,
		'note_deleted',
		'note',
		$note->comment_content,
		null
	);
}

/**
 * Log when a fee is added.
 *
 * @param int    $item_id Item ID.
 * @param object $item Item object.
 * @param int    $order_id Order ID.
 */
function log_fee_added( $item_id, $item, $order_id ) {
	if ( ! $item_id || ! $order_id ) {
		return;
	}
	
	// Only track fee items.
	if ( ! is_a( $item, 'WC_Order_Item_Fee' ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$fee_data = array(
		'name'   => $item->get_name(),
		'amount' => $item->get_total(),
		'tax'    => $item->get_total_tax(),
	);
	
	$logger->log_change(
		$order_id,
		'fee_added',
		'fee',
		null,
		wp_json_encode( $fee_data )
	);
}

/**
 * Log when a fee is updated.
 *
 * @param int    $item_id Item ID.
 * @param object $item Item object.
 * @param int    $order_id Order ID.
 */
function log_fee_updated( $item_id, $item, $order_id ) {
	if ( ! $item_id || ! $order_id ) {
		return;
	}
	
	// Only track fee items.
	if ( ! is_a( $item, 'WC_Order_Item_Fee' ) ) {
		return;
	}
	
	$changes = $item->get_changes();
	
	if ( empty( $changes ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$fee_data = array(
		'name'   => $item->get_name(),
		'amount' => $item->get_total(),
	);
	
	$logger->log_change(
		$order_id,
		'fee_changed',
		'fee',
		null, // Old value not easily available.
		wp_json_encode( $fee_data )
	);
}

/**
 * Store fee data before deletion.
 *
 * @param int $item_id Item ID.
 */
function store_fee_before_delete( $item_id ) {
	if ( ! $item_id ) {
		return;
	}
	
	$item = \WC_Order_Factory::get_order_item( $item_id );
	
	if ( ! $item || ! is_a( $item, 'WC_Order_Item_Fee' ) ) {
		return;
	}
	
	$fee_data = array(
		'order_id' => $item->get_order_id(),
		'name'     => $item->get_name(),
		'amount'   => $item->get_total(),
	);
	
	set_transient( 'ihumbak_deleting_fee_' . $item_id, $fee_data, 60 );
}

/**
 * Log when a fee is deleted.
 *
 * @param int $item_id Item ID.
 */
function log_fee_deleted( $item_id ) {
	if ( ! $item_id ) {
		return;
	}
	
	$fee_data = get_transient( 'ihumbak_deleting_fee_' . $item_id );
	
	if ( ! $fee_data || ! isset( $fee_data['order_id'] ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$fee_data['order_id'],
		'fee_removed',
		'fee',
		wp_json_encode( $fee_data ),
		null
	);
	
	delete_transient( 'ihumbak_deleting_fee_' . $item_id );
}

/**
 * Log when shipping is added.
 *
 * @param int    $item_id Item ID.
 * @param object $item Item object.
 * @param int    $order_id Order ID.
 */
function log_shipping_added( $item_id, $item, $order_id ) {
	if ( ! $item_id || ! $order_id ) {
		return;
	}
	
	// Only track shipping items.
	if ( ! is_a( $item, 'WC_Order_Item_Shipping' ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$shipping_data = array(
		'method_title' => $item->get_method_title(),
		'method_id'    => $item->get_method_id(),
		'total'        => $item->get_total(),
	);
	
	$logger->log_change(
		$order_id,
		'shipping_added',
		'shipping',
		null,
		wp_json_encode( $shipping_data )
	);
}

/**
 * Log when shipping is updated.
 *
 * @param int    $item_id Item ID.
 * @param object $item Item object.
 * @param int    $order_id Order ID.
 */
function log_shipping_updated( $item_id, $item, $order_id ) {
	if ( ! $item_id || ! $order_id ) {
		return;
	}
	
	// Only track shipping items.
	if ( ! is_a( $item, 'WC_Order_Item_Shipping' ) ) {
		return;
	}
	
	$changes = $item->get_changes();
	
	if ( empty( $changes ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	
	// Log method changes.
	if ( isset( $changes['method_title'] ) || isset( $changes['method_id'] ) ) {
		$logger->log_change(
			$order_id,
			'shipping_method_changed',
			'shipping_method',
			null, // Old value not easily available.
			$item->get_method_title()
		);
	}
	
	// Log cost changes.
	if ( isset( $changes['total'] ) ) {
		$logger->log_change(
			$order_id,
			'shipping_cost_changed',
			'shipping_cost',
			null, // Old value not easily available.
			$item->get_total()
		);
	}
}

/**
 * Store shipping data before deletion.
 *
 * @param int $item_id Item ID.
 */
function store_shipping_before_delete( $item_id ) {
	if ( ! $item_id ) {
		return;
	}
	
	$item = \WC_Order_Factory::get_order_item( $item_id );
	
	if ( ! $item || ! is_a( $item, 'WC_Order_Item_Shipping' ) ) {
		return;
	}
	
	$shipping_data = array(
		'order_id'     => $item->get_order_id(),
		'method_title' => $item->get_method_title(),
		'total'        => $item->get_total(),
	);
	
	set_transient( 'ihumbak_deleting_shipping_' . $item_id, $shipping_data, 60 );
}

/**
 * Log when shipping is deleted.
 *
 * @param int $item_id Item ID.
 */
function log_shipping_deleted( $item_id ) {
	if ( ! $item_id ) {
		return;
	}
	
	$shipping_data = get_transient( 'ihumbak_deleting_shipping_' . $item_id );
	
	if ( ! $shipping_data || ! isset( $shipping_data['order_id'] ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$shipping_data['order_id'],
		'shipping_removed',
		'shipping',
		wp_json_encode( $shipping_data ),
		null
	);
	
	delete_transient( 'ihumbak_deleting_shipping_' . $item_id );
}

/**
 * Helper function to get current order ID from context.
 *
 * @return int|null Order ID or null if not found.
 */
function get_current_order_id() {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['post'] ) && 'shop_order' === get_post_type( $_GET['post'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return absint( $_GET['post'] );
	}
	
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_POST['order_id'] ) ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return absint( $_POST['order_id'] );
	}
	
	// For HPOS, check for order ID parameter.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( isset( $_GET['id'] ) ) {
		$order = wc_get_order( absint( $_GET['id'] ) );
		if ( $order ) {
			return $order->get_id();
		}
	}
	
	return null;
}

// Will be implemented in Etap 3.
