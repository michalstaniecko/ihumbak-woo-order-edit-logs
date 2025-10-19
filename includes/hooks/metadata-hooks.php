<?php
/**
 * Metadata Hooks
 *
 * Handles WordPress metadata-related hooks for tracking direct meta updates.
 * Supports both CPT mode (update_post_meta()) and HPOS mode (WooCommerce order metadata).
 *
 * @package IHumBak\WooOrderEditLogs\Hooks
 */

namespace IHumBak\WooOrderEditLogs\Hooks;

use IHumBak\WooOrderEditLogs\Admin\Settings;
use IHumBak\WooOrderEditLogs\Log_Tracker;
use IHumBak\WooOrderEditLogs\Order_Logger;
use IHumBak\WooOrderEditLogs\HPOS_Compatibility;
use WC_Data_Store;
use WC_Order;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Initialize metadata hooks.
 */
function init_metadata_hooks() {
	// Hook for CPT mode - detects direct update_post_meta() calls.
	// Use filter to capture the old value before it's updated.
	add_filter( 'update_post_metadata', __NAMESPACE__ . '\capture_meta_update', 10, 5 );
	add_action( 'added_post_meta', __NAMESPACE__ . '\track_post_meta_add', 10, 4 );
	add_action( 'deleted_post_meta', __NAMESPACE__ . '\track_post_meta_delete', 10, 4 );

	add_action( 'woocommerce_before_order_object_save', __NAMESPACE__ . '\capture_hpos_meta_update', 10, 1 );
	add_action( 'woocommerce_after_order_object_save', __NAMESPACE__ . '\detect_hpos_meta_changes', 10, 1 );
}

/**
 * Capture and track post meta update.
 *
 * Triggered before update_post_meta() updates the value in CPT mode.
 * This filter allows us to capture the old value before it's changed.
 *
 * @param null|bool $check Whether to allow updating metadata.
 * @param int $object_id Post ID.
 * @param string $meta_key Meta key.
 * @param mixed $meta_value New meta value.
 * @param mixed $prev_value Previous meta value (if specified in update_post_meta).
 * @return null|bool Null to continue with the update, or a boolean to short-circuit.
 */
function capture_meta_update( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
	// Check if this is a WooCommerce order post type.
	if ( ! is_order_post_type( $object_id ) ) {
		return $check;
	}

	// Check if this meta key is tracked.
	if ( ! is_tracked_meta_key( $meta_key ) ) {
		return $check;
	}

	// Check if we're in the middle of an order save operation.
	// If snapshot exists, let the snapshot approach handle logging.
	if ( \IHumBak\WooOrderEditLogs\Log_Tracker::get_snapshot( $object_id ) !== false ) {
		return $check;
	}

	// Get the current (old) value before it's updated.
	$old_value = get_post_meta( $object_id, $meta_key, true );

	// If values are the same, skip logging.
	// Use loose comparison to handle numeric strings.
	// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
	if ( $old_value == $meta_value ) {
		return $check;
	}

	// Log the change immediately.
	// This only happens when update_post_meta() is called directly,
	// not when $order->save() is called (snapshot approach handles that).
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$object_id,
		'custom_field_changed',
		$meta_key,
		$old_value,
		$meta_value
	);

	// Return null to continue with the update.
	return $check;
}


/**
 * Track post meta addition.
 *
 * Triggered when add_post_meta() is called in CPT mode.
 *
 * @param int $meta_id ID of updated metadata entry.
 * @param int $object_id Post ID.
 * @param string $meta_key Meta key.
 * @param mixed $meta_value Meta value.
 */
function track_post_meta_add( $meta_id, $object_id, $meta_key, $meta_value ) {
	// Check if this is a WooCommerce order post type.
	if ( ! is_order_post_type( $object_id ) ) {
		return;
	}

	// Check if this meta key is tracked.
	if ( ! is_tracked_meta_key( $meta_key ) ) {
		return;
	}

	// Log the change (old value is empty for new meta).
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$object_id,
		'custom_field_changed',
		$meta_key,
		'',
		$meta_value
	);
}

