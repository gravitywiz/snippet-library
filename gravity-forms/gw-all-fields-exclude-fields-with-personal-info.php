<?php
/**
 * Gravity Wiz // Gravity Forms // Exclude Fields With Personal Information
 *
 * This snippet shows you how to exclude fields with personal information from instances of the {all_fields} merge tag 
 * that include the custom modifier: {all_fields:exclude[persInfoFields]}.
 */
 add_filter( 'gwaft_modifier_value_persInfoFields', function() {
	return array( 1, 2, 3 );
} );
