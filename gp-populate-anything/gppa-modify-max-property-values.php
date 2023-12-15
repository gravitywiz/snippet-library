<?php
/**
 * Gravity Perks // GP Populate Anything // Modify Max Properties Displayed in Editor
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Modify the max number of property values that can be displayed in a property value select in the Form Editor for Populate Anything.
 */
add_filter( 'gppa_max_property_values_in_editor', function( $max_property_values ) {
	// Update "2500" to the max number of property values that can be displayed in the editor.
	return 2500;
} );
