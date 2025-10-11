<?php
/**
 * HPOS Compatibility Layer
 *
 * Provides abstraction for WooCommerce High-Performance Order Storage (HPOS).
 *
 * @package IHumBak\WooOrderEditLogs
 */

namespace IHumBak\WooOrderEditLogs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class HPOS_Compatibility
 *
 * Handles compatibility between Custom Post Type (CPT) and HPOS storage modes.
 */
class HPOS_Compatibility {

	/**
	 * Check if HPOS is enabled.
	 *
	 * @return bool True if HPOS is enabled, false otherwise.
	 */
	public static function is_hpos_enabled() {
		if ( ! class_exists( 'Automattic\WooCommerce\Utilities\OrderUtil' ) ) {
			return false;
		}

		return \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	}

	/**
	 * Get the current storage mode.
	 *
	 * @return string 'hpos' or 'cpt'.
	 */
	public static function get_storage_mode() {
		return self::is_hpos_enabled() ? 'hpos' : 'cpt';
	}

	/**
	 * Get order object by ID.
	 *
	 * @param int $order_id Order ID.
	 * @return \WC_Order|false Order object or false on failure.
	 */
	public static function get_order( $order_id ) {
		return wc_get_order( $order_id );
	}

	/**
	 * Get order meta data.
	 *
	 * @param int    $order_id  Order ID.
	 * @param string $meta_key  Meta key.
	 * @param bool   $single    Whether to return a single value.
	 * @return mixed Meta value.
	 */
	public static function get_order_meta( $order_id, $meta_key, $single = true ) {
		$order = self::get_order( $order_id );
		if ( ! $order ) {
			return $single ? '' : array();
		}

		return $order->get_meta( $meta_key, $single );
	}

	/**
	 * Update order meta data.
	 *
	 * @param int    $order_id    Order ID.
	 * @param string $meta_key    Meta key.
	 * @param mixed  $meta_value  Meta value.
	 * @return bool True on success, false on failure.
	 */
	public static function update_order_meta( $order_id, $meta_key, $meta_value ) {
		$order = self::get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		$order->update_meta_data( $meta_key, $meta_value );
		$order->save();
		return true;
	}

	/**
	 * Get order data for comparison.
	 *
	 * Extracts all relevant order data in a normalized format.
	 *
	 * @param int $order_id Order ID.
	 * @return array|false Order data array or false on failure.
	 */
	public static function get_order_data_for_comparison( $order_id ) {
		$order = self::get_order( $order_id );
		if ( ! $order ) {
			return false;
		}

		$data = array(
			'status'            => $order->get_status(),
			'currency'          => $order->get_currency(),
			'total'             => $order->get_total(),
			'subtotal'          => $order->get_subtotal(),
			'tax_total'         => $order->get_total_tax(),
			'shipping_total'    => $order->get_shipping_total(),
			'discount_total'    => $order->get_discount_total(),
			'customer_id'       => $order->get_customer_id(),
			'billing_address'   => self::get_address_data( $order, 'billing' ),
			'shipping_address'  => self::get_address_data( $order, 'shipping' ),
			'payment_method'    => $order->get_payment_method(),
			'payment_method_title' => $order->get_payment_method_title(),
			'customer_note'     => $order->get_customer_note(),
			'items'             => self::get_items_data( $order ),
			'shipping_methods'  => self::get_shipping_methods_data( $order ),
			'coupons'           => self::get_coupons_data( $order ),
		);

		// Add custom meta fields if configured.
		$custom_meta = self::get_custom_meta_data( $order );
		if ( ! empty( $custom_meta ) ) {
			$data['custom_meta'] = $custom_meta;
		}

		return $data;
	}

	/**
	 * Get address data from order.
	 *
	 * @param \WC_Order $order Order object.
	 * @param string    $type  Address type ('billing' or 'shipping').
	 * @return array Address data.
	 */
	private static function get_address_data( $order, $type ) {
		$getter_prefix = "get_{$type}_";

		return array(
			'first_name' => $order->{$getter_prefix . 'first_name'}(),
			'last_name'  => $order->{$getter_prefix . 'last_name'}(),
			'company'    => $order->{$getter_prefix . 'company'}(),
			'address_1'  => $order->{$getter_prefix . 'address_1'}(),
			'address_2'  => $order->{$getter_prefix . 'address_2'}(),
			'city'       => $order->{$getter_prefix . 'city'}(),
			'state'      => $order->{$getter_prefix . 'state'}(),
			'postcode'   => $order->{$getter_prefix . 'postcode'}(),
			'country'    => $order->{$getter_prefix . 'country'}(),
			'email'      => $type === 'billing' ? $order->get_billing_email() : '',
			'phone'      => $type === 'billing' ? $order->get_billing_phone() : '',
		);
	}

