<?php
/**
 * Gravity Wiz // Gravity Forms // Close Comments when Post Created via Gravity Forms
 * https://gravitywiz.com/close-comments-when-post-created-via-gravity-forms/
 *
 * This simple snippet allows you to close comments on posts created via a Gravity Form.
 */
// Update the "123" to the ID of your form.
add_filter( 'gform_post_data_123', function( $post_data ) {

	$post_data['comment_status'] = 'closed';

	return $post_data;
}  );
