<?php
/**
 * Address Hooks
 *
 * Handles address change hooks.
 * Note: Most address tracking is done via order-hooks.php when comparing snapshots.
 * This file provides additional tracking for specific address-related actions.
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
 * Initialize address hooks.
 */
function init_address_hooks() {
	// Email and phone changes are tracked via order snapshot comparison.
	// Additional hooks can be added here for specific address-related events.
	
	// Hook for customer email changes.
	add_action( 'woocommerce_before_order_object_save', __NAMESPACE__ . '\track_email_phone_changes', 5, 2 );
}

/**
 * Track email and phone changes before order save.
 *
 * This provides more granular tracking of email/phone changes
 * that might be missed by general address comparison.
 *
 * @param \WC_Order $order Order object.
 * @param object    $data_store Data store object.
 */
function track_email_phone_changes( $order, $data_store ) {
	if ( ! $order || ! $order->get_id() ) {
		return;
	}
	
	// Get changes from order object.
	$changes = $order->get_changes();
	
	if ( empty( $changes ) ) {
		return;
	}
	
	$logger = Order_Logger::get_instance();
	$order_id = $order->get_id();
	
	// Track email changes.
	if ( isset( $changes['billing_email'] ) ) {
		// Get old value from database.
		$old_order = wc_get_order( $order_id );
		if ( $old_order ) {
			$old_email = $old_order->get_billing_email();
			$new_email = $order->get_billing_email();
			
			if ( $old_email !== $new_email ) {
				$logger->log_change(
					$order_id,
					'email_changed',
					'billing_email',
					$old_email,
					$new_email
				);
			}
		}
	}
	
	// Track phone changes.
	if ( isset( $changes['billing_phone'] ) ) {
		// Get old value from database.
		$old_order = wc_get_order( $order_id );
		if ( $old_order ) {
			$old_phone = $old_order->get_billing_phone();
			$new_phone = $order->get_billing_phone();
			
			if ( $old_phone !== $new_phone ) {
				$logger->log_change(
					$order_id,
					'phone_changed',
					'billing_phone',
					$old_phone,
					$new_phone
				);
			}
		}
	}
}

// Will be implemented in Etap 3.