	/**
	 * Get items data from order.
	 *
	 * @param \WC_Order $order Order object.
	 * @return array Items data.
	 */
	private static function get_items_data( $order ) {
		$items_data = array();
		$items      = $order->get_items();

		foreach ( $items as $item_id => $item ) {
			$items_data[ $item_id ] = array(
				'name'         => $item->get_name(),
				'product_id'   => $item->get_product_id(),
				'variation_id' => $item->get_variation_id(),
				'quantity'     => $item->get_quantity(),
				'subtotal'     => $item->get_subtotal(),
				'total'        => $item->get_total(),
				'tax'          => $item->get_total_tax(),
			);
		}

		return $items_data;
	}

	/**
	 * Get shipping methods data from order.
	 *
	 * @param \WC_Order $order Order object.
	 * @return array Shipping methods data.
	 */
	private static function get_shipping_methods_data( $order ) {
		$shipping_data = array();
		$shipping      = $order->get_items( 'shipping' );

		foreach ( $shipping as $item_id => $item ) {
			$shipping_data[ $item_id ] = array(
				'name'       => $item->get_name(),
				'method_id'  => $item->get_method_id(),
				'total'      => $item->get_total(),
				'tax'        => $item->get_total_tax(),
			);
		}

		return $shipping_data;
	}

	/**
	 * Get coupons data from order.
	 *
	 * @param \WC_Order $order Order object.
	 * @return array Coupons data.
	 */
	private static function get_coupons_data( $order ) {
		$coupons_data = array();
		$coupons      = $order->get_items( 'coupon' );

		foreach ( $coupons as $item_id => $item ) {
			$coupons_data[ $item_id ] = array(
				'code'     => $item->get_code(),
				'discount' => $item->get_discount(),
				'discount_tax' => $item->get_discount_tax(),
			);
		}

		return $coupons_data;
	}

	/**
	 * Get custom meta data from order.
	 *
	 * Gets the values of custom meta fields configured in settings.
	 *
	 * @param \WC_Order $order Order object.
	 * @return array Custom meta data.
	 */
	private static function get_custom_meta_data( $order ) {
		// Get configured custom meta fields from settings.
		if ( ! class_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings' ) ) {
			return array();
		}

		$settings = \IHumBak\WooOrderEditLogs\Admin\Settings::get_instance();
		$custom_fields = $settings->get_custom_meta_fields();

		if ( empty( $custom_fields ) ) {
			return array();
		}

		$custom_meta = array();
		foreach ( $custom_fields as $field_name ) {
			$custom_meta[ $field_name ] = $order->get_meta( $field_name, true );
		}

		return $custom_meta;
	}

	/**
	 * Compare two values and detect changes.
	 *
	 * @param mixed $old_value Old value.
	 * @param mixed $new_value New value.
	 * @return bool True if values are different, false otherwise.
	 */
	public static function values_differ( $old_value, $new_value ) {
		// Handle null/empty comparisons.
		if ( empty( $old_value ) && empty( $new_value ) ) {
			return false;
		}

		// For arrays, compare recursively.
		if ( is_array( $old_value ) || is_array( $new_value ) ) {
			return self::arrays_differ( $old_value, $new_value );
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
	 * Compare two arrays for differences.
	 *
	 * @param array $old_array Old array.
	 * @param array $new_array New array.
	 * @return bool True if arrays are different, false otherwise.
	 */
	private static function arrays_differ( $old_array, $new_array ) {
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

		// Compare values.
		foreach ( $old_array as $key => $old_value ) {
			if ( ! isset( $new_array[ $key ] ) ) {
				return true;
			}

			if ( is_array( $old_value ) || is_array( $new_array[ $key ] ) ) {
				if ( self::arrays_differ( $old_value, $new_array[ $key ] ) ) {
					return true;
				}
			} elseif ( self::values_differ( $old_value, $new_array[ $key ] ) ) {
				return true;
			}
		}

		return false;
	}
}
