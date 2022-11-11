<?php
/**
 * Gravity Wiz // Gravity Forms // Map Multiple Fields w/ Advanced Post Creation
 * https://gravitywiz.com/
 *
 * By default, the Advanced Post Creation add-on does not allow you to map multiple fields to a taxonomy nor does it
 * allow you to set terms by ID.
 * 
 * This snippet allows to specify multiple fields on a form that have been populated with term IDs (we recommend 
 * [Populate Anything][1] for this) and to set the taxonomy/terms based on those IDs for the generated post.
 *
 * [1]: https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_action( 'gform_advancedpostcreation_post_after_creation', function ( $post_id, $feed, $entry, $form ) {
	
	// Update "1", "2", "3" to the field IDs that have been populated with terms. Add additional IDs as needed.
	$term_field_ids = array( 1, 2, 3 );

	// Update "categories" to the name of the taxonomy to which the populated terms belong.
	$taxonomy = 'categories';
	
	$term_ids = array();
	foreach ( $term_field_ids as $term_field_id ) {
		$term_ids[] = (int) $entry[ $term_field_id ];
	}
	
	wp_set_object_terms( $post_id, $term_ids, $taxonomy );
	
}, 10, 4 );
