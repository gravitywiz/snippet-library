<?php
/**
 * Gravity Perks // Populate Anything // Auto-update Form ID on Form Duplication
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * When populating Gravity Forms Entries via Populate Anything, it can save time to automatically update the selected
 * form ID to the new form ID when duplicating an existing form.
 *
 * To enable on any field, just set the "gppa-set-current-form" class on the CSS Class Name setting.
 *
 * Plugin Name:  GP Populate Anything - Auto-update Form ID on Form Duplication
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Auto-update form ID to the new form ID when duplicating an existing form.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_action( 'gform_post_form_duplicated', function( $form_id, $new_id ) {
	$form       = GFAPI::get_form( $new_id );
	$has_change = false;
	foreach ( $form['fields'] as &$field ) {
		if ( strpos( $field->cssClass, 'gppa-set-current-form' ) !== false ) {
			$has_change                              = true;
			$field->{'gppa-values-primary-property'} = $new_id;
		}
	}
	if ( $has_change ) {
		GFAPI::update_form( $form );
	}
}, 10, 2 );
