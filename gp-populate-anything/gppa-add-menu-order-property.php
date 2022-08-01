<?php
/**
 * Gravity Perks // GP Populate Anything // Add Menu Order Property for Post Object
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_object_type_properties_post', function( $props ) {
	global $wpdb;
	$props['menu_order'] = array(
		'label'    => esc_html__( 'Menu Order', 'gp-populate-anything' ),
		'value'    => 'menu_order',
		'callable' => array( gp_populate_anything()->get_object_type( 'post' ), 'get_col_rows' ),
		'args'     => array( $wpdb->posts, 'menu_order' ),
		'orderby'  => true,
	);
	return $props;
} );
