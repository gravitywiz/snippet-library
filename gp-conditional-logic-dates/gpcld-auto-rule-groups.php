<?php
/**
 * Gravity Perks // Conditional Logic Dates // Auto Rule Groups
 * https://gravitywiz.com/documentation/gravity-forms-conditional-logic-dates/#relative-dates
 *
 * This snippet introduces the concept of automatic rule groups to Conditional Logic Dates. Auto rule groups are intelligently
 * modified time-based rules, adjusted based on the date-and-time-based rules that precede them.
 *
 * For example, if a rule is configured where the Current Time is greater than 5:00pm and second rule where the Current
 * Time is less than 8:00am, it is logical that the second rule is intended to check for 8:00am tomorrow. This snippet
 * set a base date modifier to increment the date used to calculate the time value for the next day.
 *
 * In the future, we look forward to adding support for enhancing this snippet with support for applying the same logic
 * when a Current Time rule is preceded by a Date field rule.
 */
add_filter( 'gform_form_post_get_meta', function( $form ) {

	if ( ! is_callable( array( 'GFForms', 'get_page' ) ) || GFForms::get_page() ) {
		return $form;
	}

	foreach ( $form['fields'] as $field ) {

		if ( empty( $field->conditionalLogic ) || $field->conditionalLogic['logicType'] !== 'all' ) {
			continue;
		}

		$start_time_rule = null;

		foreach ( $field->conditionalLogic['rules'] as &$rule ) {

			if ( $start_time_rule ) {
				$start_time = strtotime( $start_time_rule['value'] );
				$end_time   = strtotime( $rule['value'] );
				if ( $rule['operator'] === '<' && $start_time > $end_time ) {
					$rule['gpcldBaseDate'] = 1;
				}
				$start_time_rule = null;
			} else if ( $rule['fieldId'] === '_gpcld_current_time' && $rule['operator'] === '>' ) {
				$start_time_rule = $rule;
			}

		}

	}

	return $form;
} );
