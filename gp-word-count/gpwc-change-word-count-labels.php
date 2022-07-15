<?php
/**
 * Gravity Perks // Word Count // Change the Word Count Labels
 * https://gravitywiz.com/documentation/gravity-forms-word-count/
 *
 * Modify the word count labels for this all fields.
 */
add_filter( 'gpwc_script_args', 'gpwc_modify_script_args', 10, 3 );
function gpwc_modify_script_args( $args, $field, $form ) {

	$args['defaultLabel']       = '{count} / {limit}';
	$args['counterLabel']       = '{count} / {limit}';
	$args['limitReachedLabel']  = '<span style="font-weight:bold">{count} / {limit}</span>';
	$args['limitExceededLabel'] = '<span style="font-weight:bold;color:#f00">{count} / {limit}</span>';

	return $args;
}
