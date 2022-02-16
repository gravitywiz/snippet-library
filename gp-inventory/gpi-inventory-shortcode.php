<?php
/**
 * Gravity Perks // Inventory // Shortcode: Inventory // Show Available Inventory by Field
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Instruction Video: https://www.loom.com/share/9d1897075b7b435389479d4f17fc6807
 *
 * Update "123" to your form ID and "4" to your field ID with inventory.
 * [gravityforms action="inventory" id="123" field="4"]
 *
 * If using a field with scopes, you may provide the scope values in a comma-delimited list:
 * [gravityforms action="inventory" id="123" field="4" scope_values="Scope Value,Another Scope Value"]
 *
 * This snippet requires GP Inventory 1.0-beta-1.7 or newer.
 *
 * @todo
 * - Add support for excluding field parameter and showing a consolidated list of all inventories.
 */
add_filter( 'gform_shortcode_inventory', 'gpi_inventory_shortcode', 10, 3 );
function gpi_inventory_shortcode( $output, $atts, $content ) {

	$atts = shortcode_atts( array(
		'id'           => false,
		'field'        => false,
		'scope_values' => false,
	), $atts, 'gpi_inventory' );

	if ( empty( $atts['id'] ) || empty( $atts['field'] ) ) {
		return $content;
	}

	$form  = GFAPI::get_form( $atts['id'] );
	$field = GFAPI::get_field( $form, $atts['field'] );

	if ( ! $field ) {
		return $content;
	}

	/**
	 * Some scopes have infinite values (i.e. Date fields). Use the * scope value to look up submitted scope values and
	 * display the content template for each. Default template when using the * scope is:
	 *
	 * {scope}: {count}
	 *
	 * NOTE: This only works with inventories with a single scope. Will continue exploring this in the future.
	 */
	if ( $atts['scope_values'] && $atts['scope_values'] === '*' ) {
		global $wpdb;

		$resource_field_id = reset( $field->gpiResourcePropertyMap );
		$sql               = gf_apply_filters( array( 'gpis_scopes_sql', $form['id'], $resource_field_id ), $wpdb->prepare( "SELECT DISTINCT meta_value FROM {$wpdb->prefix}gf_entry_meta WHERE form_id = %d AND meta_key = %d", $form['id'], $resource_field_id ), $form, $field, $resource_field_id );
		$items             = $wpdb->get_results( $sql );

		if ( empty( $items ) ) {
			return $content;
		}

		$output          = array();
		$is_choice_field = ! empty( $field->choices );

		if ( ! $content ) {
			if ( $is_choice_field ) {
				$content = '{label}: {count}';
			} else {
				$content = '{scope}: {count}';
			}
		}

		foreach ( $items as $item ) {

			$atts['scope_values'] = $item->meta_value;
			$scope_display_value  = gf_apply_filters( array( 'gpis_scope_display_value', $form['id'], $resource_field_id ), $item->meta_value, $field, $resource_field_id, $atts );

			$item_output = '';
			if ( $is_choice_field ) {
				$item_output = $scope_display_value;
			}

			$item_output .= str_replace( '{scope}', $scope_display_value, gpi_inventory_shortcode( null, $atts, $content ) );
			$output[]     = $item_output;

		}

		$label = $field->get_field_label( false, '' );

		$output = sprintf(
			'<strong>%s</strong><ul class="gpi-inventory-list gpi-inventory-list-%d-%d"><li>%s</li></ul>',
			$label, $form['id'], $field->id, implode( '</li><li>', $output )
		);

		return $output;
	}

	/**
	 * Callback for mapping property values from the scope_values attribute to the gpi_property_map_values filter to
	 * get the current inventory for scoped inventory fields.
	 *
	 * @param $property_values
	 *
	 * @return mixed
	 */
	$map_property_values = function ( $property_values ) use ( $atts ) {
		$scope_values = explode( ',', $atts['scope_values'] );

		if ( empty( $scope_values ) || empty( array_filter( $scope_values ) ) ) {
			return $property_values;
		}

		/* Take the order of property value keys and map them according to the order of the values in scope_values
		   as the order of the properties should never change. */
		$property_value_keys = array_keys( $property_values );

		foreach ( $scope_values as $scope_value_index => $scope_value ) {
			if ( ! isset( $property_value_keys [ $scope_value_index ] ) ) {
				continue;
			}

			$property_id = $property_value_keys [ $scope_value_index ];

			$property_values [ $property_id ] = $scope_value;
		}

		return $property_values;
	};

	$items = array();

	add_filter( 'gpi_property_map_values_' . $form['id'] . '_' . $field->id, $map_property_values );

	if ( is_array( $field->choices ) ) {
		$field->gpiShowAvailableInventory = true;

		// Delete choice count cache before and after getting choice counts to prevent scopes from interfering.
		$cache_key = 'gpi_choice_counts_' . $form['id'] . '_' . $field->id;
		GFCache::delete( $cache_key );

		if ( $content ) {
			$counts = gp_inventory_type_choices()->get_choice_counts( $form['id'], $field );
			foreach ( $field->choices as $choice ) {

				$limit     = (int) $choice['inventory_limit'];
				$count     = (int) rgar( $counts, $choice['value'] );
				$available = $limit - $count;

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

		GFCache::delete( $cache_key );

		$output = sprintf(
			'<ul class="gpi-inventory-list gpi-inventory-list-%d-%d"><li>%s</li></ul>',
			$form['id'], $field->id, implode( '</li><li>', $items )
		);
	} else {
		if ( gp_inventory_type_advanced()->is_applicable_field( $field ) ) {
			$available = gp_inventory_type_advanced()->get_available_stock( $field );
			$limit     = gp_inventory_type_advanced()->get_stock_quantity( $field );
			$count     = gp_inventory_type_advanced()->get_claimed_inventory( $field );

			/**
			 * Temporarily remove filter for resources to get the inventory count specific to the field rather than the
			 * count of all fields using the same resource.
			 */
			remove_filter( 'gpi_query', array( gp_inventory_type_advanced(), 'resource_and_properties' ), 9 );
			$count_current_field = gp_inventory_type_simple()->get_claimed_inventory( $field );
			add_filter( 'gpi_query', array( gp_inventory_type_advanced(), 'resource_and_properties' ), 9, 2 );
		} else {
			$available           = gp_inventory_type_simple()->get_available_stock( $field );
			$limit               = gp_inventory_type_simple()->get_stock_quantity( $field );
			$count               = gp_inventory_type_simple()->get_claimed_inventory( $field );
			$count_current_field = $count;
		}

		$label = $field->get_field_label( false, '' );

		if ( $content ) {
			$output .= gpis_get_item_markup( $content, array(
				'limit'               => $limit,
				'count'               => $count,
				'count_current_field' => $count_current_field,
				'available'           => $available,
				'label'               => $label,
				'value'               => '',
			) );
		} else {
			$output .= $label . ': ' . $available;
		}
	}

	remove_filter( 'gpi_property_map_values_' . $form['id'] . '_' . $field->id, $map_property_values );

	return $output;
}

function gpis_get_item_markup( $template, $args ) {

	$markup = $template;

	$markup = str_replace( '{limit}', $args['limit'], $markup );
	$markup = str_replace( '{count}', $args['count'], $markup );
	$markup = str_replace( '{count_current_field}', $args['count_current_field'], $markup );
	$markup = str_replace( '{available}', $args['available'], $markup );
	$markup = str_replace( '{label}', $args['label'], $markup );
	$markup = str_replace( '{value}', $args['value'], $markup );

	return $markup;
}
