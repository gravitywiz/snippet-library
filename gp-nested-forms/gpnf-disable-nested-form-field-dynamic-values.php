<?php
/**
 * Gravity Perks // Nested Forms // Disable Nested Form Field Dynamic Values
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * When existing child entries are populated into the nested form field of a different parent form,
 * it will no longer be attached to the original parent entry when the new parent form is submitted.
 * Use this snippet to attach the child entries to the new parent entry and also allow the
 * child entries to remain attached to the original parent entry.
 */
add_filter( 'gpnf_should_use_static_value', '__return_true' );
