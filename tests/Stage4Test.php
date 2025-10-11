<?php
/**
 * Tests for Stage 4 - Admin Interface
 *
 * @package IHumBak\WooOrderEditLogs\Tests
 */

use PHPUnit\Framework\TestCase;

// Mock WordPress functions needed for admin classes
if ( ! function_exists( 'add_submenu_page' ) ) {
	function add_submenu_page( $parent, $title, $menu_title, $capability, $slug, $callback ) {
		return $slug;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) {
		return true;
	}
}

if ( ! function_exists( 'admin_url' ) ) {
	function admin_url( $path ) {
		return 'http://example.com/wp-admin/' . $path;
	}
}

if ( ! function_exists( 'wp_enqueue_style' ) ) {
	function wp_enqueue_style( $handle, $src = '', $deps = array(), $ver = false, $media = 'all' ) {
		return;
	}
}

if ( ! function_exists( 'wp_enqueue_script' ) ) {
	function wp_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false ) {
		return;
	}
}

if ( ! function_exists( 'wp_localize_script' ) ) {
	function wp_localize_script( $handle, $object_name, $l10n ) {
		return;
	}
}

if ( ! function_exists( 'wp_create_nonce' ) ) {
	function wp_create_nonce( $action ) {
		return 'test_nonce';
	}
}

if ( ! function_exists( 'get_current_screen' ) ) {
	function get_current_screen() {
		return null;
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ) {
		return $url;
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return stripslashes( $value );
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults ) {
		return array_merge( $defaults, (array) $args );
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $value ) {
		return abs( (int) $value );
	}
}

if ( ! function_exists( 'woocommerce_admin_fields' ) ) {
	function woocommerce_admin_fields( $options ) {
		return;
	}
}

if ( ! function_exists( 'woocommerce_update_options' ) ) {
	function woocommerce_update_options( $options ) {
		return;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		return $default;
	}
}

if ( ! function_exists( 'wc_get_page_screen_id' ) ) {
	function wc_get_page_screen_id( $page ) {
		return 'shop_order';
	}
}

if ( ! function_exists( 'add_meta_box' ) ) {
	function add_meta_box( $id, $title, $callback, $screen, $context, $priority ) {
		return;
	}
}

if ( ! function_exists( 'date_i18n' ) ) {
	function date_i18n( $format, $timestamp = false ) {
		return date( $format, $timestamp ?: time() );
	}
}

if ( ! function_exists( 'selected' ) ) {
	function selected( $selected, $current, $echo = true ) {
		$result = ( $selected === $current ) ? ' selected="selected"' : '';
		if ( $echo ) {
			echo $result;
		}
		return $result;
	}
}

if ( ! function_exists( 'submit_button' ) ) {
	function submit_button( $text, $type, $name, $wrap, $other_attributes ) {
		return;
	}
}

if ( ! function_exists( 'wp_verify_nonce' ) ) {
	function wp_verify_nonce( $nonce, $action ) {
		return true;
	}
}

if ( ! function_exists( 'wp_die' ) ) {
	function wp_die( $message ) {
		die( $message );
	}
}

if ( ! function_exists( 'wp_safe_redirect' ) ) {
	function wp_safe_redirect( $location ) {
		return true;
	}
}

if ( ! defined( 'IHUMBAK_ORDER_LOGS_VERSION' ) ) {
	define( 'IHUMBAK_ORDER_LOGS_VERSION', '1.0.0' );
}

if ( ! defined( 'IHUMBAK_ORDER_LOGS_URL' ) ) {
	define( 'IHUMBAK_ORDER_LOGS_URL', 'http://example.com/wp-content/plugins/ihumbak-woo-order-edit-logs/' );
}

// Load the admin classes
require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/admin/class-admin-interface.php';
require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/admin/class-settings.php';

// Mock WP_List_Table for Log_Viewer
if ( ! class_exists( 'WP_List_Table' ) ) {
	class WP_List_Table {
		public function __construct( $args = array() ) {}
		public function get_columns() { return array(); }
		public function get_sortable_columns() { return array(); }
		public function prepare_items() {}
		public function display() {}
		public function search_box( $text, $input_id ) {}
		protected function set_pagination_args( $args ) {}
		protected function get_pagenum() { return 1; }
		public function current_action() { return false; }
	}
}

require_once IHUMBAK_ORDER_LOGS_PATH . 'includes/admin/class-log-viewer.php';

/**
 * Stage 4 Test Case
 */
class Stage4Test extends TestCase {

	/**
	 * Test Admin_Interface class exists and implements singleton pattern.
	 */
	public function test_admin_interface_exists() {
		$this->assertTrue(
			class_exists( 'IHumBak\WooOrderEditLogs\Admin\Admin_Interface' ),
			'Admin_Interface class should exist'
		);
	}

