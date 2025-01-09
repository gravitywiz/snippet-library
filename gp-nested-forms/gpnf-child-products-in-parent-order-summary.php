<?php
/**
 * Gravity Perks // Nested Forms // Include Child Products Directly in Parent Form Order Summary
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/857f5bbc938041de85fc8e8cc1f2abc3
 *
 * 1. Add a Calculated Product to your parent form.
 * 2. Add your Nested Form field with the :total modifier.
 * 3. Optional: If you want to merge duplicates, assign 'true' to the $merge_duplicates variable.
 * 4. Copy and paste this snippet into your theme's functions.php file.
 *
 * Now the Calculated Product field on your parent form will be replaced with the products from each child entry.
 */
$merge_duplicates = false;
add_filter( 'gform_product_info', function( $product_info, $form, $entry ) use ( $merge_duplicates ) {

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

					$_child_products[ "{$nested_form_field_id}.{$child_entry['id']}_{$child_field_id}" ] = $child_product;
				}

				if ( $merge_duplicates ) {
					// Loop through $_child_products and compare with $child_products.
					foreach ( $_child_products as $key => $_child_product ) {
						$match_found = false;

						foreach ( $child_products as &$child_product ) {
							// Check if the name and price match
							if ( $child_product['name'] == $_child_product['name'] && $child_product['price'] == $_child_product['price'] ) {
								$child_product['quantity'] += $_child_product['quantity'];

								$match_found = true;
								unset($_child_products[$key]);
								break;
							}
						}
					}
				}

				// If there are remaining products in $_child_products (after merging) or if we are not merging, add them to $child_products.
				if ( ! empty( $_child_products ) || ! $merge_duplicates ) {
					$child_products = array_merge( $child_products, $_child_products );
				}
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
