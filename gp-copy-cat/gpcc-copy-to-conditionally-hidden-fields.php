<?php
/**
 * Gravity Perks // Copy Cat // Copy Values for Conditionally Hidden Fields
 * https://gravitywiz.com/documentation/gravity-forms-copy-cat/
 *
 * By default, Gravity Forms does not capture values for fields hidden by conditional logic. Use this snippet to "copy"
 * values to hidden fields on submission.
 */
add_action( 'gform_entry_post_save', function( $entry, $form ) {

	if ( ! is_callable( 'gp_copy_cat' ) ) {
		return $entry;
	}

	$orig_entry = $entry;
	$triggers   = gp_copy_cat()->get_copy_cat_fields( $form );

	foreach ( $triggers as $trigger_field_id => $targets ) {

		$is_trigger_hidden = GFFormsModel::is_field_hidden( $form, GFAPI::get_field( $form, $trigger_field_id ), array(), $entry );
		if ( $is_trigger_hidden ) {
			continue;
		}

		foreach ( $targets as $target ) {

			$is_target_hidden = GFFormsModel::is_field_hidden( $form, GFAPI::get_field( $form, $target['target'] ), array(), $entry );
			if ( ! $is_target_hidden ) {
				continue;
			}

			$source_field    = GFAPI::get_field( $form, $target['source'] );
			$source_values   = $source_field->get_value_submission( array() );
			$condition_field = GFAPI::get_field( $form, $target['condition'] );
			$condition_value = $condition_field->get_value_submission( $entry );
			
			// for multi-input fields, we need to check the index
			// to see if the condition is met.
			if ( strpos( $target['condition'], '.' ) !== false ) {
				list( $base, $index ) = explode( '.', $target['condition'] );
				if ( isset( $condition_field['choices'][ (int) $index ] ) ) {
					if ( $condition_field['choices'][ (int) $index ]['value'] !== $condition_value ) {
						continue;
					}
				} else {
					continue;
				}
			}

			if ( is_array( $source_values ) ) {
				foreach ( $source_values as $input_id => $source_value ) {
					$target_input_id           = str_replace( "{$source_field->id}.", "{$target['target']}.", $input_id );
					$entry[ $target_input_id ] = $source_value;
				}
			} else {
				$entry[ $target['target'] ] = $source_values;
			}
		}
	}

	if ( $orig_entry !== $entry ) {
		GFAPI::update_entry( $entry );
	}

	return $entry;
}, 9, 2 );
