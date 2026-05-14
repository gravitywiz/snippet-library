<?php
/**
 * Gravity Perks // GP Populate Anything // Modify Max Property Values to Load More Post Names/Slugs
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * This snippet increases the maximum number of property values displayed in the Form Editor for Populate Anything,
 * and also increases the number of post_name/post_slug values loaded from the database.
 *
 * Plugin Name:  GP Populate Anything — Modify Max Property Values to Load More Post Names/Slugs
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Modify the maximum number of property values to load more post names/slugs.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */

// Set the max number of property values to display in the editor and for post_name/post_slug queries.
$gppa_post_name_limit = 2500; // TODO: Update as per requirement.

add_filter( 'gppa_max_property_values_in_editor', function( $max_property_values ) use ( &$gppa_post_name_limit ) {
	return $gppa_post_name_limit;
} );

add_filter( 'gppa_object_type_col_rows_query', function( $sql, $col, $table_name, $object_type ) use ( &$gppa_post_name_limit ) {
	if ( $col !== 'post_name' && $col !== 'post_slug' ) {
		return $sql;
	}

	global $wpdb;

	if ( $table_name !== $wpdb->posts ) {
		return $sql;
	}

	if ( stripos( $sql, 'ORDER BY' ) === false ) {
		$sql = preg_replace(
			'/\s+LIMIT\s+\d+/i',
			' ORDER BY ID DESC LIMIT ' . absint( $gppa_post_name_limit ),
			$sql,
			1
		);
	}

	return $sql;

}, 10, 4 );
