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


function modify_child_form_button_messsage( $args ) {

	add_filter('gpnf_add_button_max_message_' . $args['form_id'] . '_' . $args['child_form_field_id'], function( $message, $args ) {

		// due to backwards compatibility, this hooks has the potential to be called without $args
		if ( ! $args ) {
			return $message;
		}

		$min = $args['form_field']->gpnfEntryLimitMin;
		$max = $args['form_field']->gpnfEntryLimitMax;

		if ( ! $min || $min === 0 || $min === '0' ) {
			return 'You can add no more than ' . $args['form_field']->gpnfEntryLimitMax . ' entries.';
		}

		if ( ! $max ) {
			return 'You must add at least ' . $args['form_field']->gpnfEntryLimitMin . ' entries.';
		}

		return 'You must add at least ' . $args['form_field']->gpnfEntryLimitMin . ' entries and no more than ' . $args['form_field']->gpnfEntryLimitMax . ' entries.';

	}, 10, 4 );

}


# Example
modify_child_form_button_messsage( array(
	'form_id'             => 8,
	'child_form_field_id' => 4,
) );
