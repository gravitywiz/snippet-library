<?php
/**
 * Gravity Perks // Nested Forms // Parse Parent Merge Tags for Webhooks Add-on
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Note: Merge tags must be specified in their "complicated" format to avoid being processed prematurely in the child form context.
 * Example: {Parent:2} → {%GPNF:Parent:2%}
 */
add_filter( 'gform_webhooks_request_data', function( $request_data, $feed, $entry, $form ) {

	foreach ( $request_data as $key => &$value ) {
		$value = gpnf_parent_merge_tag()->parse_parent_merge_tag( $value, $form, $entry, true, true, false, 'text' );
	}

	return $request_data;
}, 10, 4 );
