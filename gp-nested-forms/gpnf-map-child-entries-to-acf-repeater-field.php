<?php
/**
 * Gravity Perks // GP Nested Forms // Pass Nested Form Entries to ACF Repeater Field when using GF APC Add-on
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */

class GPNF_Map_Child_Entries_To_ACF_Repeater {

	private $args;
	private $_args = array();

	public function __construct( $args ) {
		$this->_args = wp_parse_args( $args, array(
			'form_id'                 => false,
			'nested_form_field_id'    => false,
			'field_map'               => array(),
			'acf_repeater_field_name' => false,
		) );

		add_action( 'gform_advancedpostcreation_post_after_creation', array( $this, 'gw_child_entries_to_repeater' ), 10, 2 );
	}

	function gw_child_entries_to_repeater( $post_id, $feed, $entry, $form ) {

		if ( $form['id'] !== $this->_args['form_id'] ) {
			return;
		}

		$parent_entry  = new GPNF_Entry( $entry );
		$child_entries = $parent_entry->get_child_entries( $this->_args['nested_form_field_id'] );
		$repeat_value  = array();
		$created_posts = gform_get_meta( $entry['id'], 'gravityformsadvancedpostcreation_post_id' );
		foreach ( $created_posts as $post ) {
			$post_id = $post['post_id'];
			foreach ( $child_entries as $child_entry ) {
				$value = array();
				foreach ( $this->_args['field_map'] as $acf_field_name => $child_entry_field_id ) {
					$value[ $acf_field_name ] = rgar( $child_entry, $child_entry_field_id );
				}
				array_push( $repeat_value, $value );
			}
			update_field( $this->_args['acf_repeater_field_name'], $repeat_value, $post_id );
		}
	}
}

new GPNF_Map_Child_Entries_To_ACF_Repeater( array(
	'form_id'                 => 7, // Set this to the parent form ID
	'nested_form_field_id'    => 18, // Update to the ID of the Nested Form field.
	'field_map'               => array(
		'num_comedien'  => 1,
		'role_comedien' => 3,
	), // The field map contains "field_name" => "child_entry_field_id" pairs. The field name is the name of the fields in
	// the ACF Repeater field. The child entry field ID is the field ID from the child form.
	'acf_repeater_field_name' => 'comediens', // Update to the field name of the ACF repeater field.
) );
