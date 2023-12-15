<?php
/**
 * Gravity Perks // Easy Passthrough // Generate Tokens on Import
 * https://gravitywiz.com/documentation/gravity-forms-easy-passthrough/
 *
 * Generate tokens for entries that are imported using GravityImport (formerly GravityView Importer)
 * https://www.gravitykit.com/extensions/gravity-forms-entry-importer/
 */
/**
 * @param array $entry
 *
 * @return boolean
 */
function gpep_gravityimporter_should_run_for_entry( $entry ) {
	if ( ! function_exists( 'gp_easy_passthrough' ) || ! method_exists( gp_easy_passthrough(), 'get_entry_token' ) ) {
		return false;
	}

	if ( empty( gp_easy_passthrough()->get_feeds( $entry['form_id'] ) ) ) {
		return false;
	}

	return true;
}

/**
 * @param array $entry
 *
 * @return void
 */
function gpep_gravityimporter_maybe_generate( $entry ) {
	if ( ! gpep_gravityimporter_should_run_for_entry( $entry ) ) {
		return;
	}

	// Force token to be generated if one doesn't already exist
	gp_easy_passthrough()->get_entry_token( $entry );
}

add_action( 'gravityview/import/entry/created', 'gpep_gravityimporter_maybe_generate' );
add_action( 'gravityview/import/entry/updated', 'gpep_gravityimporter_maybe_generate' );
