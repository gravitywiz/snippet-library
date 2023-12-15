<?php
/**
 * Gravity Perks // Nested Forms // Set Orphaned Entry Expiration Duration
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Modify how long entries submitted from a nested form should be saved before being moved to the trash.
 */
// Expire orphaned entries after 30 days (1 month).
add_filter( 'gpnf_expiration_modifier', function() {
	return MONTH_IN_SECONDS;
} );

// Never expire orphaned entries.
add_filter( 'gpnf_expiration_modifier', function() {
	return 100 * YEAR_IN_SECONDS;
} );
