<?php
/**
 * Gravity Perks // Populate Anything // Remove Empty Label Segments by Delimiter
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Let's say you have a Populate-Anything-populated field that has a Custom Value template configured like this:
 * {post:post_title} - {post:post_excerpt}
 *
 * This would populate the post title and post excerpt for each populated choice; however, some posts may not have an
 * excerpt. This snippet will split the string up by the delimiter (in this case, " - ") and remove any empty segments
 * so the final result may look like "Post Title - Post Excerpt" or, if there is no excerpt, just "Post Title".
 */
// Update "123" to your form ID and "4" to your field ID.
add_filter( 'gppa_input_choices_123_4', function( $choices ) {
	foreach ( $choices as &$choice ) {
		// Update ' - ' to the value you are using between merge tags in your custom label template.
		$delimiter      = ' - ';
		$bits           = array_filter( array_map( 'trim', explode( $delimiter, $choice['text'] ) ) );
		$choice['text'] = implode( $delimiter, $bits );
	}
	return $choices;
} );
