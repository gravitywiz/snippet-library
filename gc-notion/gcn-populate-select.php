<?php

/**
 * Gravity Connect // Notion // Populate Select Field with Notion Database Options
 * https://gravitywiz.com/documentation/gravity-connect-notion/
 *
 * Populate a Dropdown or Multi-Select field with the options from a Notion Database Status, Select
 * or Multi-Select property.
 *
 * Instructions:
 *
 * 1. Change FORMID in the filter name to your form ID.
 *
 * 2. Change the $field_id, $database_id, and $property_id variables to match your form and Notion database.
 *
 *   > 💡 The $database_id and $property_id can be found in the Javascript developer console in a GC Notion
 *   > feed settings AFTER the feed is connected to a database. An video demonstrating how to get
 *   > a property ID can be viewed here: https://www.loom.com/share/30dd8d83750f424c8cf85a8f4dd8f5f7
 */

add_filter( 'gform_pre_render_FORMID', function( $form, $ajax, $field_values ) {
	$field_id    = 1; // Change this to the ID of the field you want to populate.
	$database_id = 'DATABASE_ID'; // Change this to the ID of the Notion database which you want to populate values from.
	$property_id = 'PROPERTY_ID'; // Change this to the ID of the property in the database which you want to populate values.

	$notion_account_id = \GC_Notion\Tokens::get_resource_service_account( $database_id );
	if ( empty( $notion_account_id ) ) {
		return $form;
	}

	$token = rgar( \GC_Notion\Tokens::get_service_account_ids_to_tokens(), $notion_account_id );
	if ( empty( $token ) ) {
		return $form;
	}

	foreach ( $form['fields'] as $field ) {
		if ( $field['id'] != $field_id ) {
			continue;
		}

		try {
			$api      = new \GC_Notion\Notion_API_Client( $token );
			$response = $api->get_database( $database_id );

			foreach ( $response['properties'] as $property ) {
				if ( rgar( $property, 'id' ) !== $property_id ) {
					continue;
				}

				$type    = rgar( $property, 'type' );
				$options = rgars( $property, $type . '/options' );
				$choices = array_map( function( $option ) {
					return array(
						'value' => $option['id'],
						'text'  => $option['name'],
					);
				}, $options );

				$field['choices'] = $choices;

				break;
			}
		} catch ( \Exception $e ) {
			// noop
		}
	}

	return $form;
}, 10, 3);
