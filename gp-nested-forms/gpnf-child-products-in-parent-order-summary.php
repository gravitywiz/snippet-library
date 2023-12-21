<?php
/**
 * Gravity Perks // Nested Forms // Include Child Products Directly in Parent Form Order Summary
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/857f5bbc938041de85fc8e8cc1f2abc3
 *
 * 1. Add a Calculated Product to your parent form.
 * 2. Add your Nested Form field with the :total modifier.
 * 3. Copy and paste this snippet into your theme's functions.php file.
 *
 * Now the Calculated Product field on your parent form will be replaced with the products from each child entry.
 */
add_filter( 'gform_product_info', function( $product_info, $form, $entry ) {

	foreach ( $form['fields'] as $field ) {

		if ( ! is_a( $field, 'GF_Field_Calculation' ) ) {
			continue;
		}

		$child_products = array();

		preg_match_all( '/{[^{]*?:([0-9]+):(sum|total|count)=?([0-9]*)}/', $field->calculationFormula, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {

			list( ,$nested_form_field_id,, ) = $match;

			$nested_form_field = GFAPI::get_field( $form, $nested_form_field_id );
			if ( ! $nested_form_field ) {
				continue;
			}

			$child_form    = gp_nested_forms()->get_nested_form( $nested_form_field->gpnfForm );
			$_entry        = new GPNF_Entry( $entry );
			$child_entries = $_entry->get_child_entries( $nested_form_field_id );

			foreach ( $child_entries as $child_entry ) {
				$child_product_info = GFCommon::get_product_fields( $child_form, $child_entry );
				$_child_products    = array();
				foreach ( $child_product_info['products'] as $child_field_id => $child_product ) {
					$child_product['name'] = "{$product_info['products'][ $field->id ]['name']} — {$child_product['name']}";

					// If Nested Form fields have Live Merge Tags, process those.
					if ( method_exists( 'GP_Populate_Anything_Live_Merge_Tags', 'has_live_merge_tag' ) ) {
						$gppa_lmt = GP_Populate_Anything_Live_Merge_Tags::get_instance();
						if ( $gppa_lmt->has_live_merge_tag( $child_product['name'] ) ) {
							$gppa_lmt->populate_lmt_whitelist( $child_form );
							$child_product['name'] = $gppa_lmt->replace_live_merge_tags_static( $child_product['name'], $child_form, $child_entry );
						}
					}

					$_child_products[ "{$nested_form_field_id}.{$child_entry['id']}.{$child_field_id}" ] = $child_product;
				}
				$child_products = $child_products + $_child_products;
			}
		}

		if ( empty( $child_products ) ) {
			continue;
		}

		$product_keys = array_keys( $product_info['products'] );
		$products     = array_values( $product_info['products'] );

		// phpcs:ignore WordPress.PHP.StrictInArray.FoundNonStrictFalse
		$index = array_search( $field->id, $product_keys, false );

		array_splice( $product_keys, $index, 1, array_keys( $child_products ) );
		array_splice( $products, $index, 1, array_values( $child_products ) );

		$product_info['products'] = array_combine( $product_keys, $products );

	}

	return $product_info;
}, 10, 3 );
