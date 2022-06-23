<?php
/**
 * Gravity Perks // Conditional Pricing // Preserve Pricing Logic At Time of Submission
 * https://gravitywiz.com/documentation/gravity-forms-conditional-pricing/
 *
 * Plugins that provide editing functionality (like Entry Blocks and GravityView) will allow you to modify previously
 * selected products. If you make changes to your Conditional Pricing rules, the original price will be lost when the
 * entry is loaded for editing.
 *
 * This snippet saves the current pricing logic when the entry is created and then restores it when the entry is loaded
 * for editing.
 *
 * Note: We do not recommend this approach if you have more than a hundred or so pricing rules.
 */
add_action( 'gpcp_pricing_logic', function( $pricing_logic, $form ) {

	if ( ! is_callable( 'gravityview' ) ) {
		return $pricing_logic;
	}

	$entry = gravityview()->request->is_edit_entry();
	if ( ! $entry ) {
		return $pricing_logic;
	}

	$saved_pricing_logic = gform_get_meta( $entry->ID, 'gpcp_pricing_logic' );
	if ( ! $saved_pricing_logic ) {
		return $pricing_logic;
	}

	return $saved_pricing_logic;
}, 10, 2 );

add_action( 'gform_entry_created', function( $entry, $form ) {
	if ( rgar( $form, 'gw_pricing_logic' ) ) {
		gform_add_meta( $entry['id'], 'gpcp_pricing_logic', $form['gw_pricing_logic'] );
	}
}, 10, 2 );
