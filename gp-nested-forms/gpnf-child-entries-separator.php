<?php
/**
 * Gravity Perks // Nested Forms // Set Custom Separator
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * By default, an <hr> is used to separate child entries rendered via the {all_fields} merge tag 
 * and the Nested Form field merge tag. Use this snippet to customize the separator.
 */
add_filter( 'gpnf_child_entries_separator', function() {
	// Update this value to any characters or HTML you would like to use as a separator.
	return '---';
} );
