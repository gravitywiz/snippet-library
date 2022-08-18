<?php
/**
 * Gravity Perks // Nested Forms // Add Child Entry on Trigger
 * https://gravitywiz.com/documentation/gravity-forms-nested-form/
 *
 * Instruction Video: https://www.loom.com/share/2d01000744354e7693ac4348f521992f
 *
 * This snippet allows overrides the default button message in the Nested Form Perk
 * and shows the user the minimum and maximum number of entries that can be added.
 *
 * Plugin Name:  GP Nested Forms - Show Minimum and Maximum Entries Message
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-form/
 * Description:  Override the message next to child field submit buttons and show the minimum/maximum number of child entries allowed.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   https://gravitywiz.com
 */
function modify_child_form_button_messsage( $field_configs ) {
	foreach ( $field_configs as $config ) {
		// $args = gf_apply_filters( array( 'gpnf_template_args', $this->formId, $this->id ), $args, $this, $form );
		add_filter( 'gpnf_template_args_' . $config['form_id'] . '_' . $config['child_form_field_id'], function( $args, $form_field, $form ) {

			// $args->add_button_message is not always present when this hook is applied
			if ( ! array_key_exists( 'add_button_message', $args ) ) {
				return $args;
			}

			$min = $form_field->gpnfEntryLimitMin;
			$max = $form_field->gpnfEntryLimitMax;

			if ( ( empty( $min ) || $min === '0' ) && ! empty( $max ) ) {
				$args['add_button_message'] = format_message( 'You can add no more than ' . $max . ' entries.' );
			} elseif ( ( empty( $max ) || $max === '0' ) && ! empty( $min ) ) {
				$args['add_button_message'] = format_message( 'You must add at least ' . $min . ' entries.' );
			} elseif ( ! empty( $min ) && ! empty( $max ) ) {
				$args['add_button_message'] = format_message( 'You must add at least ' . $min . ' entries and no more than ' . $max . ' entries.' );
			}

			return $args;

		}, 10, 3 );
	}
}

function format_message( $message ) {
	return sprintf(
		'
	 	<p class="gpnf-add-entry-max">
	 		%s
	 	</p>',
		$message
	);
}


modify_child_form_button_messsage( array(
	array(
		'form_id'             => 8,
		'child_form_field_id' => 4,
	),
) );
