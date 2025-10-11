/**
 * Admin Scripts for iHumBak Order Logs
 *
 * @package IHumBak\WooOrderEditLogs
 */

(function ($) {
	'use strict';

	/**
	 * Initialize datepickers
	 */
	function initDatepickers() {
		if ($.fn.datepicker) {
			$('.ihumbak-datepicker').datepicker({
				dateFormat: 'yy-mm-dd',
				changeMonth: true,
				changeYear: true,
				yearRange: '-10:+0'
			});
		}
	}

	/**
	 * Initialize delete confirmations
	 */
	function initDeleteConfirmations() {
		// Bulk delete confirmation
		$('#doaction, #doaction2').on('click', function (e) {
			var action = $(this).siblings('select').val();
			if (action === 'delete') {
				if (!confirm(ihumbakOrderLogs.strings.confirm_bulk_delete)) {
					e.preventDefault();
					return false;
				}
			}
		});
	}

	/**
	 * Initialize log details modal
	 */
	function initLogModal() {
		// Create modal if it doesn't exist
		if ($('#ihumbak-log-modal').length === 0) {
			$('body').append(
				'<div id="ihumbak-log-modal" class="ihumbak-log-modal">' +
				'<div class="ihumbak-log-modal-content">' +
				'<div class="ihumbak-log-modal-header">' +
				'<h2>' + ihumbakOrderLogs.strings.log_details + '</h2>' +
				'<button class="ihumbak-log-modal-close">&times;</button>' +
				'</div>' +
				'<div id="ihumbak-log-modal-body"></div>' +
				'</div>' +
				'</div>'
			);
		}

		// Close modal on X click
		$(document).on('click', '.ihumbak-log-modal-close', function () {
			$('#ihumbak-log-modal').hide();
		});

		// Close modal on outside click
		$(document).on('click', '#ihumbak-log-modal', function (e) {
			if (e.target.id === 'ihumbak-log-modal') {
				$(this).hide();
			}
		});

		// View log details (placeholder for future implementation)
		$(document).on('click', '.view-log-details', function (e) {
			e.preventDefault();
			var logId = $(this).data('log-id');
			loadLogDetails(logId);
		});
	}

	/**
	 * Load log details via AJAX
	 *
	 * @param {number} logId Log ID to load
	 */
	function loadLogDetails(logId) {
		var modalBody = $('#ihumbak-log-modal-body');
		modalBody.html('<div class="ihumbak-logs-loading"></div>');
		$('#ihumbak-log-modal').show();

		$.ajax({
			url: ihumbakOrderLogs.ajax_url,
			type: 'POST',
			data: {
				action: 'ihumbak_get_log_details',
				log_id: logId,
				nonce: ihumbakOrderLogs.nonce
			},
			success: function (response) {
				if (response.success) {
					modalBody.html(response.data.html);
				} else {
					modalBody.html('<p>' + ihumbakOrderLogs.strings.error + '</p>');
				}
			},
			error: function () {
				modalBody.html('<p>' + ihumbakOrderLogs.strings.error + '</p>');
			}
		});
	}

	/**
	 * Initialize order meta box AJAX pagination
	 */
	function initOrderMetaBoxPagination() {
		$(document).on('click', '.ihumbak-logs-page-link', function (e) {
			e.preventDefault();
			var page = $(this).data('page');
			var orderId = $(this).data('order-id');
			loadOrderLogs(orderId, page);
		});
	}

	/**
	 * Load order logs via AJAX
	 *
	 * @param {number} orderId Order ID
	 * @param {number} page    Page number
	 */
	function loadOrderLogs(orderId, page) {
		var container = $('.ihumbak-order-logs-metabox');
		container.html('<div class="ihumbak-logs-loading"></div>');

		$.ajax({
			url: ihumbakOrderLogs.ajax_url,
			type: 'POST',
			data: {
				action: 'ihumbak_get_order_logs',
				order_id: orderId,
				page: page,
				nonce: ihumbakOrderLogs.nonce
			},
			success: function (response) {
				if (response.success) {
					container.html(response.data.html);
				} else {
					container.html('<p>' + ihumbakOrderLogs.strings.error + '</p>');
				}
			},
			error: function () {
				container.html('<p>' + ihumbakOrderLogs.strings.error + '</p>');
			}
		});
	}

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function () {
		initDatepickers();
		initDeleteConfirmations();
		initLogModal();
		initOrderMetaBoxPagination();
	});

})(jQuery);
