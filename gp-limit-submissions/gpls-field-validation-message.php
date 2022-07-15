<?php
/**
 * Gravity Perks // Limit Submission // Change Validation Message for a specific field
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 *
 * The following example changes the GP Limit Submission validation message on field #3 in form #5.
 */
add_filter( 'gpls_field_failed_validation_123_4', function( $message ) { 
    return 'Example validation message.'; 
} );
