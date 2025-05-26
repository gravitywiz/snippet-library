<?php

/**
 * Gravity Connect // Notion // Create Page Relation
 * https://gravitywiz.com/documentation/gravity-connect-notion/
 *
 * Instructions:
 *
 * 1. Create a relation property in your Notion database that you want to populate with the selected page
 *    IDs. Take note of which database the relation property is configured to point to as it will be needed
 *    in the next step.
 * 2. Populate a field in your form with one or more Notion Page ID's. These page IDs need to come from the
 *    the database in the previous step. The easiest way to do this is with the GP Populate Anything plugin,
 *    which allows you to populate a field with the IDs of Notion pages. This should work with any field
 *    whose value is will be a single string, for example a Dropdown, Choice, Single Line Text, or Hidden
 *    field, but isn't limited to those.
 * 3. Configure the Usage Example below to match your Form, Feed, Field ID
 *    and Notion relation property name (this much match the relation property name in Notion exactly).
 */

function gcn_create_relation( $args = array() ) {
	$form_id       = isset( $args['form_id'] ) ? $args['form_id'] : null;
	$feed_id       = isset( $args['feed_id'] ) ? $args['feed_id'] : null;
	$field_id      = isset( $args['field_id'] ) ? $args['field_id'] : null;
	$property_name = isset( $args['property_name'] ) ? $args['property_name'] : null;

	$filter_name_pieces = array( 'gcn_notion_page_data_add' );

	if ( $form_id ) {
		$filter_name_pieces[] = $form_id;
	}

	if ( $form_id && $feed_id ) {
		$filter_name_pieces[] = $feed_id;
	}

	$filter_name = implode( '_', $filter_name_pieces );

	add_filter(
		$filter_name,
		function ( $page_data, $form, $entry, $feed ) use ( $property_name, $field_id ) {
			$page_id   = rgar( $entry, $field_id );
			$prop_type = 'relation';

			if ( empty( $page_id ) ) {
				return $page_data;
			}

			$page_data['properties'][ $property_name ] = array(
				$prop_type => array(
					array(
						'id' => $page_id,
					),
				),
			);

			return $page_data;
		}, 10, 4
	);

}

/**
 * Usage Example:
 */
gcn_create_relation(
	array(
		/**
		 * Change this to your form ID.
		 */
		'form_id'       => 1,
		/**
		 * Change this to the ID of the feed you want to use.
		 * You can technically omit this to apply to all feeds
		 * for the form, but it's recommended to specify it for clarity.
		 */
		'feed_id'       => 2,
		/**
		 * Change this to the ID of the field which holds the Notion Page ID.
		 */
		'field_id'      => 3,
		/**
		 * Change this to the name of the relation property in your Notion database.
		 */
		'property_name' => 'Tasks',
	)
);
