<?php
/**
 * Gravity Perks // Limit Submission // Prevent GP Limit Submission Validation Errors From Showing On Fields
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */
// Update "123" to your form ID.
add_filter( 'gpls_field_failed_validation_123', '__return_false' );
