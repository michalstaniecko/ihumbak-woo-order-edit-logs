<?php
/**
 * Log Viewer Class
 *
 * Handles displaying logs in the admin area.
 *
 * @package IHumBak\WooOrderEditLogs\Admin
 */

namespace IHumBak\WooOrderEditLogs\Admin;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load WP_List_Table if not loaded.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Log_Viewer
 *
 * Displays logs in the admin area using WP_List_Table.
 */
class Log_Viewer extends \WP_List_Table {

	/**
	 * Single instance of the class.
	 *
	 * @var Log_Viewer|null
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Log_Viewer
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
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Log', 'ihumbak-order-logs' ),
				'plural'   => __( 'Logs', 'ihumbak-order-logs' ),
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get table columns.
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'log_id'      => __( 'Log ID', 'ihumbak-order-logs' ),
			'order_id'    => __( 'Order', 'ihumbak-order-logs' ),
			'timestamp'   => __( 'Date/Time', 'ihumbak-order-logs' ),
			'user'        => __( 'User', 'ihumbak-order-logs' ),
			'action_type' => __( 'Action', 'ihumbak-order-logs' ),
			'field_name'  => __( 'Field', 'ihumbak-order-logs' ),
			'old_value'   => __( 'Old Value', 'ihumbak-order-logs' ),
			'new_value'   => __( 'New Value', 'ihumbak-order-logs' ),
			'ip_address'  => __( 'IP Address', 'ihumbak-order-logs' ),
		);
	}

	/**
	 * Get sortable columns.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		return array(
			'log_id'      => array( 'log_id', true ),
			'order_id'    => array( 'order_id', false ),
			'timestamp'   => array( 'timestamp', false ),
			'user'        => array( 'user_display_name', false ),
			'action_type' => array( 'action_type', false ),
		);
	}

	/**
	 * Get bulk actions.
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'ihumbak-order-logs' ),
		);
	}

	/**
	 * Column default.
	 *
	 * @param object $item        Log item.
	 * @param string $column_name Column name.
	 * @return string
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'log_id':
			case 'field_name':
			case 'ip_address':
				return esc_html( $item->$column_name );

			case 'old_value':
			case 'new_value':
				$value = $item->$column_name;
				if ( empty( $value ) ) {
					return '<em>' . esc_html__( '(empty)', 'ihumbak-order-logs' ) . '</em>';
				}
				// Truncate long values.
				$value = esc_html( $value );
				if ( strlen( $value ) > 50 ) {
					$value = substr( $value, 0, 50 ) . '...';
				}
				return $value;

			default:
				return '';
		}
	}

	/**
	 * Checkbox column.
	 *
	 * @param object $item Log item.
	 * @return string
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="log_id[]" value="%s" />',
			esc_attr( $item->log_id )
		);
	}

	/**
	 * Order ID column.
	 *
	 * @param object $item Log item.
	 * @return string
	 */
	public function column_order_id( $item ) {
		$order_id = absint( $item->order_id );
		$edit_url = admin_url( 'post.php?post=' . $order_id . '&action=edit' );
		return sprintf(
			'<a href="%s">#%s</a>',
			esc_url( $edit_url ),
			esc_html( $order_id )
		);
	}

	/**
	 * Timestamp column.
	 *
	 * @param object $item Log item.
	 * @return string
	 */
	public function column_timestamp( $item ) {
		$timestamp = strtotime( $item->timestamp );
		$date      = date_i18n( get_option( 'date_format' ), $timestamp );
		$time      = date_i18n( get_option( 'time_format' ), $timestamp );
		return sprintf(
			'%s<br/><small>%s</small>',
			esc_html( $date ),
			esc_html( $time )
		);
	}

	/**
	 * User column.
	 *
	 * @param object $item Log item.
	 * @return string
	 */
	public function column_user( $item ) {
		$user_id = absint( $item->user_id );
		if ( $user_id > 0 ) {
			$user_url = admin_url( 'user-edit.php?user_id=' . $user_id );
			return sprintf(
				'<a href="%s">%s</a><br/><small>%s</small>',
				esc_url( $user_url ),
				esc_html( $item->user_display_name ),
				esc_html( $item->user_role )
			);
		}
		return esc_html( $item->user_display_name );
	}

	/**
	 * Action type column.
	 *
	 * @param object $item Log item.
	 * @return string
	 */
	public function column_action_type( $item ) {
		$logger = \IHumBak\WooOrderEditLogs\Order_Logger::get_instance();
		$action_types = $logger->get_action_types();
		
		$label = isset( $action_types[ $item->action_type ] ) 
			? $action_types[ $item->action_type ] 
			: $item->action_type;

		return esc_html( $label );
	}

