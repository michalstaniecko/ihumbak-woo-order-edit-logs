<?php
/**
 * Admin Interface Class
 *
 * Handles the admin interface for viewing logs.
 *
 * @package IHumBak\WooOrderEditLogs\Admin
 */

namespace IHumBak\WooOrderEditLogs\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Admin_Interface
 *
 * Handles the admin interface setup and initialization.
 */
class Admin_Interface {

	/**
	 * Single instance of the class.
	 *
	 * @var Admin_Interface|null
	 */
	private static $instance = null;

	/**
	 * Log viewer instance.
	 *
	 * @var Log_Viewer|null
	 */
	private $log_viewer = null;

	/**
	 * Settings instance.
	 *
	 * @var Settings|null
	 */
	private $settings = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Admin_Interface
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
	 * Initialize hooks.
	 */
	private function init_hooks() {
		// Admin menu.
		add_action( 'admin_menu', array( $this, 'register_menu' ), 60 );

		// Enqueue admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

		// Add order meta box.
		add_action( 'add_meta_boxes', array( $this, 'add_order_meta_box' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_ihumbak_get_order_logs', array( $this, 'ajax_get_order_logs' ) );
	}

	/**
	 * Register admin menu.
	 */
	public function register_menu() {
		$log_viewer = $this->get_log_viewer();
		add_submenu_page(
			'woocommerce',
			__( 'Order Logs', 'ihumbak-order-logs' ),
			__( 'Order Logs', 'ihumbak-order-logs' ),
			'manage_woocommerce',
			'ihumbak-order-logs',
			array( $log_viewer, 'render_page' )
		);
	}

	/**
	 * Enqueue admin CSS and JavaScript.
	 *
	 * @param string $hook Current admin page hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Only load on our admin pages.
		$allowed_hooks = array(
			'woocommerce_page_ihumbak-order-logs',
			'woocommerce_page_wc-orders',
			'woocommerce_page_wc-settings',
		);

		$is_order_edit = false;
		if ( function_exists( 'get_current_screen' ) ) {
			$screen = get_current_screen();
			if ( $screen && 'woocommerce_page_wc-orders' === $screen->id ) {
				$is_order_edit = true;
			}
		}

		if ( ! in_array( $hook, $allowed_hooks, true ) && ! $is_order_edit ) {
			return;
		}

		// Enqueue CSS.
		wp_enqueue_style(
			'ihumbak-order-logs-admin',
			IHUMBAK_ORDER_LOGS_URL . 'assets/css/admin-styles.css',
			array(),
			IHUMBAK_ORDER_LOGS_VERSION
		);

		// Enqueue JavaScript.
		wp_enqueue_script(
			'ihumbak-order-logs-admin',
			IHUMBAK_ORDER_LOGS_URL . 'assets/js/admin-scripts.js',
			array( 'jquery', 'jquery-ui-datepicker' ),
			IHUMBAK_ORDER_LOGS_VERSION,
			true
		);

		// Localize script.
		wp_localize_script(
			'ihumbak-order-logs-admin',
			'ihumbakOrderLogs',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'ihumbak_order_logs_nonce' ),
				'strings'  => array(
					'confirm_delete'      => __( 'Are you sure you want to delete this log entry?', 'ihumbak-order-logs' ),
					'confirm_bulk_delete' => __( 'Are you sure you want to delete the selected log entries?', 'ihumbak-order-logs' ),
					'error'               => __( 'An error occurred. Please try again.', 'ihumbak-order-logs' ),
				),
			)
		);

		// Enqueue datepicker styles.
		wp_enqueue_style( 'jquery-ui-datepicker' );
	}

	/**
	 * Get log viewer instance.
	 *
	 * @return Log_Viewer
	 */
	public function get_log_viewer() {
		return Log_Viewer::get_instance();
	}

	/**
	 * Get settings instance.
	 *
	 * @return Settings
	 */
	public function get_settings() {
		return Settings::get_instance();
	}

	/**
	 * Add order meta box.
	 */
	public function add_order_meta_box() {
		$screen = wc_get_page_screen_id( 'shop-order' );

		add_meta_box(
			'ihumbak_order_logs',
			__( 'Order Change History', 'ihumbak-order-logs' ),
			array( $this, 'render_order_meta_box' ),
			$screen,
			'normal',
			'default'
		);
	}

	/**
	 * Render order meta box.
	 *
	 * @param \WP_Post|\WC_Order $post_or_order_object Post object or Order object (HPOS).
	 */
	public function render_order_meta_box( $post_or_order_object ) {
		// Handle both CPT and HPOS modes.
		if ( $post_or_order_object instanceof \WC_Order ) {
			// HPOS mode - $post_or_order_object is a WC_Order object.
			$order_id = $post_or_order_object->get_id();
		} else {
			// CPT mode - $post_or_order_object is a WP_Post object.
			$order_id = $post_or_order_object->ID;
		}
		echo '<div class="ihumbak-order-logs-metabox">';
		$this->render_order_logs_html( $order_id, 1 );
		echo '</div>';
	}

	/**
	 * Render order logs HTML (used by both initial render and AJAX).
	 *
	 * @param int $order_id Order ID.
	 * @param int $page Current page number.
	 */
	private function render_order_logs_html( $order_id, $page = 1 ) {
		$per_page = 10;
		$offset = ( $page - 1 ) * $per_page;

		// Get logs for this order.
		$logs = \IHumBak\WooOrderEditLogs\Log_Database::get_logs_by_order(
			$order_id,
			array(
				'limit'  => $per_page,
				'offset' => $offset,
			)
		);

		// Get total count for pagination.
		$total_logs = \IHumBak\WooOrderEditLogs\Log_Database::count_logs_by_order( $order_id );
		$total_pages = ceil( $total_logs / $per_page );

		if ( ! empty( $logs ) ) : ?>
			<table class="ihumbak-order-logs-table">
				<thead>
				<tr>
					<th><?php esc_html_e( 'Date/Time', 'ihumbak-order-logs' ); ?></th>
					<th><?php esc_html_e( 'User', 'ihumbak-order-logs' ); ?></th>
					<th><?php esc_html_e( 'Action', 'ihumbak-order-logs' ); ?></th>
					<th><?php esc_html_e( 'Field', 'ihumbak-order-logs' ); ?></th>
					<th><?php esc_html_e( 'Old Value', 'ihumbak-order-logs' ); ?></th>
					<th><?php esc_html_e( 'New Value', 'ihumbak-order-logs' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ( $logs as $log ) : ?>
					<tr>
						<td class="log-timestamp">
							<?php
							$timestamp = strtotime( $log->timestamp );
							echo esc_html( date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), $timestamp ) );
							?>
						</td>
						<td class="log-user">
							<?php echo esc_html( $log->user_display_name ); ?> (<?php echo esc_html( $log->user_id ); ?>
							)
						</td>
						<td>
							<?php
							$logger = \IHumBak\WooOrderEditLogs\Order_Logger::get_instance();
							$action_types = $logger->get_action_types();
							$label = isset( $action_types[ $log->action_type ] )
								? $action_types[ $log->action_type ]
								: $log->action_type;
							echo esc_html( $label );
							?>
						</td>
						<td>
							<?php echo esc_html( $log->field_name ?: '—' ); ?>
						</td>
						<td class="log-value">
							<?php
							if ( empty( $log->old_value ) ) {
								echo '<em>' . esc_html__( '(empty)', 'ihumbak-order-logs' ) . '</em>';
							} else {
								$value = esc_html( $log->old_value );
								if ( strlen( $value ) > 50 ) {
									echo esc_html( substr( $value, 0, 50 ) ) . '...';
								} else {
									echo $value;
								}
							}
							?>
						</td>
						<td class="log-value">
							<?php
							if ( empty( $log->new_value ) ) {
								echo '<em>' . esc_html__( '(empty)', 'ihumbak-order-logs' ) . '</em>';
							} else {
								$value = esc_html( $log->new_value );
								if ( strlen( $value ) > 50 ) {
									echo esc_html( substr( $value, 0, 50 ) ) . '...';
								} else {
									echo $value;
								}
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<?php if ( $total_pages > 1 ) : ?>
				<div class="ihumbak-logs-pagination">
					<?php
					// Previous button.
					if ( $page > 1 ) {
						printf(
							'<a href="#" class="ihumbak-logs-page-link button" data-page="%d" data-order-id="%d">%s</a> ',
							$page - 1,
							$order_id,
							esc_html__( '« Previous', 'ihumbak-order-logs' )
						);
					}

					// Page numbers.
					for ( $i = 1; $i <= $total_pages; $i++ ) {
						if ( $i === $page ) {
							printf( '<span class="current-page">%d</span> ', $i );
						} else {
							printf(
								'<a href="#" class="ihumbak-logs-page-link" data-page="%d" data-order-id="%d">%d</a> ',
								$i,
								$order_id,
								$i
							);
						}
					}

					// Next button.
					if ( $page < $total_pages ) {
						printf(
							'<a href="#" class="ihumbak-logs-page-link button" data-page="%d" data-order-id="%d">%s</a>',
							$page + 1,
							$order_id,
							esc_html__( 'Next »', 'ihumbak-order-logs' )
						);
					}
					?>
				</div>
			<?php endif; ?>
		<?php else : ?>
			<div class="ihumbak-order-logs-empty">
				<?php esc_html_e( 'No changes recorded yet.', 'ihumbak-order-logs' ); ?>
			</div>
		<?php endif;
	}

	/**
	 * AJAX handler for loading order logs with pagination.
	 */
	public function ajax_get_order_logs() {
		// Verify nonce.
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'ihumbak_order_logs_nonce' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'ihumbak-order-logs' ) ) );
		}

		// Verify required parameters.
		if ( ! isset( $_POST['order_id'] ) || ! isset( $_POST['page'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing parameters.', 'ihumbak-order-logs' ) ) );
		}

		$order_id = absint( $_POST['order_id'] );
		$page = absint( $_POST['page'] );

		if ( $order_id <= 0 || $page <= 0 ) {
			wp_send_json_error( array( 'message' => __( 'Invalid parameters.', 'ihumbak-order-logs' ) ) );
		}

		// Check user capabilities.
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to view order logs.', 'ihumbak-order-logs' ) ) );
		}

		// Render the logs HTML.
		ob_start();
		$this->render_order_logs_html( $order_id, $page );
		$html = ob_get_clean();

		wp_send_json_success( array( 'html' => $html ) );
	}
}
