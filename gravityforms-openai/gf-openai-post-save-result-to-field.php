<?php
/**
 * Gravity Perks // Gravity Forms OpenAI // Add support to re-populate fields with Open AI generated values.
 * https://gravitywiz.com/
 *
 * Enables the transfer of generated values from Gravity Forms OpenAI to GP Reload Form after form submission, which would otherwise not happen.
 * This ensures that the generated values from Gravity Forms OpenAI are reloaded into the form when GP Reload Form is loaded.
 *
 * Requires 1.0-beta-1.2; or newer of Gravity Forms OpenAI.
 *
 * Instruction Video: https://www.loom.com/share/341ab02ca790461fa852eda2f8e95fbc
 *
 * Instructions: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
// Replace 1 with the form id, and 3 with the targetted field id.
add_action( 'gf_openai_post_save_result_to_field_1', function ( $result ) {
	$_POST['input_3'] = $result;
} );
