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

	add_action( 'woocommerce_before_save_order_items', __NAMESPACE__ . '\store_order_items_before_update', 10, 2 );

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
 * @param int $item_id Item ID.
 * @param object $item Item object.
 * @param int $order_id Order ID.
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
 * Store order item data before update.
 *
 * @param int $order_id Order ID.
 * @param array $items Order data.
 * @return void
 */
function store_order_items_before_update( $order_id, $items ) {
	// set transient for old product prices
	if ( empty( $items ) ) {
		return;
	}

	$order = wc_get_order( $order_id );
	$order_items = $order->get_items();

	foreach ( $order_items as $item ) {
		if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
			set_transient( 'ihumbak_update_product_price_' . $order_id . '_' . $item->get_id(), $item->get_total(), 60 );
			set_transient( 'ihumbak_update_product_quantity_' . $order_id . '_' . $item->get_id(), $item->get_quantity(), 60 );
		}
	}
}

/**
 * Log when an order item is updated.
 *
 * @param int $item_id Item ID.
 * @param object $item Item object.
 * @param int $order_id Order ID.
 */
function log_order_item_updated( $item_id, $item, $order_id ) {
	if ( ! $item_id || ! $order_id ) {
		return;
	}

	// Only track product line items.
	if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
		return;
	}

	$old_price = get_transient( 'ihumbak_update_product_price_' . $order_id . '_' . $item->get_id() );
	$old_quantity = get_transient( 'ihumbak_update_product_quantity_' . $order_id . '_' . $item->get_id() );

	$logger = Order_Logger::get_instance();

	$new_quantity = $item->get_quantity();
	// Log quantity changes.
	if ( !empty( $old_quantity ) && $old_quantity != $new_quantity ) {
		$logger->log_change(
			$order_id,
			'product_quantity_changed',
			$item->get_name(),
			$old_quantity,
			$new_quantity
		);
	}

	$new_price = $item->get_total();
	// Log price/total changes.
	if ( !empty( $old_price ) && $old_price != $new_price ) {
		$logger->log_change(
			$order_id,
			'product_price_changed',
			$item->get_name(),
			$old_price,
			$new_price
		);
	}
	delete_transient( 'ihumbak_update_product_price_' . $order_id . '_' . $item->get_id() );
	delete_transient( 'ihumbak_update_product_quantity_' . $order_id . '_' . $item->get_id() );
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