	/**
	 * Prepare items for display.
	 */
	public function prepare_items() {
		// Get current page.
		$current_page = $this->get_pagenum();
		$per_page     = 20;

		// Get sorting parameters.
		$orderby = ! empty( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'timestamp';
		$order   = ! empty( $_REQUEST['order'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'DESC';

		// Get filters.
		$filters = $this->get_filters();

		// Prepare query args.
		$args = array_merge(
			$filters,
			array(
				'limit'    => $per_page,
				'offset'   => ( $current_page - 1 ) * $per_page,
				'order_by' => $orderby,
				'order'    => strtoupper( $order ),
			)
		);

		// Get logs.
		$logs = \IHumBak\WooOrderEditLogs\Log_Database::get_logs( $args );
		$total_items = \IHumBak\WooOrderEditLogs\Log_Database::count_logs( $filters );

		// Set items.
		$this->items = $logs;

		// Set pagination.
		$this->set_pagination_args(
			array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page ),
			)
		);

		// Set columns.
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	}

	/**
	 * Get filters from request.
	 *
	 * @return array
	 */
	private function get_filters() {
		$filters = array();

		if ( ! empty( $_REQUEST['action_type'] ) && 'all' !== $_REQUEST['action_type'] ) {
			$filters['action_type'] = sanitize_text_field( wp_unslash( $_REQUEST['action_type'] ) );
		}

		if ( ! empty( $_REQUEST['user_id'] ) && 'all' !== $_REQUEST['user_id'] ) {
			$filters['user_id'] = absint( $_REQUEST['user_id'] );
		}

		if ( ! empty( $_REQUEST['order_id'] ) ) {
			$filters['order_id'] = absint( $_REQUEST['order_id'] );
		}

		if ( ! empty( $_REQUEST['date_from'] ) ) {
			$filters['date_from'] = sanitize_text_field( wp_unslash( $_REQUEST['date_from'] ) );
		}

		if ( ! empty( $_REQUEST['date_to'] ) ) {
			$filters['date_to'] = sanitize_text_field( wp_unslash( $_REQUEST['date_to'] ) );
		}

		if ( ! empty( $_REQUEST['s'] ) ) {
			$filters['search'] = sanitize_text_field( wp_unslash( $_REQUEST['s'] ) );
		}

		return $filters;
	}

	/**
	 * Display extra table navigation.
	 *
	 * @param string $which Position (top or bottom).
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' !== $which ) {
			return;
		}
		?>
		<div class="alignleft actions">
			<?php $this->display_filters(); ?>
		</div>
		<?php
	}

	/**
	 * Display filter controls.
	 */
	private function display_filters() {
		$logger = \IHumBak\WooOrderEditLogs\Order_Logger::get_instance();
		$action_types = $logger->get_action_types();
		$current_action_type = ! empty( $_REQUEST['action_type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['action_type'] ) ) : '';
		$current_date_from = ! empty( $_REQUEST['date_from'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['date_from'] ) ) : '';
		$current_date_to = ! empty( $_REQUEST['date_to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['date_to'] ) ) : '';
		$current_order_id = ! empty( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : '';

		// Action type filter.
		echo '<select name="action_type" id="action_type">';
		echo '<option value="all">' . esc_html__( 'All Actions', 'ihumbak-order-logs' ) . '</option>';
		foreach ( $action_types as $type => $label ) {
			printf(
				'<option value="%s"%s>%s</option>',
				esc_attr( $type ),
				selected( $current_action_type, $type, false ),
				esc_html( $label )
			);
		}
		echo '</select>';

		// Date range filters.
		printf(
			'<input type="text" name="date_from" id="date_from" placeholder="%s" value="%s" class="ihumbak-datepicker" />',
			esc_attr__( 'From Date', 'ihumbak-order-logs' ),
			esc_attr( $current_date_from )
		);
		
		printf(
			'<input type="text" name="date_to" id="date_to" placeholder="%s" value="%s" class="ihumbak-datepicker" />',
			esc_attr__( 'To Date', 'ihumbak-order-logs' ),
			esc_attr( $current_date_to )
		);

		// Order ID filter.
		printf(
			'<input type="number" name="order_id" id="order_id" placeholder="%s" value="%s" />',
			esc_attr__( 'Order ID', 'ihumbak-order-logs' ),
			esc_attr( $current_order_id )
		);

		submit_button( __( 'Filter', 'ihumbak-order-logs' ), '', 'filter_action', false );
	}

	/**
	 * Render the page.
	 */
	public function render_page() {
		// Process bulk actions.
		$this->process_bulk_action();

		// Prepare items.
		$this->prepare_items();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Order Logs', 'ihumbak-order-logs' ); ?></h1>
			
			<form method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ?? '' ); ?>" />
				<?php
				$this->search_box( __( 'Search Logs', 'ihumbak-order-logs' ), 'log-search' );
				$this->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Process bulk actions.
	 */
	private function process_bulk_action() {
		// Check for bulk delete.
		if ( 'delete' === $this->current_action() ) {
			// Verify nonce.
			if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'bulk-logs' ) ) {
				wp_die( esc_html__( 'Invalid nonce', 'ihumbak-order-logs' ) );
			}

			// Get selected log IDs.
			if ( ! empty( $_REQUEST['log_id'] ) && is_array( $_REQUEST['log_id'] ) ) {
				global $wpdb;
				$table_name = \IHumBak\WooOrderEditLogs\Log_Database::get_table_name();
				$log_ids = array_map( 'absint', $_REQUEST['log_id'] );
				
				// Delete logs.
				foreach ( $log_ids as $log_id ) {
					// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
					$wpdb->delete(
						$table_name,
						array( 'log_id' => $log_id ),
						array( '%d' )
					);
				}

				// Redirect to avoid resubmission.
				wp_safe_redirect( admin_url( 'admin.php?page=ihumbak-order-logs' ) );
				exit;
			}
		}
	}
}
