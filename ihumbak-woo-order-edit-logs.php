<?php
/**
 * Plugin Name: iHumBak - WooCommerce Order Edit Logs
 * Plugin URI: https://github.com/michalstaniecko/ihumbak-woo-order-edit-logs
 * Description: Automatyczne zbieranie i przechowywanie szczegółowych logów wszystkich zmian przeprowadzanych w zamówieniach WooCommerce
 * Version: 1.0.0
 * Author: Michał Staniećko
 * Author URI: https://ihumbak.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ihumbak-order-logs
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 9.0
 *
 * @package IHumBak\WooOrderEditLogs
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define plugin constants.
define( 'IHUMBAK_ORDER_LOGS_VERSION', '1.0.0' );
define( 'IHUMBAK_ORDER_LOGS_FILE', __FILE__ );
define( 'IHUMBAK_ORDER_LOGS_PATH', plugin_dir_path( __FILE__ ) );
define( 'IHUMBAK_ORDER_LOGS_URL', plugin_dir_url( __FILE__ ) );
define( 'IHUMBAK_ORDER_LOGS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Check if requirements are met before loading the plugin.
 *
 * @return bool True if all requirements are met, false otherwise.
 */
function ihumbak_order_logs_check_requirements() {
	$errors = array();

	// Check PHP version.
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		$errors[] = sprintf(
			/* translators: %s: Required PHP version */
			__( 'iHumBak Order Logs requires PHP version %s or higher.', 'ihumbak-order-logs' ),
			'7.4'
		);
	}

	// Check WordPress version.
	global $wp_version;
	if ( version_compare( $wp_version, '5.8', '<' ) ) {
		$errors[] = sprintf(
			/* translators: %s: Required WordPress version */
			__( 'iHumBak Order Logs requires WordPress version %s or higher.', 'ihumbak-order-logs' ),
			'5.8'
		);
	}

	// Check if WooCommerce is active.
	if ( ! class_exists( 'WooCommerce' ) ) {
		$errors[] = __( 'iHumBak Order Logs requires WooCommerce to be installed and activated.', 'ihumbak-order-logs' );
	} elseif ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '6.0', '<' ) ) {
		// Check WooCommerce version.
		$errors[] = sprintf(
			/* translators: %s: Required WooCommerce version */
			__( 'iHumBak Order Logs requires WooCommerce version %s or higher.', 'ihumbak-order-logs' ),
			'6.0'
		);
	}

	// Display errors if any.
	if ( ! empty( $errors ) ) {
		add_action(
			'admin_notices',
			function () use ( $errors ) {
				?>
				<div class="notice notice-error">
					<p><strong><?php esc_html_e( 'iHumBak Order Logs Error:', 'ihumbak-order-logs' ); ?></strong></p>
					<ul>
						<?php foreach ( $errors as $error ) : ?>
							<li><?php echo esc_html( $error ); ?></li>
						<?php endforeach; ?>
					</ul>
				</div>
				<?php
			}
		);
		return false;
	}

	return true;
}

/**
 * Declare HPOS compatibility.
 */
function ihumbak_order_logs_declare_hpos_compatibility() {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
			'custom_order_tables',
			__FILE__,
			true
		);
	}
}
add_action( 'before_woocommerce_init', 'ihumbak_order_logs_declare_hpos_compatibility' );

/**
 * Load plugin textdomain.
 */
function ihumbak_order_logs_load_textdomain() {
	load_plugin_textdomain(
		'ihumbak-order-logs',
		false,
		dirname( IHUMBAK_ORDER_LOGS_BASENAME ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'ihumbak_order_logs_load_textdomain' );

/**
 * Initialize the plugin.
 */
function ihumbak_order_logs_init() {
	// Check requirements.
	if ( ! ihumbak_order_logs_check_requirements() ) {
		return;
	}

	// Load Composer autoloader if available.
	if ( file_exists( IHUMBAK_ORDER_LOGS_PATH . 'vendor/autoload.php' ) ) {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'vendor/autoload.php';
	}

	// Load PSR-4 autoloader.
	spl_autoload_register( 'ihumbak_order_logs_autoloader' );

	// Initialize main plugin class.
	if ( class_exists( 'IHumBak\WooOrderEditLogs\Order_Logger' ) ) {
		\IHumBak\WooOrderEditLogs\Order_Logger::get_instance();
	}
}
add_action( 'plugins_loaded', 'ihumbak_order_logs_init', 20 );

/**
 * PSR-4 Autoloader for plugin classes.
 *
 * @param string $class The fully-qualified class name.
 */
function ihumbak_order_logs_autoloader( $class ) {
	// Project-specific namespace prefix.
	$prefix = 'IHumBak\\WooOrderEditLogs\\';

	// Base directory for the namespace prefix.
	$base_dir = IHUMBAK_ORDER_LOGS_PATH . 'includes/';

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		// No, move to the next registered autoloader.
		return;
	}

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Convert namespace separators to directory separators.
	// Convert class name to file name (e.g., Order_Logger to class-order-logger.php).
	$relative_class = str_replace( '\\', '/', $relative_class );
	
	// Split by last slash to get the class name.
	$parts = explode( '/', $relative_class );
	$class_name = array_pop( $parts );
	
	// Convert CamelCase or Snake_Case to kebab-case and add class- prefix.
	$class_name = strtolower( preg_replace( '/([a-z])([A-Z])/', '$1-$2', $class_name ) );
	$class_name = str_replace( '_', '-', $class_name );
	$file_name = 'class-' . $class_name . '.php';
	
	// Rebuild the path.
	if ( ! empty( $parts ) ) {
		$relative_path = implode( '/', $parts ) . '/' . $file_name;
	} else {
		$relative_path = $file_name;
	}

	// Build the full file path.
	$file = $base_dir . $relative_path;

	// If the file exists, require it.
	if ( file_exists( $file ) ) {
		require $file;
	}
}

/**
 * Plugin activation hook.
 */
function ihumbak_order_logs_activate() {
	// Check requirements before activation.
	if ( ! ihumbak_order_logs_check_requirements() ) {
		wp_die(
			esc_html__( 'Plugin requirements are not met. Please check the error messages above.', 'ihumbak-order-logs' ),
			esc_html__( 'Plugin Activation Error', 'ihumbak-order-logs' ),
			array( 'back_link' => true )
		);
	}

	// Load Composer autoloader if available.
	if ( file_exists( IHUMBAK_ORDER_LOGS_PATH . 'vendor/autoload.php' ) ) {
		require_once IHUMBAK_ORDER_LOGS_PATH . 'vendor/autoload.php';
	}

	// Load autoloader.
	spl_autoload_register( 'ihumbak_order_logs_autoloader' );

	// Create database tables.
	if ( class_exists( 'IHumBak\WooOrderEditLogs\Log_Database' ) ) {
		\IHumBak\WooOrderEditLogs\Log_Database::create_tables();
	}

	// Set default options.
	add_option( 'ihumbak_order_logs_version', IHUMBAK_ORDER_LOGS_VERSION );
	add_option( 'ihumbak_order_logs_db_version', '1.0.0' );

	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ihumbak_order_logs_activate' );

/**
 * Plugin deactivation hook.
 */
function ihumbak_order_logs_deactivate() {
	// Flush rewrite rules.
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ihumbak_order_logs_deactivate' );
