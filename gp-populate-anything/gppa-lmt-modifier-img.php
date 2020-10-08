<?php
/**
 * Gravity Perks // Populate Anything // Live Merge Tag Modifier: img
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Use the "img" modifier to output an image for any merge tag that returns an attachment ID.
 *
 * For example, here is a field configured to allow the user to select from a list of posts. The value of each choice is
 * the attachment ID of the featured image (e.g. "_thumbnail_id").
 *
 * ![Screenshot of field settings](https://gwiz.io/36LzNPM)
 *
 * In an HTML field on the same form, use the @{Field Label:1:value,img} to take the attachment ID (which is the value
 * of the choice) and output the image it references.
 */
add_filter( 'gppa_live_merge_tag_value', function( $value, $merge_tag, $form, $field_id, $entry_values ) {
	$lmt = GP_Populate_Anything_Live_Merge_Tags::get_instance();
	$modifiers = $lmt->extract_merge_tag_modifiers( $merge_tag );
	if ( rgar( $modifiers, 'img' ) ) {
		$value = wp_get_attachment_image( $value, 'thumbnail' );
	}
	return $value;
}, 10, 5 );