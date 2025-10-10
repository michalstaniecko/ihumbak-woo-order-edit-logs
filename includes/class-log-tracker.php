<?php
/**
 * Log Tracker Class
 *
 * Handles tracking and comparing order changes.
 *
 * @package IHumBak\WooOrderEditLogs
 */

namespace IHumBak\WooOrderEditLogs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Log_Tracker
 *
 * Manages order state snapshots and change detection.
 */
class Log_Tracker {

	/**
	 * Transient prefix for snapshots.
	 *
	 * @var string
	 */
	const SNAPSHOT_PREFIX = 'ihumbak_order_snapshot_';

	/**
	 * Transient expiration time (in seconds).
	 *
	 * @var int
	 */
	const SNAPSHOT_EXPIRATION = 600; // 10 minutes

	/**
	 * Store a snapshot of the current order state.
	 *
	 * Saves the current state of an order to a transient for later comparison.
	 *
	 * @param int $order_id Order ID.
	 * @return bool True on success, false on failure.
	 */
	public static function store_snapshot( $order_id ) {
		$order_data = HPOS_Compatibility::get_order_data_for_comparison( $order_id );
		
		if ( false === $order_data ) {
			return false;
		}

		$transient_key = self::SNAPSHOT_PREFIX . $order_id;
		return set_transient( $transient_key, $order_data, self::SNAPSHOT_EXPIRATION );
	}

	/**
	 * Get a stored snapshot.
	 *
	 * Retrieves the stored snapshot for an order.
	 *
	 * @param int $order_id Order ID.
	 * @return array|false Snapshot data or false if not found.
	 */
	public static function get_snapshot( $order_id ) {
		$transient_key = self::SNAPSHOT_PREFIX . $order_id;
		return get_transient( $transient_key );
	}

	/**
	 * Delete a stored snapshot.
	 *
	 * Removes the snapshot transient for an order.
	 *
	 * @param int $order_id Order ID.
	 * @return bool True on success.
	 */
	public static function delete_snapshot( $order_id ) {
		$transient_key = self::SNAPSHOT_PREFIX . $order_id;
		return delete_transient( $transient_key );
	}

	/**
	 * Compare two scalar values.
	 *
	 * Compares two simple values and returns whether they differ.
	 *
	 * @param mixed $old_value Old value.
	 * @param mixed $new_value New value.
	 * @return bool True if values differ, false otherwise.
	 */
	public static function compare_scalar( $old_value, $new_value ) {
		// Handle null/empty comparisons.
		if ( empty( $old_value ) && empty( $new_value ) ) {
			return false;
		}

		// For numeric values, use loose comparison.
		if ( is_numeric( $old_value ) && is_numeric( $new_value ) ) {
			// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
			return $old_value != $new_value;
		}

		// String comparison.
		return $old_value !== $new_value;
	}

