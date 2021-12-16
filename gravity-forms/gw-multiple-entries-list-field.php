<?php
/**
 * Gravity Wiz // Gravity Forms // Multiple Entries by List Field
 * https://gravitywiz.com/
 *
 * Create multiple by entries based on the rows of a List field. All other field data will be duplicated for each entry.
 * List field inputs are mapped to Admin-only fields on the form.
 *
 * Plugin Name:  Gravity Forms - Multiple Entries by List Field
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Create multiple by entries based on the rows of a List field.
 * Author:       Gravity Wiz
 * Version:      0.6
 * Author URI:   https://gravitywiz.com/
 */
class GW_Multiple_Entries_List_Field {

    public function __construct( $args = array() ) {

        // set our default arguments, parse against the provided arguments, and store for use throughout the class
        $this->_args = wp_parse_args( $args, array(
            'form_id'            => false,
            'field_id'           => false,
            'field_map'          => array(),
	        'preserve_list_data' => false,
	        'append_list_data'   => false,
	        'formatter'          => function( $value, $field_id, $instance ) { return $value; }
        ) );

        // do version check in the init to make sure if GF is going to be loaded, it is already loaded
        add_action( 'init', array( $this, 'init' ) );

    }

    public function init() {

        // make sure we're running the required minimum version of Gravity Forms
        if( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
            return;
        }

        // carry on
	    add_filter( 'gform_entry_post_save',     array( $this, 'create_multiple_entries' ) );
	    add_filter( 'gform_entry_meta',          array( $this, 'register_entry_meta' ), 10, 2 );
	    add_filter( 'gform_entries_field_value', array( $this, 'display_entry_meta' ), 10, 4 );

    }

    public function create_multiple_entries( $entry ) {

    	if( ! $this->is_applicable_form( $entry['form_id'] ) ) {
    		return $entry;
	    }

    	$data = rgar( $entry, $this->_args['field_id'] );
	    if( empty( $data ) ) {
	    	return $entry;
	    }

	    $data          = maybe_unserialize( $data );
	    $working_entry = $entry;

	    if( ! $this->_args['preserve_list_data'] ) {
	    	$working_entry[ $this->_args['field_id'] ] = null;
	    }

	    foreach( $data as $index => $row ) {

	    	$row = array_values( $row );

		    foreach( $this->_args['field_map'] as $column => $field_id ) {
			    $working_entry[ (string) $field_id ] = $this->_args['formatter']( $row[ $column - 1 ], $field_id, $this );
		    }

		    // by default, original entry is updated with list field data; if append_list_data is true,
		    if( $index == 0 && ! $this->_args['append_list_data'] ) {

		    	GFAPI::update_entry( $working_entry );

			    gform_add_meta( $working_entry['id'], 'gwmelf_parent_entry', true );
			    gform_add_meta( $working_entry['id'], 'gwmelf_group_entry_id', $working_entry['id'] );

		    } else {

		    	$working_entry['id'] = null;
			    $entry_id = GFAPI::add_entry( $working_entry );

			    // group entry ID refers to the parent entry ID that created the group of entries
			    gform_add_meta( $entry_id, 'gwmelf_parent_entry', false );
			    gform_add_meta( $entry_id, 'gwmelf_group_entry_id', $entry['id'] );

		    }

	    }

	    return $entry;
    }

	public function register_entry_meta( $entry_meta, $form_id ) {

    	if( ! $this->is_applicable_form( $form_id ) ) {
    		return $entry_meta;
	    }

		$entry_meta['gwmelf_parent_entry'] = array(
			'label'             => __( 'Primary Entry' ),
			'is_numeric'        => false,
			'is_default_column' => true
		);

		$entry_meta['gwmelf_group_entry_id'] = array(
			'label'             => __( 'Group ID' ),
			'is_numeric'        => true,
			'is_default_column' => true
		);

		return $entry_meta;
	}

	public function display_entry_meta( $value, $form_id, $field_id, $entry ) {
		switch( $field_id ) {
			case 'gwmelf_parent_entry':
				$value = (bool) $value && $value !== '&#10008;' ? '&#10004;' : '&#10008;';
				break;
		}
		return $value;
	}

    public function is_applicable_form( $form ) {

        $form_id = isset( $form['id'] ) ? $form['id'] : $form;

        return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
    }

}

# Configuration

new GW_Multiple_Entries_List_Field( array(
    'form_id'   => 123,
    'field_id'  => 4,
    'field_map' => array(
	1 => 5, // column => fieldId
        2 => 6,
        3 => 7,
    ),
	'preserve_list_data' => true,
	'append_list_data' => true
) );
