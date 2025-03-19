<?php
/**
 * Gravity Perks // Populate Anything // ACF Repeater Mapper
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Populate all rows from an ACF Repeater into a choice-based field.
 *
 * Instructions
 *
 * 1. Enable "Populate choices dynamically" on any choice-based field.
 * 2. Select the "Post", "User" or "Term" object type.
 * 3. Apply any desired filters to determine which post(s) should have their repeater data populated.
 * 4. Use the "Choice Template" to match each choice property to a repeater subfield.
 *    For example, if your repeater is labeled "parts" and you want to populate the "name" subfield as the choice label,
 *    select "parts_0_name" for the "Label" template. [Screenshot](https://gwiz.io/3m4Yq0y).
 * 5. Add the "gppa-acf-repeater-mapper" to the field's CSS Class Name setting.
 *
 * Video
 *
 * https://www.loom.com/share/a375fd3a5e6a4931a55e50d51eaec951
 *
 * Plugin Name:  GP Populate Anything — ACF Repeater Mapper
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Populate all rows from an ACF Repeater into a choice-based field.
 * Author:       Gravity Wiz
 * Version:      0.4
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gppa_input_choices', function( $choices, $field, $objects ) {

	if ( strpos( $field->cssClass, 'gppa-acf-repeater-mapper' ) === false ) {
		return $choices;
	}

	$map        = array();
	$custom_map = array();

	foreach ( $field->{'gppa-choices-templates'} as $template => $key ) {
		/**
		 * Look for ACF repeater meta: repeater_name_0_subfield_name
		 *
		 * Known limitation: This cannot have a number on the repeater field name. For an enumerator, it is
		 * recommended to use alphabets like, repeater_name_a_0_name, repeater_name_b_1_name,etc.
		 */
		if ( preg_match( '/meta_([^0-9]+)_([0-9]+)_(.+)/', $key, $matches ) ) {
			list( , $repeater, $index, $subfield ) = $matches;
			$map[ $template ]                      = $subfield;
		} else {
			$custom_map[ $template ] = $key;
		}
	}

	if ( ! $repeater ) {
		return $choices;
	}

	$choices = array();

	foreach ( $objects as $object ) {
		$rows = get_field( $repeater, $object->ID );
		if ( get_class( $object ) == 'WP_User' ) {
			$rows = get_field( $repeater, 'user_' . $object->ID );
		}
		if ( get_class( $object ) == 'WP_Term' ) {
			$rows = get_field( $repeater, $object->taxonomy . '_' . $object->term_id );
		}

		if ( $rows ) {
			foreach ( $rows as $row ) {
				$label = isset( $map['label'] ) ?
						apply_filters( 'gppa_acfrm_label', rgar( $row, $map['label'] ), $row, $map['label'] ) :
						str_replace( 'gf_custom:', '', $custom_map['label'] );

				$value = isset( $map['value'] ) ?
						apply_filters( 'gppa_acfrm_value', rgar( $row, $map['value'] ), $row, $map['value'] ) :
						str_replace( 'gf_custom:', '', $custom_map['value'] );

				$choice = array(
					'value' => $value,
					'text'  => $label,
				);

				if ( isset( $map['inventory_limit'] ) ) {
					$choice['inventory_limit'] = apply_filters( 'gppa_acfrm_inventory_limit', rgar( $row, $map['inventory_limit'] ), $row, $map['inventory_limit'] );
				}

				$choice['price'] = isset( $map['price'] ) ?
								apply_filters( 'gppa_acfrm_price', rgar( $row, $map['price'] ), $row, $map['price'] ) :
								str_replace( 'gf_custom:', '', $custom_map['price'] );

				$choices[] = $choice;
			}
		}
	}

	return $choices;
}, 10, 3 );
