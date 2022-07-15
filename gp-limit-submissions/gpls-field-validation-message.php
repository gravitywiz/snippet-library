<?php
/**
 * Gravity Perks // Limit Submission // Change Validation Message for a Specific Field
 * https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 */
add_filter( 'gpls_field_failed_validation_123_4', function( $message ) { 
    return 'Example validation message.'; 
} );
