<?php
/**
 * Product Hooks
 *
 * Handles product-related hooks in orders.
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
 * Initialize product hooks.
 */
function init_product_hooks() {
	// Hook for new order items.
	add_action( 'woocommerce_new_order_item', __NAMESPACE__ . '\log_order_item_added', 10, 3 );
	
	// Hook for updated order items.
	add_action( 'woocommerce_update_order_item', __NAMESPACE__ . '\log_order_item_updated', 10, 3 );
	
	// Hook before order item deletion to capture data.
	add_action( 'woocommerce_before_delete_order_item', __NAMESPACE__ . '\store_order_item_before_delete', 10 );
	
	// Hook for deleted order items.
	add_action( 'woocommerce_delete_order_item', __NAMESPACE__ . '\log_order_item_deleted', 10 );
}

/**
 * Log when a new order item is added.
 *
 * @param int    $item_id Item ID.
 * @param object $item Item object.
 * @param int    $order_id Order ID.
 */
function log_order_item_added( $item_id, $item, $order_id ) {
	if ( ! $item_id || ! $order_id ) {
		return;
	}
	
	// Only track product line items.
	if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$product_data = array(
		'product_id'   => $item->get_product_id(),
		'product_name' => $item->get_name(),
		'quantity'     => $item->get_quantity(),
		'subtotal'     => $item->get_subtotal(),
		'total'        => $item->get_total(),
	);
	
	$logger->log_change(
		$order_id,
		'product_added',
		'product',
		null,
		wp_json_encode( $product_data )
	);
}

/**
 * Log when an order item is updated.
 *
 * @param int    $item_id Item ID.
 * @param object $item Item object.
 * @param int    $order_id Order ID.
 */
function log_order_item_updated( $item_id, $item, $order_id ) {
	if ( ! $item_id || ! $order_id ) {
		return;
	}
	
	// Only track product line items.
	if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
		return;
	}
	
	// Get changes from the item object.
	$changes = $item->get_changes();
	
	if ( empty( $changes ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	
	// Log quantity changes.
	if ( isset( $changes['quantity'] ) ) {
		$logger->log_change(
			$order_id,
			'product_quantity_changed',
			$item->get_name(),
			null, // Old value not easily available.
			$item->get_quantity()
		);
	}
	
	// Log price/total changes.
	if ( isset( $changes['total'] ) || isset( $changes['subtotal'] ) ) {
		$logger->log_change(
			$order_id,
			'product_price_changed',
			$item->get_name(),
			null, // Old value not easily available.
			$item->get_total()
		);
	}
}

/**
 * Store order item data before deletion.
 *
 * @param int $item_id Item ID.
 */
function store_order_item_before_delete( $item_id ) {
	if ( ! $item_id ) {
		return;
	}
	
	$item = \WC_Order_Factory::get_order_item( $item_id );
	
	if ( ! $item || ! is_a( $item, 'WC_Order_Item_Product' ) ) {
		return;
	}
	
	// Store item data in transient for later use.
	$item_data = array(
		'order_id'     => $item->get_order_id(),
		'product_id'   => $item->get_product_id(),
		'product_name' => $item->get_name(),
		'quantity'     => $item->get_quantity(),
		'total'        => $item->get_total(),
	);
	
	set_transient( 'ihumbak_deleting_item_' . $item_id, $item_data, 60 );
}

/**
 * Log when an order item is deleted.
 *
 * @param int $item_id Item ID.
 */
function log_order_item_deleted( $item_id ) {
	if ( ! $item_id ) {
		return;
	}
	
	// Retrieve stored item data.
	$item_data = get_transient( 'ihumbak_deleting_item_' . $item_id );
	
	if ( ! $item_data || ! isset( $item_data['order_id'] ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$item_data['order_id'],
		'product_removed',
		'product',
		wp_json_encode( $item_data ),
		null
	);
	
	// Clean up transient.
	delete_transient( 'ihumbak_deleting_item_' . $item_id );
}

// Will be implemented in Etap 3.
