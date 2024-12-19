<?php
/**
 * Gravity Connect // Notion // Radio Button to Date Property Mapping
 *
 * This snippet demonstrates how to map a radio button field to a Date property in Notion.
 *
 * Instructions:
 *   1. Modify the filter name to scope as needed.
 *     * see filter reference for full list of variants: https://gravitywiz.com/documentation/gcn_notion_page_data/)
 *
 *   2. Get the Date property ID from the Notion database and update the $property_id variable.
 *     * Enable the gcn_show_feed_database_debug_info hook: https://gravitywiz.com/documentation/gcn_show_feed_database_debug_info/
 *     * Open the browser developer console and navigate to a GC Notion feed which is connected to the database you are populating into.
 *     * Scroll up in the developer console until you see the debug info displayed.
 *     * Find the date property and copy the ID.
 *   3. Double check that the date format in the radio field options matches the $date_format variable.
 *     * Note: if it doesn't, you can either adjust the date format in the radio field options or adjust the $date_format variable.
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * References:
 *   * GC Notion: https://gravitywiz.com/documentation/gravity-connect-notion/
 *   * Notion page POST reference: https://developers.notion.com/reference/post-page
 *   * Notion Database property reference: https://developers.notion.com/reference/page-property-values#date
 *
 * @since 1.0-beta-1.9
 */

add_filter( 'gcn_notion_page_data', function( $page_data, $form, $entry, $feed ) {
	$radio_date_field_id = 1; // Update this with the ID of the radio field.
	$property_id         = 'TODO'; // Update this with the Date property ID from the Notion database.

	// date format in radio field value expected to be: 'd F Y'. E.g. 01 January 2025
	$date_format       = 'd F Y';
	$radio_field_value = $entry[ $radio_date_field_id ];
	$date              = \DateTime::createFromFormat( $date_format, $radio_field_value );

	// if the radio field was left empty, skip adding the value.
	if ( $date ) {
		$iso_date = $date ? $date->format( 'Y-m-d' ) : '';

		$page_data['properties'][ $property_id ] = array(
			'date' => array(
				'start' => $iso_date,
			),
		);
	}

	return $page_data;
}, 10, 4 );
