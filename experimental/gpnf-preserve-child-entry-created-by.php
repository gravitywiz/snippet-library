<?php
/**
 * Gravity Perks // Nested Forms // Preserve Child Entry's Original "created_by" Property
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This snippet will need to be revisited if we ever use the GP_Nested_Forms::handle_parent_submission_post_save() filter
 * to handle anything besides setting the child entry "created_by" property.
 */
add_action( 'init', function() {
	if ( is_callable( 'gp_nested_forms' ) ) {
		remove_action( 'gform_entry_post_save', array( gp_nested_forms(), 'handle_parent_submission_post_save' ), 20 );
	}
}, 16 );
