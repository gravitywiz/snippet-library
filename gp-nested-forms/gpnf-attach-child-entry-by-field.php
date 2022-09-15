<?php
/**
 * Gravity Perks // Nested Forms // Attach Child Entry by Field
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/6b3b4a4ad0fb420491a98046c5a18217
 * 
 * Attach child entries to a parent entry when the child form is submitted outside a Nested Form field. The attachment
 * happens by specifying a field on the child form that will contain the parent entry ID to which the form should be
 * attached. Tip: Populate Anything can be used to populate this field with existing parent entries. The designated 
 * field will only appear when the child form is accessed outside a Nested Form field.
 */
class GPNF_Attach_Child_Entry_by_Field {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'nested_form_field_id'  => false,
			'child_form_id'         => false,
			'parent_entry_field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_action( 'gform_entry_created', array( $this, 'attach_child_entry_to_parent' ) );
		add_action( 'gform_pre_render', array( $this, 'hide_parent_entry_id_field' ) );

	}

	public function attach_child_entry_to_parent( $child_entry ) {

		$parent_entry_id = rgar( $child_entry, $this->_args['parent_entry_field_id'] );
		if ( ! $parent_entry_id || $child_entry['form_id'] != $this->_args['child_form_id'] ) {
			return;
		}

		$parent_entry = GFAPI::get_entry( $parent_entry_id );

		$child_entry = new GPNF_Entry( $child_entry );
		$child_entry->set_parent_meta( $parent_entry['form_id'], $parent_entry['id'] );
		$child_entry->set_nested_form_field( $this->_args['nested_form_field_id'] );

	}

	public function hide_parent_entry_id_field( $form ) {
		if ( ! $this->is_applicable_child_form( $form ) || rgar( $_REQUEST, 'action' ) !== 'gpnf_refresh_markup' ) {
			return $form;
		}
		foreach ( $form['fields'] as &$field ) {
			if ( $field->id == $this->_args['parent_entry_field_id'] ) {
				$field->visibility = 'hidden';
			}
		}
		return $form;
	}

	public function is_applicable_child_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['child_form_id'] ) || (int) $form_id === (int) $this->_args['child_form_id'];
	}

}

# Configuration

new GPNF_Attach_Child_Entry_by_Field( array(
	'nested_form_field_id'  => 4,   // Update "4" to the ID of your Nested Form field on the parent form.
	'child_form_id'         => 123, // Update "123" to ID of your child form. 
	'parent_entry_field_id' => 5,   // Update "5" to the ID of the field on your child form that will contain the parent entry ID.
) );
