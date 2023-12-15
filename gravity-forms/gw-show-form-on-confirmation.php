<?php
/**
 * Gravity Wiz // Gravity Forms // Show Form on Confirmation
 *
 * Plugin Name:  Gravity Forms Show Form on Confirmation
 * Plugin URI:   https://gravitywiz.com.com/
 * Description:  Include the form and the confirmation message after a successful submission.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   http://gravitywiz.com
 *
 * Instructions
 *
 * This functionality can be activated by adding "show_form_on_confirmation" to the field values used to render the form.
 *
 * - If you're using the Form Block, this is done via the Field Values setting ([screenshot](https://gwiz.io/2LQjvgd)).
 * - If you're using the [gravityforms] shortcode, this is done by setting the value in the field_values parameter like so:
 *     [gravityforms id="123" field_values="show_form_on_confirmation"]
 *
 * Known Limitations
 *
 * - Only works with confirmations configured to display a message (not redirect to another page).
 * - Does not work with AJAX-enabled forms.
 */
add_filter( 'gform_form_args', function( $args ) {
	if ( isset( $args['field_values']['show_form_on_confirmation'] ) && GFFormDisplay::$submission && isset( GFFormDisplay::$submission[ $args['form_id'] ] ) && rgars( GFFormDisplay::$submission, "{$args['form_id']}/confirmation_message" ) ) {

		$stash                     = GFFormDisplay::$submission;
		GFFormDisplay::$submission = array();

		$form_markup = gravity_form( $args['form_id'], true, true, false, null, false, 0, false );

		GFFormDisplay::$submission = $stash;
		GFFormDisplay::$submission[ $args['form_id'] ]['confirmation_message'] .= $form_markup;

	}
	return $args;
} );
