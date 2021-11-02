<?php
/**
 * Gravity Perks // Inventory // Shortcode: Inventory // Show Available Inventory by Field
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Update "123" to your form ID and "4" to your field ID with inventory.
 * [gravityforms action="inventory" id="123" field="4"]
 *
 * @todo
 * - Add support for excluding field parameter and showing a consolidated list of all inventories.
 */
add_filter( 'gform_shortcode_inventory', function ( $output, $atts, $content ) {

	$atts = shortcode_atts( array(
		'id'    => false,
		'field' => false,
	), $atts );

	$form  = GFAPI::get_form( $atts['id'] );
	$field = GFAPI::get_field( $form, $atts['field'] );
	if ( ! $field ) {
		return $content;
	}

	$items = array();

	if ( is_array( $field->choices ) ) {
		$field->gpiShowAvailableInventory = true;
		if ( $content ) {
			$counts = gp_inventory_type_choices()->get_choice_counts( $form['id'], $field );
			foreach ( $field->choices as $choice ) {

				$limit     = (int) $choice['inventory_limit'];
				$count     = (int) rgar( $counts, $choice['value'] );
				$available = (int) $limit - $count;

				$items[] = gpis_get_item_markup( $content, array(
					'limit'     => $limit,
					'count'     => $count,
					'available' => $available,
					'label'     => $choice['text'],
					'value'     => $choice['value'],
				) );

			}
		} else {
			$choices = gp_inventory_type_choices()->apply_choice_limits( $field->choices, $field, $form );
			foreach ( $choices as $choice ) {
				$items[] = $choice['text'];
			}
		}
		$output = sprintf(
			'<ul class="gpi-inventory-list gpi-inventory-list-%d-%d"><li>%s</li></ul>',
			$form['id'], $field->id, implode( '</li><li>', $items )
		);
	} else {
		$available = gp_inventory_type_simple()->get_available_stock( $field );
		$label     = $field->get_field_label( false, '' );
		if ( $content ) {
			$limit   = gp_inventory_type_simple()->get_stock_quantity( $field );
			$count   = gp_inventory_type_simple()->get_claimed_inventory( $field );
			$output .= gpis_get_item_markup( $content, array(
				'limit'     => $limit,
				'count'     => $count,
				'available' => $available,
				'label'     => $label,
				'value'     => '',
			) );
		} else {
			$output .= $label . ': ' . $available;
		}
	}

	return $output;
}, 10, 3 );

function gpis_get_item_markup( $template, $args ) {

	$markup = $template;

	$markup = str_replace( '{limit}', $args['limit'], $markup );
	$markup = str_replace( '{count}', $args['count'], $markup );
	$markup = str_replace( '{available}', $args['available'], $markup );
	$markup = str_replace( '{label}', $args['label'], $markup );
	$markup = str_replace( '{value}', $args['value'], $markup );

	return $markup;
}
