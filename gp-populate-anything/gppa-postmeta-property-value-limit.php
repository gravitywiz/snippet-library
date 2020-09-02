<?php
/**
 * Note: See https://gravitywiz.com/documentation/how-do-i-install-a-snippet/ for details on how to install snippets.
 */
add_filter( 'gppa_object_type_col_rows_query', function( $sql, $col, $table, $object_type ) {
	// Change this to the maximum number of post meta results
	$meta_limit = 1500;

	global $wpdb;
	if ( $wpdb->postmeta === $table ) {
		$sql = str_replace( '1000', $meta_limit, $sql );
	}
	return $sql;
}, 10, 4 );