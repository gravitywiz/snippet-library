<?php
/**
 * Gravity Perks // Populate Anything // Populate Date in Field's Format
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Experimental Snippet 🧪
 */
add_filter( 'gppa_process_template_value', function( $value, $field ) {
	if ( $field->get_input_type() === 'date' && $field->dateType === 'datepicker' ) {
		list( $format, $separator ) = array_pad( explode( '_', $field->dateFormat ), 2, 'slash' );

		$separators = array(
			'slash' => '/',
			'dash'  => '-',
			'dot'   => '.',
		);

		$format = str_replace( 'y', 'Y', $format );
		$format = implode( $separators[ $separator ], str_split( $format ) );

		// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
		$value = date( $format, strtotime( $value ) );
	}
	return $value;
}, 10, 2 );
