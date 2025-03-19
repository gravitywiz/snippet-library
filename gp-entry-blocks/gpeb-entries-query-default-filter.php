<?php
/**
 * Gravity Perks // Entry Blocks // Add Default Filter to Entries Query
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * This snippet adds a default filter to the Entries Query for all Entry Blocks. While this has many uses, this snippet's
 * primary intent is to ensure that only users can only see entries that belong to their own group. In this scenario,
 * each entry has a field indicating its "Group ID" and each user has a user meta value indicating to which group they
 * belong.
 */
add_filter( 'gpeb_entries_query', function( $filters, $form_id, $block_context ) {

	// Update "123" to your form ID.
	if ( $form_id !== 123 ) {
		return $filters;
	}

	$raw_filter = array(
		// Update "1" to the ID of the field in which the group to which an entry belongs is stored.
		'property' => '1',
		'operator' => 'is',
		// Update "groupid" to the meta key of the user meta value indicating to which group the user belongs.
		'value'    => 'gf_custom:{user:groupid}',
	);

	static $_running;

	if ( $_running ) {
		return $filters;
	} else {
		$_running = true;
	}

	$queryer          = GP_Entry_Blocks\GF_Queryer::attach( $block_context );
	$processed_filter = $queryer->process_filter( array(), array(
		'filter_value'       => apply_filters( 'gpeb_filter_value', $raw_filter['value'], $raw_filter, $queryer ),
		'filter'             => $raw_filter,
		'filter_group'       => array(),
		'filter_group_index' => 0,
		'form_id'            => $form_id,
		'property'           => null,
		'property_id'        => $raw_filter['property'],
	) )[0][0];

	if ( empty( $filters ) ) {
		$filters[] = array();
	}

	// Add our filter as an "AND" condition for each filter group to ensure that it cannot be unintentionally bypassed.
	foreach ( $filters as &$group ) {
		$group[] = $processed_filter;
	}

	$_running = false;

	return $filters;
}, 10, 3 );
