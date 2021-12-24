/**
 * Gravity Perks // Nested Forms // Sync Parent/Child Entry Payment Status
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Sync the payment status of child entries with their parent's. This is useful with GP Limit Choices and GP Inventory
 * to ensure that limits/inventories applied to child field's will not include child entries where payment has not been
 * collected for their parent entry.
 * 
 * Plugin Name:  GP Nested Forms — Sync Parent/Child Entry Payment Status
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Sync the payment status of child entries with their parent's.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_action( 'gform_after_submission', function( $entry, $form ) {
	gpnf_update_child_entries_payment_status( $entry );
}, 10, 2 );

add_action( 'gform_post_update_entry', function( $entry, $original_entry ) {
	if ( $original_entry['payment_status'] !== $entry['payment_status'] ) {
		gpnf_update_child_entries_payment_status( $entry );
	}
}, 10, 2 );

add_action( 'gform_after_update_entry', function( $entry_id, $original_entry ) {
	$entry = GFAPI::get_entry( $entry_id );
	if ( $original_entry['payment_status'] !== $entry['payment_status'] ) {
		gpnf_update_child_entries_payment_status( $entry );
	}
}, 10, 2 );

function gpnf_update_child_entries_payment_status( $parent_entry ) {

	$parent_entry = new GPNF_Entry( $parent_entry );
	if ( ! $parent_entry->payment_status ) {
		return;
	}

	$child_entries = $parent_entry->get_child_entries();
	foreach ( $child_entries as $child_entry ) {
		GFAPI::update_entry_property( $child_entry['id'], 'payment_status', $parent_entry->payment_status );
	}

}
