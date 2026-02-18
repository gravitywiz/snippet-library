<?php
/**
 * Gravity Perks // Bookings // Sync Bookings on Entry Trash/Restore
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Experimental Snippet 🧪
 *
 * Deletes bookings when an entry is trashed and recreates them when restored.
 * If a time slot has since been booked by another entry, recreation will be
 * skipped, an entry note is added, and the entry is flagged for automatic
 * retry whenever a conflicting booking is later freed up.
 */
class GPB_Trash_Sync {

	const FLAG_KEY = '_gpb_bookings_deleted_on_trash';

	private static $instance = null;

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'gform_update_status', array( $this, 'handle_status_change' ), 10, 3 );
	}

	public function handle_status_change( $entry_id, $new_status, $old_status ) {
		if ( ! function_exists( 'gpb_delete_entry_bookings' ) || ! class_exists( '\GP_Bookings\Booking' ) ) {
			return;
		}

		$entry_id = (int) $entry_id;
		if ( $entry_id <= 0 ) {
			return;
		}

		if ( $new_status === 'trash' && $old_status !== 'trash' ) {
			$this->handle_trash( $entry_id );
		} elseif ( $old_status === 'trash' && $new_status !== 'trash' ) {
			$this->handle_restore( $entry_id );
		}
	}

	private function handle_trash( $entry_id ) {
		$deleted = gpb_delete_entry_bookings( $entry_id, 'Entry moved to trash' );

		if ( $deleted <= 0 ) {
			gform_delete_meta( $entry_id, self::FLAG_KEY );
			return;
		}

		gform_update_meta( $entry_id, self::FLAG_KEY, 1 );

		$entry = GFAPI::get_entry( $entry_id );
		if ( ! is_wp_error( $entry ) && ! empty( $entry['form_id'] ) ) {
			$this->retry_orphaned_bookings( (int) $entry['form_id'], $entry_id );
		}
	}

	private function handle_restore( $entry_id ) {
		if ( (int) gform_get_meta( $entry_id, self::FLAG_KEY ) !== 1 ) {
			return;
		}

		if ( ! empty( gpb_get_entry_bookings( $entry_id ) ) ) {
			gform_delete_meta( $entry_id, self::FLAG_KEY );
			return;
		}

		$entry = GFAPI::get_entry( $entry_id );
		if ( is_wp_error( $entry ) || empty( $entry ) ) {
			return;
		}

		try {
			\GP_Bookings\Booking::create_from_entry( $entry );
			gform_delete_meta( $entry_id, self::FLAG_KEY );
		} catch ( \Throwable $e ) {
			$note = sprintf(
				'Booking could not be recreated on restore — the time slot is occupied by another entry. This entry will be automatically rebooked if the conflicting booking is freed. (Error: %s)',
				$e->getMessage()
			);
			GFFormsModel::add_note( $entry_id, 0, 'GP Bookings', $note, 'gpb_restore_conflict' );
			$this->log( sprintf( 'Restore recreation failed for entry %d: %s', $entry_id, $e->getMessage() ) );
		}
	}

	private function retry_orphaned_bookings( $form_id, $skip_id = 0 ) {
		$sorting = array(
			'key'       => 'date_created',
			'direction' => 'ASC',
		);

		$entries = GFAPI::get_entries( $form_id, array(
			'status'        => 'active',
			'field_filters' => array(
				array(
					'key'   => self::FLAG_KEY,
					'value' => '1',
				),
			),
		), $sorting );

		if ( is_wp_error( $entries ) || empty( $entries ) ) {
			return;
		}

		foreach ( $entries as $entry ) {
			$entry_id = (int) rgar( $entry, 'id' );

			if ( $entry_id === $skip_id ) {
				continue;
			}

			if ( ! empty( gpb_get_entry_bookings( $entry_id ) ) ) {
				gform_delete_meta( $entry_id, self::FLAG_KEY );
				continue;
			}

			try {
				\GP_Bookings\Booking::create_from_entry( $entry );
				gform_delete_meta( $entry_id, self::FLAG_KEY );
				GFFormsModel::add_note( $entry_id, 0, 'GP Bookings', 'Booking automatically recreated after conflicting booking was freed.', 'gpb_restore_success' );
				$this->log( sprintf( 'Orphaned booking recreated for entry %d.', $entry_id ) );
				break;
			} catch ( \Throwable $e ) {
				$this->log( sprintf( 'Orphan retry failed for entry %d: %s', $entry_id, $e->getMessage() ) );
			}
		}
	}

	private function log( $message ) {
		if ( function_exists( 'gp_bookings' ) ) {
			gp_bookings()->log_debug( $message );
		}
	}

}

GPB_Trash_Sync::get_instance();
