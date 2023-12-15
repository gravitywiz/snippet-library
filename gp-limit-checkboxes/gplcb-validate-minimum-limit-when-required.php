<?php
/**
 * Gravity Perks // Limit Checkboxes // Validate Minimum Limit Only When Field is Required
 * https://gravitywiz.com/documentation/gravity-forms-limit-checkboxes/
 *
 * Only validate minimum limit when field is required or
 * when at least one checkbox has been checked.
 */
add_filter( 'gplcb_should_validate_minimum', function( $should_validate, $form, $field ) {
	return $field->isRequired || gp_limit_checkboxes()->get_checkbox_count( $field->id, $form );
}, 10, 3 );