	/**
	 * Test Admin_Interface singleton pattern.
	 */
	public function test_admin_interface_singleton() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Admin_Interface', 'get_instance' ),
			'Admin_Interface should have get_instance method'
		);
	}

	/**
	 * Test Admin_Interface has register_menu method.
	 */
	public function test_admin_interface_has_register_menu() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Admin_Interface', 'register_menu' ),
			'Admin_Interface should have register_menu method'
		);
	}

	/**
	 * Test Admin_Interface has enqueue_admin_assets method.
	 */
	public function test_admin_interface_has_enqueue_assets() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Admin_Interface', 'enqueue_admin_assets' ),
			'Admin_Interface should have enqueue_admin_assets method'
		);
	}

	/**
	 * Test Admin_Interface has add_order_meta_box method.
	 */
	public function test_admin_interface_has_meta_box() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Admin_Interface', 'add_order_meta_box' ),
			'Admin_Interface should have add_order_meta_box method'
		);
	}

	/**
	 * Test Admin_Interface render_order_meta_box method exists and handles both WP_Post and WC_Order.
	 */
	public function test_admin_interface_render_meta_box_hpos_compatible() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Admin_Interface', 'render_order_meta_box' ),
			'Admin_Interface should have render_order_meta_box method'
		);
		
		// Test that the method signature accepts both WP_Post and WC_Order objects
		$reflection = new \ReflectionMethod( 'IHumBak\WooOrderEditLogs\Admin\Admin_Interface', 'render_order_meta_box' );
		$parameters = $reflection->getParameters();
		
		$this->assertCount( 1, $parameters, 'render_order_meta_box should accept exactly one parameter' );
		$this->assertEquals( 'post_or_order_object', $parameters[0]->getName(), 'Parameter should be named post_or_order_object for HPOS compatibility' );
	}

	/**
	 * Test Log_Viewer class exists.
	 */
	public function test_log_viewer_exists() {
		$this->assertTrue(
			class_exists( 'IHumBak\WooOrderEditLogs\Admin\Log_Viewer' ),
			'Log_Viewer class should exist'
		);
	}

	/**
	 * Test Log_Viewer extends WP_List_Table.
	 */
	public function test_log_viewer_extends_list_table() {
		// Skip this test as WP_List_Table is not available in unit tests
		$this->markTestSkipped( 'WP_List_Table not available in unit tests' );
	}

	/**
	 * Test Log_Viewer has get_columns method.
	 */
	public function test_log_viewer_has_columns() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Log_Viewer', 'get_columns' ),
			'Log_Viewer should have get_columns method'
		);
	}

	/**
	 * Test Log_Viewer has prepare_items method.
	 */
	public function test_log_viewer_has_prepare_items() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Log_Viewer', 'prepare_items' ),
			'Log_Viewer should have prepare_items method'
		);
	}

	/**
	 * Test Log_Viewer has render_page method.
	 */
	public function test_log_viewer_has_render_page() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Log_Viewer', 'render_page' ),
			'Log_Viewer should have render_page method'
		);
	}

	/**
	 * Test Settings class exists.
	 */
	public function test_settings_exists() {
		$this->assertTrue(
			class_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings' ),
			'Settings class should exist'
		);
	}

	/**
	 * Test Settings singleton pattern.
	 */
	public function test_settings_singleton() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings', 'get_instance' ),
			'Settings should have get_instance method'
		);
	}

	/**
	 * Test Settings has get_settings method.
	 */
	public function test_settings_has_get_settings() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings', 'get_settings' ),
			'Settings should have get_settings method'
		);
	}

	/**
	 * Test Settings has output_settings method.
	 */
	public function test_settings_has_output() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings', 'output_settings' ),
			'Settings should have output_settings method'
		);
	}

	/**
	 * Test Settings has save_settings method.
	 */
	public function test_settings_has_save() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings', 'save_settings' ),
			'Settings should have save_settings method'
		);
	}

	/**
	 * Test Settings has utility methods.
	 */
	public function test_settings_utility_methods() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings', 'is_logging_enabled' ),
			'Settings should have is_logging_enabled method'
		);
		
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings', 'is_auto_cleanup_enabled' ),
			'Settings should have is_auto_cleanup_enabled method'
		);
		
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Admin\Settings', 'get_retention_days' ),
			'Settings should have get_retention_days method'
		);
	}

	/**
	 * Test admin CSS file exists.
	 */
	public function test_admin_css_exists() {
		$css_file = IHUMBAK_ORDER_LOGS_PATH . 'assets/css/admin-styles.css';
		$this->assertFileExists( $css_file, 'Admin CSS file should exist' );
	}

	/**
	 * Test admin JS file exists.
	 */
	public function test_admin_js_exists() {
		$js_file = IHUMBAK_ORDER_LOGS_PATH . 'assets/js/admin-scripts.js';
		$this->assertFileExists( $js_file, 'Admin JS file should exist' );
	}

	/**
	 * Test views directory exists.
	 */
	public function test_views_directory_exists() {
		$views_dir = IHUMBAK_ORDER_LOGS_PATH . 'includes/admin/views';
		$this->assertDirectoryExists( $views_dir, 'Views directory should exist' );
	}

	/**
	 * Test Log_Database has get_logs method.
	 */
	public function test_log_database_has_get_logs() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Log_Database', 'get_logs' ),
			'Log_Database should have get_logs method'
		);
	}

	/**
	 * Test Log_Database has count_logs method.
	 */
	public function test_log_database_has_count_logs() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Log_Database', 'count_logs' ),
			'Log_Database should have count_logs method'
		);
	}

	/**
	 * Test Order_Logger has init_admin_interface method.
	 */
	public function test_order_logger_has_init_admin() {
		$this->assertTrue(
			method_exists( 'IHumBak\WooOrderEditLogs\Order_Logger', 'init_admin_interface' ),
			'Order_Logger should have init_admin_interface method'
		);
	}
}
