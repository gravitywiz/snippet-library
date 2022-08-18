<?php
/**
 * Gravity Perks // Nested Forms // Override Max Entries Message
 * https://gravitywiz.com/documentation/gravity-forms-nested-form/
 *
 * Instruction Video: https://www.loom.com/share/1ff5c50881134365b9bfd6b234a8c1c8
 *
 * This snippet allows overrides the default button message in the Nested Form Perk
 * and shows the user the minimum and maximum number of entries that can be added.
 *
 * Plugin Name:  GP Nested Forms - Show Minimum and Maximum Entries Message
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-form/
 * Description:  Override the message next to child field submit buttons and show the minimum/maximum number of child entries allowed.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
function override_child_form_max_entry_message( $field_configs = null ) {

	// apply modified message to all child form fields if $field_configs is not passed in
	if ( is_null( $field_configs ) ) {
		add_filter( 'gpnf_template_args', 'template_args_filter', 10, 3 );
		return;
	}

	foreach ( $field_configs as $config ) {
		add_filter( 'gpnf_template_args_' . $config['form_id'] . '_' . $config['child_form_field_id'], 'template_args_filter' , 10, 3 );
	}
}

function template_args_filter( $args, $form_field, $form ) {

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

# Example adding message to all child form fields
override_child_form_max_entry_message();

# Example adding message to a list of child form fields
// override_child_form_max_entry_message( array(
// 	array(
// 		'form_id'             => 8,
// 		'child_form_field_id' => 4,
// 	),
// ) );
