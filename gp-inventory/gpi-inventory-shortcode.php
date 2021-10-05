<?php
/**
 * Gravity Perks // Inventory // Shortcode: Inventory // Show Available Inventory by Field
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Update "123" to your form ID and "4" to your field ID with inventory.
 * [gravityforms action="inventory" id="123" field="4"]
 */
add_filter( 'gform_shortcode_inventory', function ( $output, $atts ) {

	$atts = shortcode_atts( array(
		'id'    => false,
		'field' => false,
	), $atts );

	$form  = GFAPI::get_form( $atts['id'] );
	$field = GFAPI::get_field( $form, $atts['field'] );

	$items = array();

	if ( is_array( $field->choices ) ) {
		$field->gpiShowAvailableInventory = true;
		$choices = gp_inventory_type_choices()->apply_choice_limits( $field->choices, $field, $form );
		foreach ( $choices as $choice ) {
			$items[] = $choice['text'];
		}
		$output = sprintf(
			'<ul class="gpi-inventory-list gpi-inventory-list-%d-%d"><li>%s</li></ul>',
			$form['id'], $field->id, implode( '</li><li>', $items )
		);
	} else {
		$output = 'This field\'s inventory type is not support yet.';
	}

	return $output;
}, 10, 2 );
