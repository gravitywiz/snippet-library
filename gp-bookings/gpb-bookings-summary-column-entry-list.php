<?php
/**
 * Gravity Perks // Bookings // Bookings Summary Column in the Entry List
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Adds a single "Bookings" column to the Gravity Forms Entries page that lists the
 * date/time and assigned resource for every booking on the entry — including resources
 * assigned automatically via Automatic Assignment.
 *
 * Instructions:
 *   1. Install this snippet (see https://gravitywiz.com/documentation/how-do-i-install-a-snippet/).
 *   2. Go to Forms > Entries > (gear icon) Select Columns and add "Bookings Summary".
 *   3. Optionally set $form_ids below to limit which forms get the column.
 */
class GPB_Bookings_Summary_Column {

	public static $form_ids = array();

	public static $date_resource_separator = ' — ';

	public static $meta_key = 'gpb_bookings_summary';

	public static function init() {
		add_filter( 'gform_entry_meta', array( __CLASS__, 'register_column' ), 10, 2 );
		add_filter( 'gform_entries_field_value', array( __CLASS__, 'render_column' ), 10, 4 );
	}

	public static function is_applicable_form( $form_id ) {
		return empty( self::$form_ids ) || in_array( (int) $form_id, array_map( 'intval', self::$form_ids ), true );
	}

	public static function register_column( $entry_meta, $form_id ) {

		if ( ! self::is_applicable_form( $form_id ) ) {
			return $entry_meta;
		}

		$entry_meta[ self::$meta_key ] = array(
			'label'      => __( 'Bookings Summary', 'gp-bookings' ),
			'is_numeric' => false,
			'is_default' => false,
			'update_entry_meta_callback' => '__return_empty_string',
		);

		return $entry_meta;
	}

	public static function render_column( $value, $form_id, $field_id, $entry ) {

		if ( $field_id !== self::$meta_key || ! self::is_applicable_form( $form_id ) ) {
			return $value;
		}

		return self::get_summary( rgar( $entry, 'id' ) );
	}

	public static function get_summary( $entry_id ) {

		if ( ! $entry_id || ! function_exists( 'gpb_get_consolidated_entry_bookings' ) ) {
			return '';
		}

		$bookings = gpb_get_consolidated_entry_bookings( $entry_id );

		if ( empty( $bookings ) ) {
			return '';
		}

		$lines = array();

		foreach ( $bookings as $booking ) {

			$date = self::format_booking_date( $booking );
			$name = self::get_bookable_name( $booking );

			if ( $date && $name ) {
				$lines[] = $date . self::$date_resource_separator . $name;
			} else {
				$lines[] = $date ? $date : $name;
			}
		}

		$lines = array_filter( $lines );

		return implode( '<br>', array_map( 'esc_html', $lines ) );
	}

	public static function format_booking_date( $booking ) {

		$start = $booking->get_start_datetime();

		if ( ! $start ) {
			return '';
		}

		$is_date_only = in_array( $booking->get_mode(), array( 'nightly', 'date-range-end-inclusive' ), true );
		$format       = $is_date_only ? get_option( 'date_format' ) : get_option( 'date_format' ) . ' ' . get_option( 'time_format' );

		$timestamp = strtotime( is_string( $start ) ? $start : $start->format( 'Y-m-d H:i:s' ) );

		if ( ! $timestamp ) {
			return '';
		}

		$formatted = date_i18n( $format, $timestamp );

		// For multi-day bookings, append the end date.
		$end = $booking->get_end_datetime();
		if ( $is_date_only && $end ) {
			$end_timestamp = strtotime( is_string( $end ) ? $end : $end->format( 'Y-m-d H:i:s' ) );
			if ( $end_timestamp && date( 'Y-m-d', $end_timestamp ) !== date( 'Y-m-d', $timestamp ) ) {
				$formatted .= ' – ' . date_i18n( $format, $end_timestamp );
			}
		}

		return $formatted;
	}

	public static function get_bookable_name( $booking ) {

		$bookable = $booking->get_bookable();

		if ( ! $bookable || ! method_exists( $bookable, 'get_name' ) ) {
			return '';
		}

		return (string) $bookable->get_name();
	}

}

add_action( 'init', array( 'GPB_Bookings_Summary_Column', 'init' ) );
