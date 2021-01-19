<?php
/**
 * Gravity Perks // Limit Submission // Redirect After Limit Reached
 *
 * Plugin Name:  GPLS Redirect After Limit Reached
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-limit-submissions/
 * Description:  Redirect to a specified URL when the limit is reached (rather than displaying a limit message).
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 *
 * Instructions
 *
 * 1. Download and install this plugin.
 * 2. Set the Limit Message setting in your Limit Submissions feed to any valid URL.
 *
 * That's it! This plugin will now handle redirecting to that URL rather than displaying the limit message.
 */
add_filter( 'gform_get_form_filter', function( $form_string ) {
	if ( is_callable( 'gp_limit_submissions' ) && gp_limit_submissions()->enforce->is_limit_reached() ) {
		$url = gp_limit_submissions()->enforce->test()->failed_rule_group->get_message();
		if ( GFCommon::is_valid_url( $url ) ) {
			echo "<script>window.location = '{$url}';</script>";
			exit;
		}
	}
	return $form_string;
} );
