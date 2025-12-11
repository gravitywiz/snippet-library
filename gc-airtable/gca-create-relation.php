<?php
/**
 * Gravity Connect // Airtable // Create Relation
 *
 * This snippet demonstrates how to create a relation between two tables in Airtable
 * when a GC Airtable feed is being processed. It uses an example assuming the following:
 *
 *   1. There is an Airtable Base with at least two tables.
 *   2. There is a Gravity Forms feed that is connected to one of the tables in the Airtable Base.
 *
 * TIP: you can easily find the following by creating a new GC Airtable feed, connecting
 * it to the Table which you want to create the relation in and then saving.
 * If you open the developer console in your browser and refresh the page, a table of all
 * the fields in the table will be logged.
 *
 *     - $args['linked_table_id']
 *     - $args['link_field_id']
 *     - $args['value_mappings'] (the Airtable field IDs which you want to optionally add data to in the new linked record)
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * References:
 *   * https://gravitywiz.com/documentation/gravity-connect-airtable
 *   * https://gravitywiz.com/documentation/gca_entry_added_to_airtable/
 */

/**
 * @param mixed $args
 * @param? array $args['form_id']               The ID of the form to which this relation applies.
 * @param? array $args['feed_id']               The ID of the feed to which this relation applies. (Only used if form_id is also provided)
 * @param string $args['linked_table_id']       The ID of the linked table in Airtable.
 * @param string $args['link_field_id']         The ID of the field in the linked table that links to table connected to the feed.
 * @param? array $args['value_mappings']        An associative array mapping Airtable field IDs to Gravity Forms field IDs.
 *
 * @return void
 */
function gca_create_relation( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'form_id'         => null, // include form ID
			'feed_id'         => null,
			'linked_table_id' => null, // The ID of the Phone Numbers table.
			'link_field_id'   => null, // The ID of the field in the Phone Numbers table that links to the People table.

			'value_mappings'  => array(), // The value mappings of Airtable field ids to Graivty Forms field ids.
		)
	);

	if ( ! $args['linked_table_id'] || ! $args['link_field_id'] ) {
		return;
	}

	$filter_name_pieces = array( 'gca_entry_added_to_airtable' );

	if ( $args['form_id'] ) {
		$filter_name_pieces[] = $args['form_id'];
	}

	if ( $args['form_id'] && $args['feed_id'] ) {
		$filter_name_pieces[] = $args['feed_id'];
	}

	$filter_name = implode( '_', $filter_name_pieces );

	add_action(
		$filter_name,
		function( $entry, $create_record_resp, $gca_connection_instance ) use ( $args ) {
			if ( empty( $args['linked_table_id'] ) ) {
				return;
			}

			$base_id = $gca_connection_instance->get_base_id();

			$value_mappings = $args['value_mappings'];
			$mappings       = array();

			foreach ( $value_mappings as $airtable_field_id => $gf_field_id ) {
				$value = rgar( $entry, $gf_field_id );

				if ( $value === '' || $value === null ) {
					// do not use empty() here so that the values 0 and 0.0 are allowed.
					continue;
				}

				$mappings[ $airtable_field_id ] = $value;
			}

			$records = array(
				array(
					'fields' => array_merge(
						array(
							$args['link_field_id'] => array( $create_record_resp['id'] ),
						),
						$mappings
					),
				),
			);

			try {
				$airtable_api       = $gca_connection_instance->get_airtable_api();
				$create_record_resp = $airtable_api->create_records(
					$base_id,
					$args['linked_table_id'],
					$records
				);
			} catch ( Exception $e ) {
				$msg = gca_get_exception_message( $e );
				gc_airtable()->log_error( $msg );
			}
		},
		10,
		3
	);
}

/**
 * Usage Example:
 */
gca_create_relation(
	array(
		/**
		 * Change this to your form ID.
		 */
		'form_id'         => 1,
		/**
		 * Change this to the ID of the feed you want to use.
		 */
		'feed_id'         => 2,
		/**
		 * Change this to the ID of the linked table in Airtable.
		 */
		'linked_table_id' => 'tblXXXXXXXXXXXXXX',
		/**
		 * Change this to the ID of the link field in Airtable.
		 */
		'link_field_id'   => 'fldXXXXXXXXXXXXXX',
		/**
		 * Change this to an array of value mappings.
		 * The keys are Airtable field IDs and the values are Gravity Forms field IDs.
		 */
		'value_mappings'  => array(
			'fldXXXXXXXXXXXXXX' => 3, // map Airtable field "fldXXXXXXXXXXXXXX" to Gravity Forms field with ID 3
			// Add more mappings as needed
		),
	)
);
