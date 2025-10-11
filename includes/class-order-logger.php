<?php
/**
 * Order Logger Class
 *
 * Main class for the order logging system.
 *
 * @package IHumBak\WooOrderEditLogs
 */

namespace IHumBak\WooOrderEditLogs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Order_Logger
 *
 * Main plugin class that coordinates the logging system.
 */
class Order_Logger {

	/**
	 * Single instance of the class.
	 *
	 * @var Order_Logger|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Order_Logger
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * Private to prevent direct instantiation.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize WordPress hooks.
	 */
	private function init_hooks() {
		// Check if database needs upgrade.
		add_action( 'admin_init', array( $this, 'check_database_upgrade' ) );

		// Admin notices.
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );

		// Initialize hook files after WooCommerce is loaded.
		add_action( 'woocommerce_init', array( $this, 'init_woocommerce_hooks' ) );

		// Initialize admin interface.
		if ( is_admin() ) {
			$this->init_admin_interface();
		}
	}

	/**
	 * Initialize admin interface.
	 */
	public function init_admin_interface() {
		\IHumBak\WooOrderEditLogs\Admin\Admin_Interface::get_instance();
	}

	/**
	 * Initialize WooCommerce hooks.
	 *
	 * Loads and initializes all hook files for tracking order changes.
	 */
	public function init_woocommerce_hooks() {
		// Load hook files.
		$hooks_path = IHUMBAK_ORDER_LOGS_PATH . 'includes/hooks/';

		if ( file_exists( $hooks_path . 'order-hooks.php' ) ) {
			require_once $hooks_path . 'order-hooks.php';
			if ( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\init_order_hooks' ) ) {
				\IHumBak\WooOrderEditLogs\Hooks\init_order_hooks();
			}
		}

		if ( file_exists( $hooks_path . 'product-hooks.php' ) ) {
			require_once $hooks_path . 'product-hooks.php';
			if ( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\init_product_hooks' ) ) {
				\IHumBak\WooOrderEditLogs\Hooks\init_product_hooks();
			}
		}

		if ( file_exists( $hooks_path . 'address-hooks.php' ) ) {
			require_once $hooks_path . 'address-hooks.php';
			if ( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\init_address_hooks' ) ) {
				\IHumBak\WooOrderEditLogs\Hooks\init_address_hooks();
			}
		}

		if ( file_exists( $hooks_path . 'payment-hooks.php' ) ) {
			require_once $hooks_path . 'payment-hooks.php';
			if ( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\init_payment_hooks' ) ) {
				\IHumBak\WooOrderEditLogs\Hooks\init_payment_hooks();
			}
		}

		if ( file_exists( $hooks_path . 'metadata-hooks.php' ) ) {
			require_once $hooks_path . 'metadata-hooks.php';
			if ( function_exists( 'IHumBak\WooOrderEditLogs\Hooks\init_metadata_hooks' ) ) {
				\IHumBak\WooOrderEditLogs\Hooks\init_metadata_hooks();
			}
		}
	}

	/**
	 * Check if database needs upgrade and perform upgrade if needed.
	 */
	public function check_database_upgrade() {
		if ( Log_Database::needs_upgrade() ) {
			Log_Database::create_tables();
		}
	}

	/**
	 * Display admin notices.
	 */
	public function admin_notices() {
		// Check if WooCommerce is active.
		if ( ! class_exists( 'WooCommerce' ) ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( 'iHumBak Order Logs:', 'ihumbak-order-logs' ); ?></strong>
					<?php esc_html_e( 'WooCommerce is required for this plugin to work.', 'ihumbak-order-logs' ); ?>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Get plugin version.
	 *
	 * @return string Plugin version.
	 */
	public function get_version() {
		return defined( 'IHUMBAK_ORDER_LOGS_VERSION' ) ? IHUMBAK_ORDER_LOGS_VERSION : '1.0.0';
	}

	/**
	 * Get database version.
	 *
	 * @return string Database version.
	 */
	public function get_db_version() {
		return get_option( 'ihumbak_order_logs_db_version', '0' );
	}

	/**
	 * Log a change in an order.
	 *
	 * Main method for logging order changes. Validates input, enriches data
	 * with user information and metadata, then saves to database.
	 *
	 * @param int    $order_id        Order ID.
	 * @param string $action_type     Type of action (see action types).
	 * @param string $field_name      Name of the changed field.
	 * @param mixed  $old_value       Value before change.
	 * @param mixed  $new_value       Value after change.
	 * @param array  $additional_data Optional additional data.
	 * @return bool True on success, false on failure.
	 */
	public function log_change( $order_id, $action_type, $field_name = '', $old_value = null, $new_value = null, $additional_data = array() ) {
		// Validate required fields.
		if ( empty( $order_id ) || empty( $action_type ) ) {
			return false;
		}

		// Format values for storage using Log_Formatter.
		$formatter = new Log_Formatter();
		$old_value = $formatter->format_for_storage( $old_value );
		$new_value = $formatter->format_for_storage( $new_value );

		// Prepare additional data as JSON if needed.
		if ( ! empty( $additional_data ) && is_array( $additional_data ) ) {
			$additional_data = wp_json_encode( $additional_data );
		}

		// Prepare log data.
		$log_data = array(
			'order_id'        => absint( $order_id ),
			'action_type'     => sanitize_text_field( $action_type ),
			'field_name'      => $field_name ? sanitize_text_field( $field_name ) : null,
			'old_value'       => $old_value,
			'new_value'       => $new_value,
			'additional_data' => $additional_data,
		);

		// Insert log entry.
		return false !== Log_Database::insert_log( $log_data );
	}

	/**
	 * Get supported action types.
	 *
	 * Returns array of all supported action types.
	 *
	 * @return array Array of action types.
	 */
	public function get_action_types() {
		$action_types = array(
			'order_created'            => __( 'Order Created', 'ihumbak-order-logs' ),
			'status_changed'           => __( 'Status Changed', 'ihumbak-order-logs' ),
			'billing_address_changed'  => __( 'Billing Address Changed', 'ihumbak-order-logs' ),
			'shipping_address_changed' => __( 'Shipping Address Changed', 'ihumbak-order-logs' ),
			'product_added'            => __( 'Product Added', 'ihumbak-order-logs' ),
			'product_removed'          => __( 'Product Removed', 'ihumbak-order-logs' ),
			'product_quantity_changed' => __( 'Product Quantity Changed', 'ihumbak-order-logs' ),
			'product_price_changed'    => __( 'Product Price Changed', 'ihumbak-order-logs' ),
			'shipping_added'           => __( 'Shipping Added', 'ihumbak-order-logs' ),
			'shipping_removed'         => __( 'Shipping Removed', 'ihumbak-order-logs' ),
			'shipping_cost_changed'    => __( 'Shipping Cost Changed', 'ihumbak-order-logs' ),
			'shipping_method_changed'  => __( 'Shipping Method Changed', 'ihumbak-order-logs' ),
			'payment_method_changed'   => __( 'Payment Method Changed', 'ihumbak-order-logs' ),
			'fee_added'                => __( 'Fee Added', 'ihumbak-order-logs' ),
			'fee_removed'              => __( 'Fee Removed', 'ihumbak-order-logs' ),
			'fee_changed'              => __( 'Fee Changed', 'ihumbak-order-logs' ),
			'coupon_added'             => __( 'Coupon Added', 'ihumbak-order-logs' ),
			'coupon_removed'           => __( 'Coupon Removed', 'ihumbak-order-logs' ),
			'note_added'               => __( 'Note Added', 'ihumbak-order-logs' ),
			'note_deleted'             => __( 'Note Deleted', 'ihumbak-order-logs' ),
			'email_changed'            => __( 'Email Changed', 'ihumbak-order-logs' ),
			'phone_changed'            => __( 'Phone Changed', 'ihumbak-order-logs' ),
			'customer_data_changed'    => __( 'Customer Data Changed', 'ihumbak-order-logs' ),
			'total_changed'            => __( 'Total Changed', 'ihumbak-order-logs' ),
			'tax_changed'              => __( 'Tax Changed', 'ihumbak-order-logs' ),
			'meta_updated'             => __( 'Metadata Updated', 'ihumbak-order-logs' ),
			'custom_field_changed'     => __( 'Custom Field Changed', 'ihumbak-order-logs' ),
			'date_changed'             => __( 'Date Changed', 'ihumbak-order-logs' ),
			'currency_changed'         => __( 'Currency Changed', 'ihumbak-order-logs' ),
		);

		/**
		 * Filter available action types.
		 *
		 * Allows developers to add or remove action types.
		 *
		 * @param array $action_types Array of action types.
		 */
		return apply_filters( 'ihumbak_order_logs_action_types', $action_types );
	}
}
