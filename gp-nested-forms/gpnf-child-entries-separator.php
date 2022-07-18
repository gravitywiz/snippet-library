<?php
/**
 * Gravity Perks // Nested Forms // Set Custom Separator
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Use “—” to separate child entries rather than the default <hr>.
 */
add_filter( 'gpnf_child_entries_separator', function() {
	return '---';
} );