	/**
	 * Compare two arrays.
	 *
	 * Recursively compares two arrays for differences.
	 *
	 * @param array $old_array Old array.
	 * @param array $new_array New array.
	 * @return bool True if arrays differ, false otherwise.
	 */
	public static function compare_array( $old_array, $new_array ) {
		// Ensure both are arrays.
		$old_array = (array) $old_array;
		$new_array = (array) $new_array;

		// Different number of elements.
		if ( count( $old_array ) !== count( $new_array ) ) {
			return true;
		}

		// Compare keys.
		$old_keys = array_keys( $old_array );
		$new_keys = array_keys( $new_array );
		sort( $old_keys );
		sort( $new_keys );

		if ( $old_keys !== $new_keys ) {
			return true;
		}

		// Compare values recursively.
		foreach ( $old_array as $key => $old_value ) {
			if ( ! isset( $new_array[ $key ] ) ) {
				return true;
			}

			if ( is_array( $old_value ) || is_array( $new_array[ $key ] ) ) {
				if ( self::compare_array( $old_value, $new_array[ $key ] ) ) {
					return true;
				}
			} elseif ( self::compare_scalar( $old_value, $new_array[ $key ] ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Compare two addresses.
	 *
	 * Specialized comparison for address data.
	 *
	 * @param array $old_address Old address data.
	 * @param array $new_address New address data.
	 * @return array Array of changed fields with old and new values.
	 */
	public static function compare_addresses( $old_address, $new_address ) {
		$changes = array();
		$fields  = array(
			'first_name',
			'last_name',
			'company',
			'address_1',
			'address_2',
			'city',
			'state',
			'postcode',
			'country',
			'email',
			'phone',
		);

		foreach ( $fields as $field ) {
			$old_val = isset( $old_address[ $field ] ) ? $old_address[ $field ] : '';
			$new_val = isset( $new_address[ $field ] ) ? $new_address[ $field ] : '';

			if ( self::compare_scalar( $old_val, $new_val ) ) {
				$changes[ $field ] = array(
					'old' => $old_val,
					'new' => $new_val,
				);
			}
		}

		return $changes;
	}

	/**
	 * Detect all changes between snapshot and current state.
	 *
	 * Compares a stored snapshot with the current order state and returns
	 * an array of all detected changes.
	 *
	 * @param int $order_id Order ID.
	 * @return array Array of changes, each with action_type, field_name, old_value, new_value.
	 */
	public static function detect_changes( $order_id ) {
		$snapshot     = self::get_snapshot( $order_id );
		$current_data = HPOS_Compatibility::get_order_data_for_comparison( $order_id );
		$changes      = array();

		if ( false === $snapshot || false === $current_data ) {
			return $changes;
		}

		// Compare status.
		if ( self::compare_scalar( $snapshot['status'], $current_data['status'] ) ) {
			$changes[] = array(
				'action_type' => 'status_changed',
				'field_name'  => 'status',
				'old_value'   => $snapshot['status'],
				'new_value'   => $current_data['status'],
			);
		}

		// Compare currency.
		if ( self::compare_scalar( $snapshot['currency'], $current_data['currency'] ) ) {
			$changes[] = array(
				'action_type' => 'currency_changed',
				'field_name'  => 'currency',
				'old_value'   => $snapshot['currency'],
				'new_value'   => $current_data['currency'],
			);
		}

		// Compare totals.
		if ( self::compare_scalar( $snapshot['total'], $current_data['total'] ) ) {
			$changes[] = array(
				'action_type' => 'total_changed',
				'field_name'  => 'total',
				'old_value'   => $snapshot['total'],
				'new_value'   => $current_data['total'],
			);
		}

		// Compare tax.
		if ( self::compare_scalar( $snapshot['tax_total'], $current_data['tax_total'] ) ) {
			$changes[] = array(
				'action_type' => 'tax_changed',
				'field_name'  => 'tax_total',
				'old_value'   => $snapshot['tax_total'],
				'new_value'   => $current_data['tax_total'],
			);
		}

		// Compare shipping total.
		if ( self::compare_scalar( $snapshot['shipping_total'], $current_data['shipping_total'] ) ) {
			$changes[] = array(
				'action_type' => 'shipping_cost_changed',
				'field_name'  => 'shipping_total',
				'old_value'   => $snapshot['shipping_total'],
				'new_value'   => $current_data['shipping_total'],
			);
		}

		// Compare payment method.
		if ( self::compare_scalar( $snapshot['payment_method'], $current_data['payment_method'] ) ) {
			$changes[] = array(
				'action_type' => 'payment_method_changed',
				'field_name'  => 'payment_method',
				'old_value'   => $snapshot['payment_method'],
				'new_value'   => $current_data['payment_method'],
			);
		}

		// Compare billing address.
		$billing_changes = self::compare_addresses(
			$snapshot['billing_address'],
			$current_data['billing_address']
		);
		if ( ! empty( $billing_changes ) ) {
			$changes[] = array(
				'action_type'     => 'billing_address_changed',
				'field_name'      => 'billing_address',
				'old_value'       => $snapshot['billing_address'],
				'new_value'       => $current_data['billing_address'],
				'additional_data' => $billing_changes,
			);
		}

		// Compare shipping address.
		$shipping_changes = self::compare_addresses(
			$snapshot['shipping_address'],
			$current_data['shipping_address']
		);
		if ( ! empty( $shipping_changes ) ) {
			$changes[] = array(
				'action_type'     => 'shipping_address_changed',
				'field_name'      => 'shipping_address',
				'old_value'       => $snapshot['shipping_address'],
				'new_value'       => $current_data['shipping_address'],
				'additional_data' => $shipping_changes,
			);
		}

		// Compare items (products).
		if ( self::compare_array( $snapshot['items'], $current_data['items'] ) ) {
			$changes[] = array(
				'action_type' => 'product_quantity_changed',
				'field_name'  => 'items',
				'old_value'   => $snapshot['items'],
				'new_value'   => $current_data['items'],
			);
		}

		return $changes;
	}
}
