<?php
/**
 * Log Database Class
 *
 * Handles database operations for order logs.
 *
 * @package IHumBak\WooOrderEditLogs
 */

namespace IHumBak\WooOrderEditLogs;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Log_Database
 *
 * Manages database schema and operations for order logs.
 */
class Log_Database {

	/**
	 * Database version.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.0.0';

	/**
	 * Get the table name.
	 *
	 * @return string Table name with WordPress prefix.
	 */
	public static function get_table_name() {
		global $wpdb;
		return $wpdb->prefix . 'ihumbak_order_logs';
	}

	/**
	 * Create database tables.
	 *
	 * Creates the main logs table if it doesn't exist.
	 */
	public static function create_tables() {
		global $wpdb;

		$table_name      = self::get_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE {$table_name} (
			log_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			order_id bigint(20) UNSIGNED NOT NULL,
			user_id bigint(20) UNSIGNED NOT NULL,
			user_display_name varchar(250) NOT NULL,
			user_role varchar(100) NOT NULL,
			timestamp datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			action_type varchar(100) NOT NULL,
			field_name varchar(255) DEFAULT NULL,
			old_value longtext DEFAULT NULL,
			new_value longtext DEFAULT NULL,
			ip_address varchar(45) DEFAULT NULL,
			user_agent varchar(500) DEFAULT NULL,
			additional_data longtext DEFAULT NULL,
			PRIMARY KEY  (log_id),
			KEY order_id (order_id),
			KEY user_id (user_id),
			KEY action_type (action_type),
			KEY timestamp (timestamp)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		// Update database version.
		update_option( 'ihumbak_order_logs_db_version', self::DB_VERSION );
	}

	/**
	 * Check if database needs upgrade.
	 *
	 * @return bool True if upgrade is needed, false otherwise.
	 */
	public static function needs_upgrade() {
		$current_version = get_option( 'ihumbak_order_logs_db_version', '0' );
		return version_compare( $current_version, self::DB_VERSION, '<' );
	}

	/**
	 * Insert a new log entry.
	 *
	 * @param array $data Log data to insert.
	 * @return int|false The number of rows inserted, or false on error.
	 */
	public static function insert_log( $data ) {
		global $wpdb;

		$table_name = self::get_table_name();

		// Prepare default values.
		$defaults = array(
			'user_id'           => get_current_user_id(),
			'user_display_name' => wp_get_current_user()->display_name,
			'user_role'         => self::get_user_role(),
			'timestamp'         => current_time( 'mysql' ),
			'ip_address'        => self::get_user_ip(),
			'user_agent'        => self::get_user_agent(),
		);

		// Merge with provided data.
		$data = wp_parse_args( $data, $defaults );

		// Validate required fields.
		if ( empty( $data['order_id'] ) || empty( $data['action_type'] ) ) {
			return false;
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->insert(
			$table_name,
			$data,
			array(
				'%d', // order_id
				'%d', // user_id
				'%s', // user_display_name
				'%s', // user_role
				'%s', // timestamp
				'%s', // action_type
				'%s', // field_name
				'%s', // old_value
				'%s', // new_value
				'%s', // ip_address
				'%s', // user_agent
				'%s', // additional_data
			)
		);
	}

	/**
	 * Get logs for a specific order.
	 *
	 * @param int   $order_id Order ID.
	 * @param array $args     Optional. Additional query arguments.
	 * @return array Array of log entries.
	 */
	public static function get_logs_by_order( $order_id, $args = array() ) {
		global $wpdb;

		$table_name = self::get_table_name();

		$defaults = array(
			'limit'      => 100,
			'offset'     => 0,
			'order_by'   => 'timestamp',
			'order'      => 'DESC',
			'action_type' => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$sql = $wpdb->prepare(
			"SELECT * FROM {$table_name} WHERE order_id = %d",
			$order_id
		);

		if ( ! empty( $args['action_type'] ) ) {
			$sql .= $wpdb->prepare( ' AND action_type = %s', $args['action_type'] );
		}

		$sql .= $wpdb->prepare(
			' ORDER BY %s %s LIMIT %d OFFSET %d',
			esc_sql( $args['order_by'] ),
			esc_sql( $args['order'] ),
			$args['limit'],
			$args['offset']
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql );
	}

	/**
	 * Get all logs with optional filters.
	 *
	 * @param array $args Query arguments.
	 * @return array Array of log entries.
	 */
	public static function get_logs( $args = array() ) {
		global $wpdb;

		$table_name = self::get_table_name();

		$defaults = array(
			'limit'       => 20,
			'offset'      => 0,
			'order_by'    => 'timestamp',
			'order'       => 'DESC',
			'action_type' => '',
			'user_id'     => '',
			'order_id'    => '',
			'date_from'   => '',
			'date_to'     => '',
			'search'      => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$where = array( '1=1' );
		$params = array();

		// Filter by action type.
		if ( ! empty( $args['action_type'] ) ) {
			$where[] = 'action_type = %s';
			$params[] = $args['action_type'];
		}

		// Filter by user ID.
		if ( ! empty( $args['user_id'] ) ) {
			$where[] = 'user_id = %d';
			$params[] = absint( $args['user_id'] );
		}

		// Filter by order ID.
		if ( ! empty( $args['order_id'] ) ) {
			$where[] = 'order_id = %d';
			$params[] = absint( $args['order_id'] );
		}

		// Filter by date range.
		if ( ! empty( $args['date_from'] ) ) {
			$where[] = 'timestamp >= %s';
			$params[] = $args['date_from'] . ' 00:00:00';
		}

		if ( ! empty( $args['date_to'] ) ) {
			$where[] = 'timestamp <= %s';
			$params[] = $args['date_to'] . ' 23:59:59';
		}

		// Search in multiple fields.
		if ( ! empty( $args['search'] ) ) {
			$where[] = '(field_name LIKE %s OR old_value LIKE %s OR new_value LIKE %s OR user_display_name LIKE %s)';
			$search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$params[] = $search_term;
			$params[] = $search_term;
			$params[] = $search_term;
			$params[] = $search_term;
		}

		$where_clause = implode( ' AND ', $where );

		// Build query.
		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = $wpdb->prepare( "SELECT * FROM {$table_name} WHERE {$where_clause}", $params );
		} else {
			$sql = "SELECT * FROM {$table_name} WHERE {$where_clause}";
		}

		$sql .= $wpdb->prepare(
			' ORDER BY %s %s LIMIT %d OFFSET %d',
			esc_sql( $args['order_by'] ),
			esc_sql( $args['order'] ),
			$args['limit'],
			$args['offset']
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return $wpdb->get_results( $sql );
	}

	/**
	 * Count total logs with optional filters.
	 *
	 * @param array $args Query arguments (same as get_logs).
	 * @return int Total number of logs.
	 */
	public static function count_logs( $args = array() ) {
		global $wpdb;

		$table_name = self::get_table_name();

		$defaults = array(
			'action_type' => '',
			'user_id'     => '',
			'order_id'    => '',
			'date_from'   => '',
			'date_to'     => '',
			'search'      => '',
		);

		$args = wp_parse_args( $args, $defaults );

		$where = array( '1=1' );
		$params = array();

		// Filter by action type.
		if ( ! empty( $args['action_type'] ) ) {
			$where[] = 'action_type = %s';
			$params[] = $args['action_type'];
		}

		// Filter by user ID.
		if ( ! empty( $args['user_id'] ) ) {
			$where[] = 'user_id = %d';
			$params[] = absint( $args['user_id'] );
		}

		// Filter by order ID.
		if ( ! empty( $args['order_id'] ) ) {
			$where[] = 'order_id = %d';
			$params[] = absint( $args['order_id'] );
		}

		// Filter by date range.
		if ( ! empty( $args['date_from'] ) ) {
			$where[] = 'timestamp >= %s';
			$params[] = $args['date_from'] . ' 00:00:00';
		}

		if ( ! empty( $args['date_to'] ) ) {
			$where[] = 'timestamp <= %s';
			$params[] = $args['date_to'] . ' 23:59:59';
		}

		// Search in multiple fields.
		if ( ! empty( $args['search'] ) ) {
			$where[] = '(field_name LIKE %s OR old_value LIKE %s OR new_value LIKE %s OR user_display_name LIKE %s)';
			$search_term = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$params[] = $search_term;
			$params[] = $search_term;
			$params[] = $search_term;
			$params[] = $search_term;
		}

		$where_clause = implode( ' AND ', $where );

		// Build query.
		if ( ! empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}", $params );
		} else {
			$sql = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
		return absint( $wpdb->get_var( $sql ) );
	}

	/**
	 * Delete old logs based on retention period.
	 *
	 * @param int $days Number of days to retain logs.
	 * @return int|false Number of rows deleted, or false on error.
	 */
	public static function delete_old_logs( $days = 90 ) {
		global $wpdb;

		$table_name = self::get_table_name();
		$date       = gmdate( 'Y-m-d H:i:s', strtotime( "-{$days} days" ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		return $wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$table_name} WHERE timestamp < %s",
				$date
			)
		);
	}

	/**
	 * Get current user's role.
	 *
	 * @return string User role.
	 */
	private static function get_user_role() {
		$user = wp_get_current_user();
		if ( empty( $user->roles ) ) {
			return 'guest';
		}
		return $user->roles[0];
	}

	/**
	 * Get user's IP address.
	 *
	 * @return string IP address.
	 */
	private static function get_user_ip() {
		// Check for shared internet/ISP IP.
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) && filter_var( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ), FILTER_VALIDATE_IP ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['HTTP_CLIENT_IP'] ) );
		}

		// Check for IPs passing through proxies.
		if ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ips = explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) );
			$ip  = trim( $ips[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}

		// Standard remote address.
		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) && filter_var( wp_unslash( $_SERVER['REMOTE_ADDR'] ), FILTER_VALIDATE_IP ) ) {
			return sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
		}

		return '0.0.0.0';
	}

	/**
	 * Get user agent string.
	 *
	 * @return string User agent.
	 */
	private static function get_user_agent() {
		if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ), 0, 500 );
		}
		return '';
	}
}
