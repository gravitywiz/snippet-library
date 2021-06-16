<?php
/**
 * Gravity Perks // GP Unique ID // Retroactively Populate Unique IDs
 * https://gravitywiz.com/documentation/gp-unique-id/
 *
 * 1. Copy and paste this code into your theme's functions.php file.
 * 2. Go to your home page and add the following parameters to the query string:
 *
 *     gpui_retro_pop=1
 *     form_id=123
 *
 *     Make sure you replace "123" with the ID of your form. Full URL should look like this:
 *
 *     http://mysite.com/?gpui_retro_pop=1&form_id=123
 *
 * 3. Submit the updated URL. If everything is correct, you will be greeted with a success message and updated entry count.
 *
 * NOTE: This snippet has a hard-coded limit of 999 entries.
 */
if( isset( $_GET['gpui_retro_pop'] ) && class_exists( 'GFAPI' ) ) {

	$form_id = rgget( 'form_id' );
	$form = GFAPI::get_form( $form_id );
	if( ! $form ) {
		echo 'Please provide a valid form ID.';
		exit;
	}

	$unique_id_fields = GFCommon::get_fields_by_type( $form, array( 'uid' ) );
	if( empty( $unique_id_fields ) ) {
		echo 'There are no Unique ID fields on this form.';
		exit;
	}

	$filters = array();
	foreach( $unique_id_fields as $unique_id_field ) {
		$filters[] = array(
			'key' => $unique_id_field->id,
			'value' => null
		);
	}
	$filters['mode'] = 'any';

	$entries = GFAPI::get_entries( $form['id'], array(
		'field_filters' => $filters,
	), null, array(
		'offset' => 0,
		'page_size' => 999
	) );

	if( empty( $entries ) ) {
		echo 'There are no entries requiring retroactive population.';
		exit;
	}

	$count = 0;

	foreach( $entries as $entry ) {

		$has_update = false;

		foreach( $unique_id_fields as $unique_id_field ) {
			if( empty( $entry[ $unique_id_field->id ] ) ) {
				$entry[ $unique_id_field->id ] = gp_unique_id()->get_unique( $form['id'], $unique_id_field );
				$has_update = true;
			}
		}

		if( $has_update ) {
			GFAPI::update_entry( $entry );
			$count++;
		}

	}

	printf( '%d entries updated.', $count );

	exit;
}