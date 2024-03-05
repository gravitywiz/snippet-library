<?php
/**
 * Gravity Perks // Nested Forms // Sync Child Entry Payment Details w/ Parent
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Sync the payment details of child entries with their parent's. This is useful with GP Limit Choices and GP Inventory
 * to ensure that limits/inventories applied to child field's will not include child entries where payment has not been
 * collected for their parent entry.
 *
 * Plugin Name:  GP Nested Forms — Sync Parent/Child Entry Payment Details
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Sync the payment details of child entries with their parent's.
 * Author:       Gravity Wiz
 * Version:      0.3
 * Author URI:   https://gravitywiz.com
 */
add_action( 'gform_after_submission', function( $entry, $form ) {
	gpnf_sync_child_entries_payment_details( $entry );
}, 10, 2 );

add_action( 'gform_post_update_entry', function( $entry, $original_entry ) {
	gpnf_sync_child_entries_payment_details( $entry );
}, 10, 2 );

add_action( 'gform_after_update_entry', 'gpnf_get_parent_entry_and_sync_child_entries_payment_details' );
add_action( 'gform_update_payment_status', 'gpnf_get_parent_entry_and_sync_child_entries_payment_details' );
add_action( 'gform_update_payment_date', 'gpnf_get_parent_entry_and_sync_child_entries_payment_details' );
add_action( 'gform_update_transaction_id', 'gpnf_get_parent_entry_and_sync_child_entries_payment_details' );

/**
 * Bulk Sync Parent/Child Entry Payment Details
 * https://docs.gravityforms.com/gform_entry_list_bulk_actions/
 * https://docs.gravityforms.com/gform_entry_list_action/
 */

add_filter( 'gform_entry_list_bulk_actions', function( $actions, $form_id ) {
	$form = GFAPI::get_form( $form_id );
	if ( is_callable( 'gp_nested_forms' )
		&& gp_nested_forms()->has_nested_form_field( $form )
		&& gpnf_has_product_field( $form )
	) {
		$actions['gpnf_sync_child_entries'] = 'Sync Child Entry Payment Details';
	}
	return $actions;
}, 10, 2 );

add_action( 'gform_entry_list_action', function( $action, $entries, $form_id ) {
	if ( $action === 'gpnf_sync_child_entries' ) {
		foreach ( $entries as $entry_id ) {
			$entry = GFAPI::get_entry( $entry_id );
			gpnf_sync_child_entries_payment_details( $entry );
		}
	}
}, 10, 3 );

if ( ! function_exists( 'gpnf_sync_child_entries_payment_details' ) ) {
	function gpnf_sync_child_entries_payment_details( $parent_entry ) {

		if ( ! $parent_entry['payment_status'] ) {
			return;
		}

		$parent_entry  = new GPNF_Entry( $parent_entry );
		$child_entries = $parent_entry->get_child_entries();

		// "payment_amount" is excluded as the parents total is not relevant on the entry level.
		$sync_props = array( 'payment_status', 'payment_date', 'transaction_id' );

		foreach ( $child_entries as $child_entry ) {
			foreach ( $sync_props as $sync_prop ) {
				if ( $parent_entry->$sync_prop !== $child_entry[ $sync_prop ] ) {
					GFAPI::update_entry_property( $child_entry['id'], $sync_prop, $parent_entry->$sync_prop );
				}
			}
		}

	}
}

if ( ! function_exists( 'gpnf_has_product_field' ) ) {
	function gpnf_has_product_field( $form ) {

		foreach ( $form['fields'] as $field ) {
			if ( GFCommon::is_product_field( $field['type'] ) ) {
				return true;
			}
		}

		return false;
	}
}

if ( ! function_exists( 'gpnf_get_parent_entry_and_sync_child_entries_payment_details' ) ) {
	function gpnf_get_parent_entry_and_sync_child_entries_payment_details( $entry_id ) {
		// When editing entry, get the entry_id from $_GET.
		if ( rgar( $_GET, 'view' ) === 'entry' ) {
			$entry_id = rgar( $_GET, 'lid' );
		}

		$entry = GFAPI::get_entry( $entry_id );
		gpnf_sync_child_entries_payment_details( $entry );
	}
}
