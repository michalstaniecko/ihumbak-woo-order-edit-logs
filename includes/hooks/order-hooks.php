<?php
/**
 * Order Hooks
 *
 * Handles WooCommerce order-related hooks.
 *
 * @package IHumBak\WooOrderEditLogs\Hooks
 */

namespace IHumBak\WooOrderEditLogs\Hooks;

use IHumBak\WooOrderEditLogs\Order_Logger;
use IHumBak\WooOrderEditLogs\Log_Tracker;
use IHumBak\WooOrderEditLogs\HPOS_Compatibility;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize order hooks.
 */
function init_order_hooks() {
	// Hook before order is saved to store snapshot.
	add_action( 'woocommerce_before_order_object_save', __NAMESPACE__ . '\store_order_snapshot', 10, 2 );
	
	// Hook after order is saved to detect and log changes.
	add_action( 'woocommerce_after_order_object_save', __NAMESPACE__ . '\detect_order_changes', 10, 2 );
	
	// Hook for order creation.
	add_action( 'woocommerce_new_order', __NAMESPACE__ . '\log_order_created', 10, 2 );
	
	// Hook for status changes.
	add_action( 'woocommerce_order_status_changed', __NAMESPACE__ . '\log_status_change', 10, 4 );
}

/**
 * Store order snapshot before save.
 *
 * @param \WC_Order $order Order object.
 * @param object    $data_store Data store object.
 */
function store_order_snapshot( $order, $data_store ) {
	if ( ! $order || ! $order->get_id() ) {
		return;
	}
	
	// Only store snapshot if order already exists (update scenario).
	if ( $order->get_id() > 0 ) {
		Log_Tracker::store_snapshot( $order->get_id() );
	}
}

/**
 * Detect and log order changes after save.
 *
 * @param \WC_Order $order Order object.
 * @param object    $data_store Data store object.
 */
function detect_order_changes( $order, $data_store ) {
	if ( ! $order || ! $order->get_id() ) {
		return;
	}
	
	$order_id = $order->get_id();
	$snapshot = Log_Tracker::get_snapshot( $order_id );
	
	// If no snapshot exists, this might be a new order (handled by woocommerce_new_order).
	if ( false === $snapshot ) {
		return;
	}
	
	// Get current order data.
	$current_data = HPOS_Compatibility::get_order_data_for_comparison( $order_id );
	
	if ( false === $current_data ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	
	// Compare and log changes.
	compare_and_log_field( $snapshot, $current_data, 'currency', 'currency_changed', 'currency', $logger, $order_id );
	compare_and_log_field( $snapshot, $current_data, 'payment_method', 'payment_method_changed', 'payment_method', $logger, $order_id );
	compare_and_log_field( $snapshot, $current_data, 'total', 'total_changed', 'total', $logger, $order_id );
	compare_and_log_field( $snapshot, $current_data, 'tax_total', 'tax_changed', 'tax_total', $logger, $order_id );
	compare_and_log_field( $snapshot, $current_data, 'shipping_total', 'shipping_cost_changed', 'shipping_total', $logger, $order_id );
	
	// Compare billing address.
	if ( isset( $snapshot['billing_address'] ) && isset( $current_data['billing_address'] ) ) {
		$billing_changes = Log_Tracker::compare_addresses( $snapshot['billing_address'], $current_data['billing_address'] );
		if ( ! empty( $billing_changes ) ) {
			foreach ( $billing_changes as $field => $change ) {
				$logger->log_change(
					$order_id,
					'billing_address_changed',
					$field,
					$change['old'],
					$change['new']
				);
			}
		}
	}
	
	// Compare shipping address.
	if ( isset( $snapshot['shipping_address'] ) && isset( $current_data['shipping_address'] ) ) {
		$shipping_changes = Log_Tracker::compare_addresses( $snapshot['shipping_address'], $current_data['shipping_address'] );
		if ( ! empty( $shipping_changes ) ) {
			foreach ( $shipping_changes as $field => $change ) {
				$logger->log_change(
					$order_id,
					'shipping_address_changed',
					$field,
					$change['old'],
					$change['new']
				);
			}
		}
	}
	
	// Compare custom meta fields.
	$snapshot_meta = isset( $snapshot['custom_meta'] ) ? $snapshot['custom_meta'] : array();
	$current_meta = isset( $current_data['custom_meta'] ) ? $current_data['custom_meta'] : array();
	
	if ( ! empty( $snapshot_meta ) || ! empty( $current_meta ) ) {
		// Check for changed or removed fields.
		foreach ( $snapshot_meta as $meta_key => $old_value ) {
			$new_value = isset( $current_meta[ $meta_key ] ) ? $current_meta[ $meta_key ] : '';
			
			if ( Log_Tracker::compare_scalar( $old_value, $new_value ) ) {
				$logger->log_change(
					$order_id,
					'custom_field_changed',
					$meta_key,
					$old_value,
					$new_value
				);
			}
		}
		
		// Check for new custom meta fields that weren't in the snapshot.
		foreach ( $current_meta as $meta_key => $new_value ) {
			if ( ! isset( $snapshot_meta[ $meta_key ] ) ) {
				// Only log if the new value is not empty.
				if ( ! empty( $new_value ) || ( is_string( $new_value ) && '0' === $new_value ) ) {
					$logger->log_change(
						$order_id,
						'custom_field_changed',
						$meta_key,
						'',
						$new_value
					);
				}
			}
		}
	}
	
	// Clean up snapshot.
	Log_Tracker::delete_snapshot( $order_id );
}

/**
 * Compare and log a single field change.
 *
 * @param array        $snapshot Snapshot data.
 * @param array        $current Current data.
 * @param string       $field Field name.
 * @param string       $action_type Action type to log.
 * @param string       $log_field Field name for log.
 * @param Order_Logger $logger Logger instance.
 * @param int          $order_id Order ID.
 */
function compare_and_log_field( $snapshot, $current, $field, $action_type, $log_field, $logger, $order_id ) {
	if ( isset( $snapshot[ $field ] ) && isset( $current[ $field ] ) ) {
		if ( Log_Tracker::compare_scalar( $snapshot[ $field ], $current[ $field ] ) ) {
			$logger->log_change(
				$order_id,
				$action_type,
				$log_field,
				$snapshot[ $field ],
				$current[ $field ]
			);
		}
	}
}

/**
 * Log order creation.
 *
 * @param int       $order_id Order ID.
 * @param \WC_Order $order Order object.
 */
function log_order_created( $order_id, $order = null ) {
	if ( ! $order_id ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$order_id,
		'order_created',
		'',
		null,
		null,
		array( 'created_via' => $order ? $order->get_created_via() : 'unknown' )
	);
}

/**
 * Log order status change.
 *
 * @param int    $order_id Order ID.
 * @param string $old_status Old status (without 'wc-' prefix).
 * @param string $new_status New status (without 'wc-' prefix).
 * @param object $order Order object.
 */
function log_status_change( $order_id, $old_status, $new_status, $order ) {
	if ( ! $order_id || $old_status === $new_status ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$order_id,
		'status_changed',
		'status',
		$old_status,
		$new_status
	);
}

// Will be implemented in Etap 3.
