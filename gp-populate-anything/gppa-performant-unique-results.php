<?php
/**
 * Gravity Perks // Populate Anything // Performant Unique Results (for Database Object Type)
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Populate Anything limits its queries to the first 500 results for performance. When using the "Only Show Unique Results"
 * setting, results are filtered down from those first 500 results meaning that if you're looking for distinct values in
 * a database that has more than 500 results, it's possible that not all unique results will be returned.
 *
 * The traditional solution for this is to increase the query limit to the maximum size of your database but this can
 * come at a significant performance cost.
 *
 * This snippet will check if the Order By, Value Template, and Label Template are all the same and if so, it will
 * update the query to only select the distinct values from the Order By column. This will ensure that all unique
 * results are returned without the performance cost of increasing the query limit.
 */
add_filter( 'gppa_object_type_database_query', function( $query, $args, $db_object_type ) {
	global $wpdb;

	$order_by       = rgars( $args, 'ordering/orderby' );
	$template_value = rgars( $args, 'templates/value' );
	$template_label = rgars( $args, 'templates/label' );
	$are_all_same   = count( array_unique( array( $order_by, $template_value, $template_label ) ) ) === 1;

	if ( $are_all_same ) {
		$query = str_replace( 'SELECT *', $wpdb->prepare( 'SELECT DISTINCT %i', $order_by ), $query );
	}

	return $query;
}, 10, 3 );