/**
 * Track post meta deletion.
 *
 * Triggered when delete_post_meta() is called in CPT mode.
 *
 * @param array $meta_ids An array of deleted metadata entry IDs.
 * @param int $object_id Post ID.
 * @param string $meta_key Meta key.
 * @param mixed $meta_value Meta value.
 */
function track_post_meta_delete( $meta_ids, $object_id, $meta_key, $meta_value ) {
	// Check if this is a WooCommerce order post type.
	if ( ! is_order_post_type( $object_id ) ) {
		return;
	}

	// Check if this meta key is tracked.
	if ( ! is_tracked_meta_key( $meta_key ) ) {
		return;
	}

	// Log the change (new value is empty for deleted meta).
	$logger = Order_Logger::get_instance();
	$logger->log_change(
		$object_id,
		'custom_field_changed',
		$meta_key,
		$meta_value,
		''
	);
}

/**
 * Check if a post is a WooCommerce order.
 *
 * @param int $post_id Post ID.
 * @return bool True if post is an order, false otherwise.
 */
function is_order_post_type( $post_id ) {
	// Get valid order post types.
	$order_types = wc_get_order_types();

	// Get post type.
	$post_type = get_post_type( $post_id );

	return in_array( $post_type, $order_types, true );
}

/**
 * Check if an object ID is a WooCommerce order (works for both CPT and HPOS).
 *
 * @param int $object_id Object ID.
 * @param string $meta_type Meta type (e.g., 'post', 'hpos_order').
 * @return bool True if object is an order, false otherwise.
 */
function is_order( $object_id, $meta_type = 'post' ) {
	// For post meta, check if it's an order post type.
	if ( 'post' === $meta_type ) {
		return is_order_post_type( $object_id );
	}

	// For HPOS, the meta_type should be specific to orders.
	// WooCommerce uses different meta types for HPOS orders.
	// We need to verify this is an order by trying to get the order object.
	if ( function_exists( 'wc_get_order' ) ) {
		$order = wc_get_order( $object_id );
		return $order instanceof \WC_Order;
	}

	return false;
}

/**
 * Check if a meta key should be tracked.
 *
 * @param string $meta_key Meta key to check.
 * @return bool True if meta key should be tracked, false otherwise.
 */
function is_tracked_meta_key( $meta_key ) {
	// Get configured custom meta fields from settings.
	if ( ! class_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings' ) ) {
		return false;
	}

	$settings = \IHumBak\WooOrderEditLogs\Admin\Settings::get_instance();
	$custom_fields = $settings->get_custom_meta_fields();

	return in_array( $meta_key, $custom_fields, true );
}

/**
 * Capture HPOS meta update before saving.
 *
 * @param \WC_Order $order Order.
 * @return void
 */
function capture_hpos_meta_update( $order ) {
	$settings = Settings::get_instance();
	$meta_keys = $settings->get_custom_meta_fields();
	$old_order = new WC_Order( $order->get_id() );
	$transient_key_prefix = Log_Tracker::get_prefix() . '_' . $order->get_id();
	if ( empty( $meta_keys ) ) {
		return;
	}
	foreach ( $meta_keys as $meta_key ) {
		$meta_value = $old_order->get_meta( $meta_key );
		set_transient( $transient_key_prefix . '_' . $meta_key, $meta_value, HOUR_IN_SECONDS );
	}
}

/**
 * Detect and log HPOS meta changes.
 *
 * @param \WC_Order $order Order.
 * @return void
 */
function detect_hpos_meta_changes( $order ) {
	$meta_keys = Settings::get_instance()->get_custom_meta_fields();
	if ( empty( $meta_keys ) ) {
		return;
	}
	$transient_key_prefix = Log_Tracker::get_prefix() . '_' . $order->get_id();
	$logger = Order_Logger::get_instance();
	foreach ( $meta_keys as $meta_key ) {
		$meta_value = $order->get_meta( $meta_key );
		$old_value = get_transient( $transient_key_prefix . '_' . $meta_key );

		if ( true !== Log_Tracker::compare_scalar( $old_value, $meta_value ) ) {
			return;
		}

		$logger->log_change(
			$order->get_id(),
			'custom_field_changed',
			$meta_key,
			$old_value,
			$meta_value
		);
		delete_transient( $transient_key_prefix . '_' . $meta_key );
	}
}
