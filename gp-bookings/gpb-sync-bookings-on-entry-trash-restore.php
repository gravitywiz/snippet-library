<?php
/**
 * Gravity Perks // Bookings // Sync Bookings on Entry Trash/Restore
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Experimental Snippet 🧪
 *
 * Deletes bookings when an entry is trashed and recreates them when restored.
 * If a time slot has since been booked by another entry, recreation will be
 * skipped and logged to avoid conflicts.
 */
add_action( 'gform_update_status', function( $entry_id, $new_status, $old_status ) {

	if ( ! function_exists( 'gpb_delete_entry_bookings' ) || ! class_exists( '\GP_Bookings\Booking' ) ) {
		return;
	}

	$entry_id = (int) $entry_id;
	if ( $entry_id <= 0 ) {
		return;
	}

	$flag_key = '_gpb_bookings_deleted_on_trash';

	if ( $new_status === 'trash' && $old_status !== 'trash' ) {
		$deleted = gpb_delete_entry_bookings( $entry_id, 'Entry moved to trash' );
		if ( $deleted > 0 ) {
			gform_update_meta( $entry_id, $flag_key, 1 );
		} else {
			gform_delete_meta( $entry_id, $flag_key );
		}
		return;
	}

	if ( $old_status === 'trash' && $new_status !== 'trash' ) {
		$was_deleted = (int) gform_get_meta( $entry_id, $flag_key ) === 1;
		if ( ! $was_deleted ) {
			return;
		}

		if ( ! empty( gpb_get_entry_bookings( $entry_id ) ) ) {
			gform_delete_meta( $entry_id, $flag_key );
			return;
		}

		$entry = GFAPI::get_entry( $entry_id );
		if ( is_wp_error( $entry ) || empty( $entry ) ) {
			return;
		}

		try {
			\GP_Bookings\Booking::create_from_entry( $entry );
			gform_delete_meta( $entry_id, $flag_key );
		} catch ( \Throwable $e ) {
			if ( function_exists( 'gp_bookings' ) ) {
				gp_bookings()->log_debug( sprintf( 'Restore recreation failed for entry %d: %s', $entry_id, $e->getMessage() ) );
			}
		}
	}

}, 10, 3 );
