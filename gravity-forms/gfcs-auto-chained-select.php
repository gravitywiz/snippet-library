<?php
/**
 * Gravity Wiz // Gravity Forms Chained Selects // Auto Select Only Option
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 *
 * The Gravity Forms Chained Selects field requires you to manually select a value in each Drop Down
 * even if there is only a single option available in that Drop Down. This snippet will automatically
 * selected an option when it is the only option available.
 */
add_filter( 'gform_chained_selects_input_choices', function( $choices ) {
	$choices = gfcs_auto_select_only_choice( $choices );
	return $choices;
} );

function gfcs_auto_select_only_choice( $choices ) {

	$choices[0]['isSelected'] = $choices[0]['isSelected'] || count( $choices ) <= 1;

	if ( ! empty( $choices['choices'] ) ) {
		$choices['choices'] = gfcs_auto_select_only_choice( $choices['choices'] );
	}

	return $choices;
}
