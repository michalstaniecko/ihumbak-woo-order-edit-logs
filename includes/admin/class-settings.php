<?php
/**
 * Settings Class
 *
 * Handles plugin settings and options.
 *
 * @package IHumBak\WooOrderEditLogs\Admin
 */

namespace IHumBak\WooOrderEditLogs\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Settings
 *
 * Handles plugin settings using WooCommerce Settings API.
 */
class Settings {

	/**
	 * Single instance of the class.
	 *
	 * @var Settings|null
	 */
	private static $instance = null;

	/**
	 * Settings tab ID.
	 *
	 * @var string
	 */
	private $tab_id = 'ihumbak_order_logs';

	/**
	 * Get the singleton instance.
	 *
	 * @return Settings
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Add settings tab.
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 50 );
		
		// Add settings fields.
		add_action( 'woocommerce_settings_' . $this->tab_id, array( $this, 'output_settings' ) );
		
		// Save settings.
		add_action( 'woocommerce_update_options_' . $this->tab_id, array( $this, 'save_settings' ) );
	}

	/**
	 * Add settings tab to WooCommerce settings.
	 *
	 * @param array $tabs Existing tabs.
	 * @return array
	 */
	public function add_settings_tab( $tabs ) {
		$tabs[ $this->tab_id ] = __( 'Order Logs', 'ihumbak-order-logs' );
		return $tabs;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		$settings = array(
			// General section.
			array(
				'title' => __( 'General Settings', 'ihumbak-order-logs' ),
				'type'  => 'title',
				'id'    => 'ihumbak_order_logs_general',
			),
			array(
				'title'   => __( 'Enable Logging', 'ihumbak-order-logs' ),
				'desc'    => __( 'Enable automatic order change logging', 'ihumbak-order-logs' ),
				'id'      => 'ihumbak_order_logs_enabled',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Save IP Address', 'ihumbak-order-logs' ),
				'desc'    => __( 'Save user IP address with each log entry', 'ihumbak-order-logs' ),
				'id'      => 'ihumbak_order_logs_save_ip',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'title'   => __( 'Save User Agent', 'ihumbak-order-logs' ),
				'desc'    => __( 'Save browser user agent with each log entry', 'ihumbak-order-logs' ),
				'id'      => 'ihumbak_order_logs_save_user_agent',
				'default' => 'yes',
				'type'    => 'checkbox',
			),
			array(
				'type' => 'sectionend',
				'id'   => 'ihumbak_order_logs_general',
			),

			// Retention section.
			array(
				'title' => __( 'Log Retention', 'ihumbak-order-logs' ),
				'type'  => 'title',
				'id'    => 'ihumbak_order_logs_retention',
			),
			array(
				'title'   => __( 'Auto Cleanup', 'ihumbak-order-logs' ),
				'desc'    => __( 'Automatically delete old logs', 'ihumbak-order-logs' ),
				'id'      => 'ihumbak_order_logs_auto_cleanup',
				'default' => 'no',
				'type'    => 'checkbox',
			),
			array(
				'title'             => __( 'Retention Days', 'ihumbak-order-logs' ),
				'desc'              => __( 'Number of days to keep logs before automatic deletion', 'ihumbak-order-logs' ),
				'id'                => 'ihumbak_order_logs_retention_days',
				'default'           => '90',
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '1',
					'step' => '1',
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'ihumbak_order_logs_retention',
			),

			// Performance section.
			array(
				'title' => __( 'Performance', 'ihumbak-order-logs' ),
				'type'  => 'title',
				'id'    => 'ihumbak_order_logs_performance',
			),
			array(
				'title'             => __( 'Logs Per Page', 'ihumbak-order-logs' ),
				'desc'              => __( 'Number of log entries to display per page', 'ihumbak-order-logs' ),
				'id'                => 'ihumbak_order_logs_per_page',
				'default'           => '20',
				'type'              => 'number',
				'custom_attributes' => array(
					'min'  => '10',
					'max'  => '100',
					'step' => '10',
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'ihumbak_order_logs_performance',
			),

			// Export section.
			array(
				'title' => __( 'Export Settings', 'ihumbak-order-logs' ),
				'type'  => 'title',
				'id'    => 'ihumbak_order_logs_export',
			),
			array(
				'title'   => __( 'Date Format', 'ihumbak-order-logs' ),
				'desc'    => __( 'Date format for exported files', 'ihumbak-order-logs' ),
				'id'      => 'ihumbak_order_logs_export_date_format',
				'default' => 'Y-m-d H:i:s',
				'type'    => 'text',
			),
			array(
				'title'   => __( 'CSV Separator', 'ihumbak-order-logs' ),
				'id'      => 'ihumbak_order_logs_csv_separator',
				'default' => ',',
				'type'    => 'select',
				'options' => array(
					','  => __( 'Comma (,)', 'ihumbak-order-logs' ),
					';'  => __( 'Semicolon (;)', 'ihumbak-order-logs' ),
					"\t" => __( 'Tab', 'ihumbak-order-logs' ),
				),
			),
			array(
				'type' => 'sectionend',
				'id'   => 'ihumbak_order_logs_export',
			),
		);

		return apply_filters( 'ihumbak_order_logs_settings', $settings );
	}

	/**
	 * Output settings.
	 */
	public function output_settings() {
		woocommerce_admin_fields( $this->get_settings() );
	}

	/**
	 * Save settings.
	 */
	public function save_settings() {
		woocommerce_update_options( $this->get_settings() );
	}

	/**
	 * Get setting value.
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_option( $key, $default = null ) {
		return get_option( $key, $default );
	}

	/**
	 * Check if logging is enabled.
	 *
	 * @return bool
	 */
	public function is_logging_enabled() {
		return 'yes' === $this->get_option( 'ihumbak_order_logs_enabled', 'yes' );
	}

	/**
	 * Check if auto cleanup is enabled.
	 *
	 * @return bool
	 */
	public function is_auto_cleanup_enabled() {
		return 'yes' === $this->get_option( 'ihumbak_order_logs_auto_cleanup', 'no' );
	}

	/**
	 * Get retention days.
	 *
	 * @return int
	 */
	public function get_retention_days() {
		return absint( $this->get_option( 'ihumbak_order_logs_retention_days', 90 ) );
	}
}
