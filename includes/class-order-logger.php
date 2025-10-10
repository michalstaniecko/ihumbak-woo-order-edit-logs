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
}
