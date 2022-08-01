<?php
/**
 * Gravity Perks // Multi-Page Navigation // Disable Submission From Last Page With Errors
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 */
add_filter( 'gpmpn_enable_submission_from_last_page_with_errors', '__return_false' );
