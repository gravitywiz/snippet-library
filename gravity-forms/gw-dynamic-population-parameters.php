<?php
/**
 * Gravity Wiz // Gravity Forms // Use Merge Tags as Dynamic Population Parameters
 * https://gravitywiz.com/use-merge-tags-as-dynamic-population-parameters/
 *
 * This snippet enables the ability to use merge tags as dynamic population parameters, allowing you to specify default values for complex fields.
 */
add_filter( 'gform_pre_render', 'gw_prepopluate_merge_tags' );
function gw_prepopluate_merge_tags( $form ) {
	global $gw_filter_names;

	$gw_filter_names = array();

	foreach( $form['fields'] as &$field ) {

		if( ! rgar( $field, 'allowsPrepopulate' ) ) {
			continue;
		}

		// complex fields store inputName in the "name" property of the inputs array
		if( is_array( rgar( $field, 'inputs' ) ) && $field['type'] != 'checkbox' ) {
			foreach( $field->inputs as $input ) {
				if( $input['name'] ) {
					$gw_filter_names[ $input['name'] ] = GFCommon::replace_variables_prepopulate( $input['name'] );
				}
			}
		} else {
			$gw_filter_names[ $field->inputName ] = GFCommon::replace_variables_prepopulate( $field->inputName );
		}

	}

	foreach( $gw_filter_names as $filter_name => $filter_value ) {

		if( $filter_value && $filter_name != $filter_value ) {
			add_filter( "gform_field_value_{$filter_name}", function( $value, $field, $name ) {
				global $gw_filter_names;
				$value = $gw_filter_names[ $name ];
				/** @var GF_Field $field  */
				if( $field->get_input_type() == 'list' ) {
					remove_all_filters( "gform_field_value_{$name}" );
					$value = GFFormsModel::get_parameter_value( $name, array( $name => $value ), $field );
				}
				return $value;
			}, 10, 3 );
		}

	}

	return $form;
}
