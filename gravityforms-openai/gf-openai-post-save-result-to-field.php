<?php
/**
 * Gravity Perks // GravityForms-OpenAI // Add support to populate fields with Open AI generated values.
 *
 * https://gravitywiz.com/
 *
 * Requires 1.0-beta-1.2; or newer of Gravity Forms OpenAI.
 *
 * Instructions:
 *   - Install using https://gravitywiz.com/gravity-forms-custom-javascript/
 */
// Replace 342 with the form id, and 3 with the targetted field id.
add_action( 'gf_openai_post_save_result_to_field_342', 'gw_openai_post_save_result' ); 
function gw_openai_post_save_result( $result ) {
	$_POST['input_3'] = $result;
}
