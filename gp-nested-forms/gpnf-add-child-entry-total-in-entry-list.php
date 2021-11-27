<?php
/**
 * Gravity Perks // Nested Forms // Add Total Entry Count for Nested Form Fields in Entry List View
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Add the total count of child entries for each Nested Form field in its label when viewed in the Entry List view.
 */
add_filter( 'gform_form_post_get_meta', function( $form ) {
	if ( GFForms::get_page() === 'entry_list' ) {
		foreach( $form['fields'] as &$field ) {
			if ( $field->get_input_type() === 'form' ) {
				// We just need the total via the $total variable set by reference.
				GFAPI::get_entries(
					$field->gpnfForm,
					array(
						'status'        => rgget( 'filter' ) ?: 'active',
						'field_filters' => array(
							'mode' => 'all',
							array(
								'key'   => GPNF_Entry::ENTRY_NESTED_FORM_FIELD_KEY,
								'value' => $field->id,
							),
							// We don't want orphaned entries.
							array(
								'key'   => GPNF_Entry::ENTRY_EXP_KEY,
								'value' => null,
							),
						),
					),
					array(
						'key'        => 'id',
						'direction'  => 'ASC',
						'is_numeric' => true,
					),
					array(
						'offset'    => 0,
						'page_size' => 0,
					),
					$total
				);
				$field->label .= sprintf( ' (%d %s)', $total, strtolower( $total > 1 ? $field->get_items_label() : $field->get_item_label() ) );
			}
		}
	}
	return $form;
} );
