<?php
/**
 * Gravity Perks // Nested Forms // Override Max Entries Message
 * https://gravitywiz.com/documentation/gravity-forms-nested-form/
 *
 * This snippet allows overrides the default button message in the Nested Form Perk
 * and shows the user the minimum and maximum number of entries that can be added.
 *
 * @version 0.1
 */

// Update "123" to your form ID and "4" to your field ID. Remove "_123_4" to apply this globally.
add_filter( 'gpnf_template_args_123_4', function ( $args, $form_field ) {

	// $args->add_button_message is not always present when this hook is applied
	if ( ! array_key_exists( 'add_button_message', $args ) ) {
		return $args;
	}

	$min = $form_field->gpnfEntryLimitMin;
	$max = $form_field->gpnfEntryLimitMax;

	$message = null;

	if ( ( empty( $min ) || $min === '0' ) && ! empty( $max ) ) {
		$message = 'You can add no more than ' . $max . ' entries.';
	} elseif ( ( empty( $max ) || $max === '0' ) && ! empty( $min ) ) {
		$message = 'You must add at least ' . $min . ' entries.';
	} elseif ( ! empty( $min ) && ! empty( $max ) ) {
		$message = 'You must add at least ' . $min . ' entries and no more than ' . $max . ' entries.';
	}

	if ( ! is_null( $message ) ) {
		$args['add_button_message'] = sprintf(
			'
		 	<p class="gpnf-add-entry-max">
		 		%s
		 	</p>',
			$message
		);
	}

	return $args;
}, 10, 2 );
