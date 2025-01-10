<?php
/**
 * Gravity Connect // Airtable // Create Phone Number Relation
 *
 * Experimental Snippet 🧪
 *
 * This snippet demonstrates how to create a relation between two tables in Airtable
 * when a GC Airtable feed is being processed. It uses an example assuming the following:
 *
 *   1. There are a "People" table and "Phone Numbers" table in Airtable.
 *   2. The "Phone Numbers" table has, at minimum, a phone number field and a link field.
 *   3. There is a GCA feed that creates a record in the "People" table.
 *   4. The snippet adds a phone number to the "Phone Numbers" table and creates a relation
 *    between the newly created "People" record and the "Phone Numbers" record.
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * References:
 *   * https://gravitywiz.com/documentation/gravity-connect-airtable
 *   * https://gravitywiz.com/documentation/gca_entry_added_to_airtable/
 */

/**
 * You can also apply this to individual forms for feeds for more granular control. For example:
 *
 * add_action( 'gca_entry_added_to_airtable_FORMID', function( $entry, $create_record_resp, $gca_connection_instance ) {} );
 * add_action( 'gca_entry_added_to_airtable_FORMID_FEEDID', function( $entry, $create_record_resp, $gca_connection_instance ) {} );
 *
 */
add_action( 'gca_entry_added_to_airtable', function( $entry, $create_record_resp, $gca_connection_instance ) {
	$gf_phone_number_field_id = '1'; // The ID of the form field which contains the value you want to use to create the relation.
	$table_id                 = 'TODO'; // The ID of the Phone number
	$base_id                  = $gca_connection_instance->get_base_id();

	$phone_number = rgar( $entry, $gf_phone_number_field_id );

	if ( empty( $phone_number ) ) {
		return;
	}

	/**
	 * TIP: you can easily find the following by creating a new GC Airtable feed, connecting
	 * it to the "Phone Numbers" table and saving. If you open the developer console in your
	 * browser and refresh the page, a table of all the fields in the table will be logged.
	 */
	$phone_field_id = 'TODO'; // The ID of the phone number field in the Phone Numbers table.
	$link_field_id  = 'TODO'; // The ID of the link field in the Phone Numbers table.

	$records = array(
		array(
			'fields' => array(
				$phone_field_id => $phone_number,
				$link_field_id  => array( $create_record_resp['id'] ),
			),
		),
	);

	try {
		$airtable_api       = $gca_connection_instance->get_airtable_api();
		$create_record_resp = $airtable_api->create_records( $base_id, $table_id, $records );
	} catch ( Exception $e ) {
		$msg = gca_get_exception_message( $e );
		gc_airtable()->log_error( $msg );
	}
}, 10, 3 );
