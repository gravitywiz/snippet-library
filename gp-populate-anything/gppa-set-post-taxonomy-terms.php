<?php
/**
 * Gravity Perks // Populate Anything // Set Post Taxonomy Terms
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * When the Term ID is set as the value of a dynamically populated choice-based, this snippet allows you
 * update the post that is created with the name of the taxonomy instead the term ID.
 *
 * Note: This only applies to forms that have Post Fields.
 */
add_filter( 'gform_after_create_post', function( $post_id, $entry, $form ) {

	foreach ( $form['fields'] as &$field ) {
		if ( is_callable( 'gp_populate_anything' ) && $field->{'gppa-choices-enabled'} && $field->{'gppa-choices-object-type'} == 'term' ) {
			$value    = gp_populate_anything()->get_field_value( $form, $entry, $field->id );
			$term_ids = is_array( $value ) ? $value : explode( ',', $value );
			$term     = get_term( $term_ids[0] );
			wp_set_post_terms( $post_id, $term_ids, $term->taxonomy );
		}
	}

}, 10, 3 );
