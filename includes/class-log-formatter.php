<?php
/**
 * Log Formatter Class
 *
 * Handles formatting of log data for display.
 *
 * @package IHumBak\WooOrderEditLogs
 */

namespace IHumBak\WooOrderEditLogs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Log_Formatter
 *
 * Formats log data for storage and display.
 */
class Log_Formatter {

	/**
	 * Format value for storage.
	 *
	 * Converts values to appropriate format for database storage.
	 *
	 * @param mixed $value Value to format.
	 * @return string|null Formatted value.
	 */
	public function format_for_storage( $value ) {
		// Null values.
		if ( null === $value ) {
			return null;
		}

		// Arrays and objects - convert to JSON.
		if ( is_array( $value ) || is_object( $value ) ) {
			return wp_json_encode( $value );
		}

		// Booleans.
		if ( is_bool( $value ) ) {
			return $value ? '1' : '0';
		}

		// Numbers - keep as string representation.
		if ( is_numeric( $value ) ) {
			return (string) $value;
		}

		// Strings - sanitize.
		return sanitize_text_field( (string) $value );
	}

	/**
	 * Format value for display.
	 *
	 * Converts stored values to human-readable format.
	 *
	 * @param mixed  $value       Value to format.
	 * @param string $action_type Optional action type for context.
	 * @param string $field_name  Optional field name for context.
	 * @return string Formatted value.
	 */
	public function format_for_display( $value, $action_type = '', $field_name = '' ) {
		// Handle null values.
		if ( null === $value || '' === $value ) {
			return __( '(empty)', 'ihumbak-order-logs' );
		}

		// Try to decode JSON.
		$decoded = json_decode( $value, true );
		if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
			// Format arrays nicely.
			return $this->format_array_for_display( $decoded );
		}

		// Format based on action type or field name.
		if ( $this->is_price_field( $action_type, $field_name ) ) {
			return $this->format_price( $value );
		}

		if ( $this->is_date_field( $field_name ) ) {
			return $this->format_date( $value );
		}

		// Default: return as string.
		return esc_html( $value );
	}

	/**
	 * Format price value.
	 *
	 * @param mixed $value Price value.
	 * @return string Formatted price.
	 */
	public function format_price( $value ) {
		if ( ! is_numeric( $value ) ) {
			return esc_html( $value );
		}

		// Use WooCommerce price formatting if available.
		if ( function_exists( 'wc_price' ) ) {
			return wp_kses_post( wc_price( $value ) );
		}

		return number_format( (float) $value, 2 );
	}

	/**
	 * Format date value.
	 *
	 * @param string $value Date value.
	 * @return string Formatted date.
	 */
	public function format_date( $value ) {
		$timestamp = strtotime( $value );
		if ( false === $timestamp ) {
			return esc_html( $value );
		}

		return date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp );
	}

	/**
	 * Format array for display.
	 *
	 * @param array $array Array to format.
	 * @return string Formatted array.
	 */
	public function format_array_for_display( $array ) {
		$output = '<ul class="ihumbak-log-array">';
		foreach ( $array as $key => $value ) {
			$output .= '<li>';
			$output .= '<strong>' . esc_html( $key ) . ':</strong> ';
			
			if ( is_array( $value ) ) {
				$output .= $this->format_array_for_display( $value );
			} else {
				$output .= esc_html( $value );
			}
			
			$output .= '</li>';
		}
		$output .= '</ul>';
		
		return $output;
	}

	/**
	 * Check if field is a price field.
	 *
	 * @param string $action_type Action type.
	 * @param string $field_name  Field name.
	 * @return bool True if price field.
	 */
	private function is_price_field( $action_type, $field_name ) {
		$price_actions = array(
			'total_changed',
			'tax_changed',
			'shipping_cost_changed',
			'product_price_changed',
			'fee_changed',
		);

		$price_fields = array(
			'total',
			'tax',
			'price',
			'cost',
			'subtotal',
			'shipping_total',
			'tax_total',
			'discount_total',
		);

		return in_array( $action_type, $price_actions, true ) || 
		       in_array( $field_name, $price_fields, true );
	}

	/**
	 * Check if field is a date field.
	 *
	 * @param string $field_name Field name.
	 * @return bool True if date field.
	 */
	private function is_date_field( $field_name ) {
		$date_fields = array(
			'date',
			'timestamp',
			'created',
			'modified',
			'completed',
			'paid',
		);

		foreach ( $date_fields as $date_field ) {
			if ( false !== strpos( strtolower( $field_name ), $date_field ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get human-readable label for action type.
	 *
	 * @param string $action_type Action type.
	 * @return string Human-readable label.
	 */
	public function get_action_type_label( $action_type ) {
		$logger = Order_Logger::get_instance();
		$types  = $logger->get_action_types();

		return isset( $types[ $action_type ] ) ? $types[ $action_type ] : $action_type;
	}

	/**
	 * Get human-readable label for field name.
	 *
	 * @param string $field_name Field name.
	 * @return string Human-readable label.
	 */
	public function get_field_label( $field_name ) {
		$labels = array(
			'status'            => __( 'Status', 'ihumbak-order-logs' ),
			'total'             => __( 'Total', 'ihumbak-order-logs' ),
			'tax_total'         => __( 'Tax Total', 'ihumbak-order-logs' ),
			'shipping_total'    => __( 'Shipping Total', 'ihumbak-order-logs' ),
			'discount_total'    => __( 'Discount Total', 'ihumbak-order-logs' ),
			'payment_method'    => __( 'Payment Method', 'ihumbak-order-logs' ),
			'shipping_method'   => __( 'Shipping Method', 'ihumbak-order-logs' ),
			'billing_address'   => __( 'Billing Address', 'ihumbak-order-logs' ),
			'shipping_address'  => __( 'Shipping Address', 'ihumbak-order-logs' ),
			'customer_note'     => __( 'Customer Note', 'ihumbak-order-logs' ),
			'items'             => __( 'Items', 'ihumbak-order-logs' ),
			'currency'          => __( 'Currency', 'ihumbak-order-logs' ),
		);

		/**
		 * Filter field labels.
		 *
		 * Allows developers to add or modify field labels.
		 *
		 * @param array $labels Array of field labels.
		 */
		$labels = apply_filters( 'ihumbak_order_logs_field_labels', $labels );

		return isset( $labels[ $field_name ] ) ? $labels[ $field_name ] : ucwords( str_replace( '_', ' ', $field_name ) );
	}
}
