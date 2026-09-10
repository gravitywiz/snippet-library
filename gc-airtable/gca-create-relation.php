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
 * Optionally, the snippet can search the linked table for an existing record and
 * link it instead of creating a duplicate. If no matching record is found, a new
 * linked record is created as usual.
 *
 * TIP: you can easily find the following by creating a new GC Airtable feed, connecting
 * it to the Table which you want to create the relation in and then saving.
 * If you open the developer console in your browser and refresh the page, a table of all
 * the fields in the table will be logged.
 *
 *     - $args['linked_table_id']
 *     - $args['link_field_id']
 *     - $args['value_mappings'] (Airtable field IDs mapped to Gravity Forms field IDs or merge-tag templates)
 *     - $args['match_field_id'] (the Airtable field ID to populate when a new linked record is created)
 *     - $args['match_field_name'] (the name of the same Airtable field, used to find an existing linked record)
 *     - $args['match_value'] (a Gravity Forms merge-tag template used to build the value to match)
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
 * @param? array $args['value_mappings']        Maps Airtable field IDs to Gravity Forms fields or merge-tag templates.
 * @param? string $args['match_field_id']       The ID of the Airtable field populated when a new linked record is created.
 * @param? string $args['match_field_name']     The name of the Airtable field used to match an existing linked record.
 * @param? string $args['match_value']          A Gravity Forms merge-tag template that resolves to the value to match.
 *
 * @return void
 */
