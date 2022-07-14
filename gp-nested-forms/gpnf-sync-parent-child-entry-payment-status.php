<?php
/**
 * Gravity Perks // Nested Forms // Sync Parent/Child Entry Payment Info
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Sync the payment info of child entries with their parent's. This is useful with GP Limit Choices and GP Inventory
 * to ensure that limits/inventories applied to child field's will not include child entries where payment has not been
 * collected for their parent entry.
 *
 * Plugin Name:  GP Nested Forms — Sync Parent/Child Entry Payment Info
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Sync the payment info of child entries with their parent's.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_action( 'gform_after_submission', function( $entry, $form ) {
	gpnf_update_child_entries_payment_status( $entry );
}, 10, 2 );

add_action( 'gform_post_update_entry', function( $entry, $original_entry ) {
		gpnf_update_child_entries_payment_status( $entry );
}, 10, 2 );

add_action( 'gform_after_update_entry', function( $entry_id, $original_entry ) {
	$entry = GFAPI::get_entry( $entry_id );
		gpnf_update_child_entries_payment_status( $entry );
}, 10, 2 );


/**
 * Bulk Sync Parent/Child Entry Payment Info
 * https://docs.gravityforms.com/gform_entry_list_bulk_actions/
 * https://docs.gravityforms.com/gform_entry_list_action/
 */

add_filter( 'gform_entry_list_bulk_actions_32', 'add_payment_action', 10, 2 );	//	Add bulk syncing entries via "Bulk Actions" for form 32
function add_payment_action( $actions, $form_id ) {
    $actions['copy_payment_info'] = 'Copy Payment Info to Child Entries';
    return $actions;
}

add_action( 'gform_entry_list_action', 'perform_payment_action', 10, 3 );
function perform_payment_action( $action, $entries, $form_id ) {
    if ( $action == 'copy_payment_info' ) {
        foreach ( $entries as $entry_id ) {
            $entry = GFAPI::get_entry( $entry_id );
			gpnf_update_child_entries_payment_status( $entry );
        }
    }
}

function gpnf_update_child_entries_payment_status( $parent_entry ) {

	$parent_entry = new GPNF_Entry( $parent_entry );
	if ( ! $parent_entry->payment_status ) {
		return;
	}

	$child_entries = $parent_entry->get_child_entries();
	foreach ( $child_entries as $child_entry ) {
		if($parent_entry->payment_status !== $child_entry['payment_status'] || $parent_entry->payment_date !== $child_entry['payment_date'] || $parent_entry->transaction_id !== $child_entry['transaction_id']){
			GFAPI::update_entry_property( $child_entry['id'], 'payment_status', $parent_entry->payment_status );	// Deliberately not syncing payment_amount because the payment_amount from the parent entry will be a total which isn't relevant at the child entry level
			GFAPI::update_entry_property( $child_entry['id'], 'payment_date', $parent_entry->payment_date );
			GFAPI::update_entry_property( $child_entry['id'], 'transaction_id', $parent_entry->transaction_id );
		}
	}

}
