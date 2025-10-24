<?php
/**
 * Gravity Perks // Limit Submissions // Skip Limit If Any Field Value Is Blank
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 *
 * Skip evaluation of Limit Submissions feed whenever one of its field-based rules
 * resolves to a blank value for the current submission.
 */
add_filter( 'gpls_should_apply_rules', function( $should_apply, $form_id, $rule_test ) {

	if ( ! $should_apply || empty( $rule_test->rules ) || empty( $rule_test->rule_group ) ) {
		return $should_apply;
	}

	// Replace '123' with your Form ID
	$target_form_ids = array( 123 );

	if ( ! in_array( (int) $form_id, $target_form_ids, true ) ) {
		return $should_apply;
	}

	$feed_id = method_exists( $rule_test->rule_group, 'get_feed_id' ) ? $rule_test->rule_group->get_feed_id() : null;

	if ( $feed_id && ! empty( $rule_test->skip_limit_feed_on_blank ) ) {
		return false;
	}

	foreach ( $rule_test->rules as $rule ) {
		if ( ! $rule instanceof GPLS_Rule_Field ) {
			continue;
		}

		$value = $rule->get_limit_field_value( $rule->get_field() );

		if ( false === $value ) {
			continue;
		}

		if ( is_array( $value ) ) {
			$value = GFCommon::trim_deep( $value );

			if ( GFCommon::is_empty_array( $value ) ) {
				$rule_test->skip_limit_feed_on_blank = true;
				return false;
			}

			continue;
		}

		if ( is_string( $value ) ) {
			$value = trim( $value );
		}

		if ( rgblank( $value ) ) {
			$rule_test->skip_limit_feed_on_blank = true;
			return false;
		}
	}

	return $should_apply;
}, 20, 3 );
