<?php
/**
 * Gravity Perks // Inventory // Auto-selections
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 */
global $gpias_form_id, $gpias_list_field_id, $gpias_product_field_id;

$gpias_form_id          = 123;
$gpias_list_field_id    = 4;
$gpias_product_field_id = 5;

add_filter( "gform_column_input_{$gpias_form_id}_{$gpias_list_field_id}_1", function( $input_info, $field, $column, $value, $form_id ) {
	global $gpias_product_field_id;

	$source_field = GFAPI::get_field( $form_id, $gpias_product_field_id );
	$choices      = $source_field->choices;

	foreach ( $choices as &$choice ) {
		if ( $source_field->enablePrice ) {
			$price            = rgempty( 'price', $choice ) ? 0 : GFCommon::to_number( rgar( $choice, 'price' ) );
			$choice['value'] .= '|' . $price;
		}
	}

	return array(
		'type'    => 'select',
		'choices' => $choices,
	);
}, 10, 5 );

add_filter( "gform_pre_process_{$gpias_form_id}", function( $form ) {
	global $gpias_list_field_id, $gpias_product_field_id;

	$list_field = GFAPI::get_field( $form, $gpias_list_field_id );
	$booths     = $list_field->get_value_submission( array() );

	foreach ( $form['fields'] as $field ) {

		if ( $field->id != $gpias_product_field_id ) {
			continue;
		}

		while ( ! empty( $booths ) ) {

			$booth                         = array_shift( $booths );
			$_POST[ "input_{$field->id}" ] = $booth;

			$_fields        = $form['fields'];
			$form['fields'] = array( $field );

			$result = gp_inventory_type_choices()->validation( array(
				'is_valid' => true,
				'form'     => $form,
			) );

			$form['fields'] = $_fields;

			if ( $result['is_valid'] ) {
				$field->failed_validation = false;
				return $form;
			}
		}

		if ( empty( $booths ) ) {
			$list_field->failed_validation  = true;
			$list_field->validation_message = 'None of your selected booths are available.';
		}
	}

	return $form;
} );
