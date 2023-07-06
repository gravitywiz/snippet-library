<?php
/**
 * Gravity Perks // Limit Dates // Even Dates Only
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 */
// Update "123" to your form ID and "4" to your Date field ID.
add_filter( 'gpld_limit_dates_options_123_4', function( $field_options, $form, $field ) {
    if ( $field_options['minDate'] && $field_options['maxDate'] ) {

        $min_date = ( new DateTime() )->modify( $field_options['minDateMod'] );
        $max_date = ( new DateTime() )->modify( $field_options['maxDateMod'] );

        $even_days = array();

        while ( $min_date <= $max_date ) {
            if ( $min_date->format( 'd' ) % 2 !== 0 ) {
                $even_days[] = $min_date->format( 'm/d/Y' );
            }
            $min_date->modify( '+1 day' );
        }

        $field_options['exceptions'] = $even_days;

    }
    return $field_options;
}, 10, 3 );
