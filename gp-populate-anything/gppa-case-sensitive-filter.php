<?php
/**
 * Gravity Perks // Populate Anything // Case-sensitive Filter for Database Object Type
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_object_type_database_query', function( $query, $args, $gppa_object_type_database ) {
	// Replace "order_name" with the column name in which you would like to make the search case-sensitive.
	$search  = "`{$args['primary_property_value']}`.`order_name` = ";
	$replace = "`{$args['primary_property_value']}`.`order_name` = BINARY ";
	return str_replace( $search, $replace, $query);
}, 10, 3 );
