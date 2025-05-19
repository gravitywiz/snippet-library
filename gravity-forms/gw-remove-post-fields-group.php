<?php
/**
 * Gravity Wiz // Gravity Forms // Remove Post Fields Group from Form Editor
 * https://gravitywiz.com/
 *
 * Removes the "Post Fields" group from the form editor field palette. A great way to prevent accidental post creation
 * if you have clients managing their own forms.
 */
add_filter( 'gform_field_groups_form_editor', function( $field_groups ) {
    unset( $field_groups['post_fields'] );
    return $field_groups;
} );
