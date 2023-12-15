<?php
/**
 * Gravity Perks // Entry Blocks // Customize Edit Entry Form's Submit Button Text
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 */
add_filter( 'gpeb_edit_form', function( $form ) {
	$form['button']['text'] = 'Update Entry';
	return $form;
} );
