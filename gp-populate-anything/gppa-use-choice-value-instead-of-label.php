<?php
/**
 * Gravity Perks // Populate Anything // Use Choice Value Instead of Label in Admin
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/ 
 *
 * By default, Populate Anything will display choice labels in the Entry List and Entry Detail views.
 * This snippet reverts to the default Gravity Forms behavior of showing values instead for Populate Anything enabled fields.
 */
add_action('admin_init', function() {
	remove_filter( 'gform_entry_field_value', array( gp_populate_anything(), 'entry_field_value' ), 20 );
	remove_filter( 'gform_entries_field_value', array( gp_populate_anything(), 'entries_field_value' ), 20 );
});
