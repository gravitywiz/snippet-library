<?php
/**
 * Gravity Perks // Nested Forms // Add Custom Label for "Add Entry" Button Depending on Child Entry Count
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Use this snippet to set a custom "Add Entry" button label when there are no child entries. In this example, the button
 * will read "Add First Entry" if there are no child entries and "Add Another Entry" if there is at least one child entry.
 */
// Update "123" to your parent form ID.
add_filter( 'gpnf_template_args_123', function( $args ) {
	if ( isset( $args['add_button'] ) ) {
		$search             = 'data-bind="';
		$replace            = $search . sprintf( 'text: ! entries().length ? `Add First %1$s` : `Add Another %1$s`, ', $args['field']->get_item_label() );
		$args['add_button'] = str_replace( $search, $replace, $args['add_button'] );
	}
	return $args;
} );
