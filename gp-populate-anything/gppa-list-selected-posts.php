<?php
/**
 * Gravity Perks // Populate Anything // List Selected Posts in HTML Field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * When populating a Multi Select field with posts and allowing the user to select posts they're interested in, this
 * snippet will allow you to populate a list of linked post titles based on the selections in the Multi Select field.
 *
 * This snippet requires that the Multi Select value template is mapped to the Post ID and that the Live Merge Tag for
 * this field in the HTML field uses both the "value" modifier and the "listPosts" modifier.
 *
 * Example: @{My Multi Select Field:5:value,listPosts}
 * Screenshot: https://gwiz.io/3zCo8iX
 */
add_filter( 'gppa_live_merge_tag_value', function( $value, $merge_tag, $form, $field_id, $entry_values ) {
	if ( strpos( $merge_tag, 'listPosts' ) === false ) {
		return $value;
	}
	$post_ids = array_filter( array_map( 'trim', explode( ',', $value ) ) );
	if ( empty( $post_ids ) ) {
		return $value;
	}
	$output = array();
	foreach ( $post_ids as $post_id ) {
		$output[] = sprintf( '<a href="%s">%s</a>', get_permalink( $post_id ), get_the_title( $post_id ) );
	}
	return '<ul><li>' . implode( '</li><li>', $output ) . '</li></ul>';
}, 10, 5 );
