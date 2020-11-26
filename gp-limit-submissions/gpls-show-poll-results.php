<?php
/**
 * Gravity Perks // GP Limit Submissions // Display Poll Results When Limit Is Reached
 * http://gravitywiz.com/documentaiton/gravity-forms-limit-submissions/
 */
add_action( 'gform_get_form_filter', function( $markup, $form ) {

	if ( ! is_callable( 'gf_polls' ) || ! gf_polls()->get_form_setting( $form, 'displayResults' ) ) {
		return $markup;
	}

	if ( is_callable( 'gp_limit_submissions' ) && property_exists( gp_limit_submissions(), 'enforce' ) && gp_limit_submissions()->enforce->is_limit_reached() ) {
		add_filter( "gform_get_form_filter_{$form['id']}", function( $markup, $form ) {
			$results = gf_polls()->gpoll_get_results( $form['id'] );
			$markup .= $results['summary'];
			return $markup;
		}, 11, 2 );
	}

	return $markup;
}, 10, 2 );
