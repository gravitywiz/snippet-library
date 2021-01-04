<?php
/**
 * Gravity Perks // Populate Anything // Include Static Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * 
 * Plugin Name:  GPPA Include Static Choices
 * Plugin URI:   https://gravitywiz.com.com/documentation/gravity-forms-populate-anything/
 * Description:  Include static choices alongside your dynamic choices populated by Populate Anything.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gppa_input_choices', function( $choices, $field, $objects ) {

    if ( strpos( $field->cssClass, 'gppa-prepend-static-choices' ) === false ) {
        return $choices;
    }

    $no_choices_string = apply_filters( 'gppa_no_choices_text', '&ndash; ' . esc_html__( 'No Results', 'gp-populate-anything' ) . ' &ndash;', $field );
    if ( $choices[0]['text'] === $no_choices_string ) {
        $choices = array();
    }

	if ( strpos( $field->cssClass, 'gppa-has-other-choice' ) !== false ) {
		$other_choice = array_pop( $field->choices );
    }

    $choices = array_merge( $field->choices, $choices );

	if ( isset( $other_choice ) ) {
		array_push( $choices, $other_choice );
    }

    return $choices;
}, 10, 3 );