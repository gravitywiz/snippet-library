<?php
/**
 * Gravity Perks // Populate Anything // Display Post Featured Image
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Populate post featured image into an HTML field.
 *
 * Usage:
 *
 * 1. Populate the value of a field with a post ID.
 * 2. Insert the live merge tag for that field into the content setting of an HTML field.
 * 3. The live merge tag should have the `:featured_image` modifer like so: `@{Field Label:4:featured_image}` (where 4 is the ID of the field).
 */
// Update 123 to your Form ID
add_filter( 'gppa_live_merge_tag_value_123', function( $merge_tag_match_value, $merge_tag, $form, $field_id, $entry_values ) {

	$bits      = explode( ':', str_replace( array( '{', '}' ), '', $merge_tag ) );
	$modifiers = explode( ',', array_pop( $bits ) );
	if ( ! in_array( 'featured_image', $modifiers, true ) ) {
		return $merge_tag_match_value;
	}

	$post_id = rgar( $entry_values, $field_id );
	if ( ! $post_id ) {
		return $merge_tag_match_value;
	}

	$feautured_image = get_the_post_thumbnail( $post_id );

	return $feautured_image;
}, 10, 5 );