function gca_create_relation( $args = array() ) {
	$args = wp_parse_args(
		$args,
		array(
			'form_id'          => null, // include form ID
			'feed_id'          => null,
			'linked_table_id'  => null, // The ID of the Phone Numbers table.
			'link_field_id'    => null, // The ID of the field in the Phone Numbers table that links to the People table.
			'value_mappings'   => array(), // Map Airtable fields to Gravity Forms fields or merge-tag templates.
			'match_field_id'   => null,
			'match_field_name' => null,
			'match_value'      => null,
		)
	);

	if ( ! $args['linked_table_id'] || ! $args['link_field_id'] ) {
		return;
	}

	$has_match_value  = $args['match_value'] !== null && $args['match_value'] !== '';
	$has_match_config = $args['match_field_id'] || $args['match_field_name'] || $has_match_value;
	$can_match        = $args['match_field_id'] && $args['match_field_name'] && $has_match_value;

	if ( $has_match_config && ! $can_match ) {
		gc_airtable()->log_error( 'gca_create_relation(): All three matching settings are required.' );
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
		$sync_relation = function ( $entry, $create_record_resp, $gca_connection_instance ) use ( $args, $can_match ) {
			if ( empty( $args['linked_table_id'] ) ) {
				return;
			}

			$main_record_id = rgar( $create_record_resp, 'id' );

			if ( empty( $main_record_id ) ) {
				return;
			}

			$base_id = $gca_connection_instance->get_base_id();

			$value_mappings = $args['value_mappings'];
			$mappings       = array();

			foreach ( $value_mappings as $airtable_field_id => $field_id_or_template ) {
				$is_merge_tag_template = is_string( $field_id_or_template ) &&
					strpos( $field_id_or_template, '{' ) !== false &&
					strpos( $field_id_or_template, '}' ) !== false;

				if ( $is_merge_tag_template ) {
					$value = GFCommon::replace_variables(
						$field_id_or_template,
						$gca_connection_instance->get_form(),
						$entry,
						false,
						false,
						false,
						'text'
					);
				} else {
					$value = rgar( $entry, $field_id_or_template );
				}

				if ( $value === '' || $value === null ) {
					// do not use empty() here so that the values 0 and 0.0 are allowed.
					continue;
				}

				$mappings[ $airtable_field_id ] = $value;
			}

			try {
				$airtable_api = $gca_connection_instance->get_airtable_api();

				if ( $can_match ) {
					$match_value = trim( (string) GFCommon::replace_variables(
						$args['match_value'],
						$gca_connection_instance->get_form(),
						$entry,
						false,
						false,
						false,
						'text'
					) );

					if ( $match_value === '' ) {
						gc_airtable()->log_error( 'gca_create_relation(): The configured match value resolved to an empty value.' );
						return;
					}

					$field_name    = str_replace(
						array( '\\', '}' ),
						array( '\\\\', '\\}' ),
						$args['match_field_name']
					);
					$formula_value = str_replace(
						array( '\\', '"' ),
						array( '\\\\', '\\"' ),
						(string) $match_value
					);

					$response = $airtable_api->list_records(
						$base_id,
						$args['linked_table_id'],
						array(
							'filterByFormula'       => sprintf( '{%s} = "%s"', $field_name, $formula_value ),
							'returnFieldsByFieldId' => true,
							'maxRecords'            => 2,
							'pageSize'              => 2,
						)
					);
					$matches  = (array) rgar( $response, 'records', array() );

					if ( count( $matches ) > 1 ) {
						gc_airtable()->log_error( 'gca_create_relation(): More than one linked record matched; no relation was created.' );
						return;
					}

					if ( count( $matches ) === 1 ) {
						$matched_record_id = rgar( $matches[0], 'id' );
						$linked_record_ids = rgars(
							$matches[0],
							'fields/' . $args['link_field_id'],
							array()
						);

						if ( empty( $matched_record_id ) ) {
							gc_airtable()->log_error( 'gca_create_relation(): Airtable did not return the matched record ID.' );
							return;
						}

						if ( ! is_array( $linked_record_ids ) ) {
							$linked_record_ids = array();
						}

						$linked_record_ids[] = $main_record_id;

						$airtable_api->patch_record(
							$base_id,
							$args['linked_table_id'],
							$matched_record_id,
							array(
								$args['link_field_id'] => array_values( array_unique( $linked_record_ids ) ),
							)
						);

						return;
					}

					$mappings[ $args['match_field_id'] ] = $match_value;
				}

				$records = array(
					array(
						'fields' => array_merge(
							array(
								$args['link_field_id'] => array( $main_record_id ),
							),
							$mappings
						),
					),
				);

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

	add_action(
		'gform_post_process_feed',
		function ( $feed, $entry, $form, $addon ) use ( $args, $sync_relation ) {
			if ( rgar( $feed, 'addon_slug' ) !== 'gc-airtable' ) {
				return;
			}

			if ( $args['form_id'] && (int) rgar( $form, 'id' ) !== (int) $args['form_id'] ) {
				return;
			}

			if (
				$args['form_id'] &&
				$args['feed_id'] &&
				(int) rgar( $feed, 'id' ) !== (int) $args['feed_id']
			) {
				return;
			}

			$main_record_id = gc_airtable()->get_resource_id( $entry, $feed );

			if ( empty( $main_record_id ) ) {
				return;
			}

			try {
				$connection = \GC_Airtable\Airtable_Connection::create_from_feed( $feed );

				$sync_relation(
					$entry,
					array( 'id' => $main_record_id ),
					$connection
				);
			} catch ( Exception $e ) {
				$msg = gca_get_exception_message( $e );
				gc_airtable()->log_error( $msg );
			}
		},
		10,
		4
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
		'form_id'          => 1,
		/**
		 * Change this to the ID of the feed you want to use.
		 */
		'feed_id'          => 2,
		/**
		 * Change this to the ID of the linked table in Airtable.
		 */
		'linked_table_id'  => 'tblXXXXXXXXXXXXXX',
		/**
		 * Change this to the ID of the link field in Airtable.
		 */
		'link_field_id'    => 'fldXXXXXXXXXXXXXX',
		/**
		 * Optional. To link an existing record when one matches, provide all three settings:
		 *
		 * - The ID of the Airtable field to populate when a new linked record is created.
		 * - The name of that same Airtable field, used by Airtable's matching formula.
		 * - A Gravity Forms merge-tag template that resolves to the value to match.
		 *
		 * Omit all three settings to retain the original always-create behavior.
		 */
		'match_field_id'   => 'fldXXXXXXXXXXXXXX',
		'match_field_name' => 'Name',
		'match_value'      => '{Name (First):3.3} {Name (Last):3.6}',
		/**
		 * Change this to an array of value mappings.
		 * The keys are Airtable field IDs. Values can be Gravity Forms field IDs
		 * or merge-tag templates.
		 * These values are only written when a new linked record is created.
		 */
		'value_mappings'   => array(
			'fldXXXXXXXXXXXXXX' => 3, // Map to the raw value of Gravity Forms field 3.
			'fldYYYYYYYYYYYYYY' => '{Booking time:4:startDate,format[yyyy-MM-dd]}',
			// Add more mappings as needed
		),
	)
);
