<?php
/**
 * Gravity Perks // GP Populate Anything // Add Term Order Property for Term Object
 *
 * This snippet requires a plugin such as https://wordpress.org/plugins/taxonomy-terms-order/ which will add
 * a new "term_order" column to the wp_terms table.
 *
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Installation: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gppa_object_type_properties_term', function( $props ) {
	global $wpdb;
	$props['term_order'] = array(
		'label'    => esc_html__( 'Term Order', 'gp-populate-anything' ),
		'value'    => 'term_order',
		'callable' => array( gp_populate_anything()->get_object_type( 'term' ), 'get_col_rows' ),
		'args'     => array( $wpdb->terms, 'term_order' ),
		'orderby'  => true,
	);
	return $props;
} );
