<?php
/**
 * Gravity Wiz // Gravity Forms // Create Dynamic Post Excerpts with Gravity Forms
 * https://gravitywiz.com/create-dynamic-post-excerpts-gravity-forms/
 *
 * This snippet allows you to create your own content template for the Post Excerpt.
 */
// Update "123" to the ID of your form.
add_filter( 'gform_post_data_123', 'gw_post_excerpt_content_template', 10, 3 );
function gw_post_excerpt_content_template( $post_data, $form, $entry ) {

	// Modify this to include whatever merge tags and words you'd like included in the excerpt.
	$excerpt_template = '{Name (First):1.3} {Name (Last):1.6} tells a riveting tale about {Subject:3}.';

	$post_data['post_excerpt'] = GFCommon::replace_variables( $excerpt_template, $form, $entry );

	return $post_data;
}
