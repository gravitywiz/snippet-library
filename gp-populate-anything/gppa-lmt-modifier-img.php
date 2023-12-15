<?php
/**
 * Gravity Perks // Populate Anything // Live Merge Tag Modifier: img
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Use the "img" modifier to output an image, rather than the merge tag's default output.
 *
 * For example, here is a field configured to allow the user to select from a list of posts. The value of each choice is
 * the attachment ID of the featured image (e.g. "_thumbnail_id").
 *
 * ![Screenshot of field settings](https://gwiz.io/36LzNPM)
 *
 * In an HTML field on the same form, use the @{Field Label:1:value,img} to take the attachment ID (which is the value
 * of the choice) and output the image it references.
 *
 * This also works with any field that is populated with an image URL including comma-delimited lists of URLs. This is
 * relevant when populating URLs into text inputs from a File Upload field.
 */
add_filter( 'gppa_live_merge_tag_value', function( $value, $merge_tag, $form, $field_id, $entry_values ) {

	if ( ! gp_populate_anything()->live_merge_tags ) {
		return $value;
	}

	$modifiers = gp_populate_anything()->live_merge_tags->extract_merge_tag_modifiers( $merge_tag );
	if ( ! rgar( $modifiers, 'img' ) ) {
		return $value;
	}

	// Check for Media Library attachment IDs.
	if ( is_numeric( $value ) ) {
		$value = wp_get_attachment_image( $value, 'thumbnail' );
	} else {
		$images = explode( ',', $value );
		$values = array();
		foreach ( $images as $image ) {
			$values[] = sprintf( '<img src="%s" alt="" style="max-width:100%%;">', $image );
		}
		$value = implode( '', $values );
	}

	return $value;
}, 10, 5 );
