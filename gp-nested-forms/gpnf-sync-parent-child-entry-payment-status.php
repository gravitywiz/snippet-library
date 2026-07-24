<?php
/**
 * Gravity Perks // Nested Forms // Sync Child Entry Payment Details w/ Parent
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Sync the payment details of child entries with their parent's. This is useful with GP Limit Choices and GP Inventory
 * to ensure that limits/inventories applied to child field's will not include child entries where payment has not been
 * collected for their parent entry.
 *
 * With GP Bookings, this also updates the status of bookings owned by child entries to match GP Bookings' "Confirm On
 * Payment Status" and "Cancel On Payment Status" settings.
 *
 * Plugin Name:  GP Nested Forms — Sync Parent/Child Entry Payment Details
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Sync the payment details of child entries with their parent's.
 * Author:       Gravity Wiz
 * Version:      0.4
 * Author URI:   https://gravitywiz.com
 */
add_action( 'gform_after_submission', function( $entry, $form ) {
	gpnf_sync_child_entries_payment_details( $entry );
}, 10, 2 );

add_action( 'gform_post_update_entry', function( $entry, $original_entry ) {
	gpnf_sync_child_entries_payment_details( $entry );
}, 10, 2 );

add_action( 'gform_after_update_entry', function( $form, $entry_id, $original_entry ) {
	gpnf_get_parent_entry_and_sync_child_entries_payment_details( $entry_id );
}, 10, 3 );

add_action( 'gform_update_payment_status', 'gpnf_get_parent_entry_and_sync_child_entries_payment_details' );
add_action( 'gform_update_payment_date', 'gpnf_get_parent_entry_and_sync_child_entries_payment_details' );
add_action( 'gform_update_transaction_id', 'gpnf_get_parent_entry_and_sync_child_entries_payment_details' );

/**
 * Bulk Sync Parent/Child Entry Payment Details
 * https://docs.gravityforms.com/gform_entry_list_bulk_actions/
 * https://docs.gravityforms.com/gform_entry_list_action/
 */

add_filter( 'gform_entry_list_bulk_actions', function( $actions, $form_id ) {
	$form = GFAPI::get_form( $form_id );
	if ( is_callable( 'gp_nested_forms' )
		&& gp_nested_forms()->has_nested_form_field( $form )
		&& gpnf_has_product_field( $form )
	) {
		$actions['gpnf_sync_child_entries'] = 'Sync Child Entry Payment Details';
	}
	return $actions;
}, 10, 2 );

add_action( 'gform_entry_list_action', function( $action, $entries, $form_id ) {
	if ( $action === 'gpnf_sync_child_entries' ) {
		foreach ( $entries as $entry_id ) {
			$entry = GFAPI::get_entry( $entry_id );
			gpnf_sync_child_entries_payment_details( $entry );
		}
	}
}, 10, 3 );

if ( ! function_exists( 'gpnf_sync_child_entries_payment_details' ) ) {
	function gpnf_sync_child_entries_payment_details( $parent_entry ) {

		if ( ! $parent_entry['payment_status'] ) {
			return;
		}

		$parent_entry  = new GPNF_Entry( $parent_entry );
		$child_entries = $parent_entry->get_child_entries();

		// "payment_amount" is excluded as the parents total is not relevant on the entry level.
		$sync_props = array( 'payment_status', 'payment_date', 'transaction_id' );

		foreach ( $child_entries as $child_entry ) {
			foreach ( $sync_props as $sync_prop ) {
				if ( $parent_entry->$sync_prop !== $child_entry[ $sync_prop ] ) {
					GFAPI::update_entry_property( $child_entry['id'], $sync_prop, $parent_entry->$sync_prop );
				}
			}

			/*
			 * Run on every sync rather than only when the payment status changed so re-syncing (e.g. via the bulk
			 * action) also repairs bookings whose status was missed.
			 */
			gpnf_sync_child_entry_booking_statuses( $child_entry, $parent_entry->payment_status );
		}

	}
}

if ( ! function_exists( 'gpnf_sync_child_entry_booking_statuses' ) ) {
	/**
	 * Apply the parent entry's payment status to any GP Bookings bookings owned by a child entry.
	 *
	 * GP Bookings stores bookings against the entry containing the Booking field — the child entry — but its only
	 * payment listener (gform_post_payment_action) receives the parent entry and looks up bookings by that entry's ID,
	 * so it finds none. This mirrors GP Bookings' own BookingStatuses::handle_payment_action() against the child entry
	 * so the plugin's payment status settings remain the source of truth.
	 *
	 * @param array  $child_entry    The child entry that owns the bookings.
	 * @param string $payment_status The parent entry's payment status.
	 */
	function gpnf_sync_child_entry_booking_statuses( $child_entry, $payment_status ) {

		// Inert when GP Bookings is not active.
		if ( ! class_exists( '\GP_Bookings\Queries\Booking_Query' ) ) {
			return;
		}

		$child_entry['payment_status'] = $payment_status;

		if ( \GP_Bookings\Settings::should_confirm_booking( $child_entry ) ) {
			$new_status = 'confirmed';
		} elseif ( \GP_Bookings\Settings::should_cancel_booking( $child_entry ) ) {
			$new_status = 'cancelled';
		} else {
			return;
		}

		$bookings = \GP_Bookings\Queries\Booking_Query::get_entry_bookings( (int) $child_entry['id'] );
		$note     = esc_html__( 'Booking status updated to match the parent entry payment status.', 'gp-bookings' );

		foreach ( $bookings as $booking ) {
			try {
				// update_status() returns early when the status is unchanged, so this is safe to call repeatedly.
				$booking->update_status( $new_status, $note );
			} catch ( Exception $e ) {
				GFCommon::log_debug( sprintf(
					'%s(): Unable to set booking #%d on entry #%d to %s: %s',
					__FUNCTION__,
					$booking->get_id(),
					$child_entry['id'],
					$new_status,
					$e->getMessage()
				) );
			}
		}

	}
}

if ( ! function_exists( 'gpnf_has_product_field' ) ) {
	function gpnf_has_product_field( $form ) {

		foreach ( $form['fields'] as $field ) {
			if ( GFCommon::is_product_field( $field['type'] ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'gpnf_get_parent_entry_and_sync_child_entries_payment_details' ) ) {
	function gpnf_get_parent_entry_and_sync_child_entries_payment_details( $entry_id ) {
		$entry = GFAPI::get_entry( $entry_id );
		gpnf_sync_child_entries_payment_details( $entry );
	}
}
