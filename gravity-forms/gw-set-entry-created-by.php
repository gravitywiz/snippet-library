<?php
/**
 * Gravity Wiz // Gravity Forms // Set Entry Creator by Field Value
 * https://gravitywiz.com
 *
 * Use in combination with [Populate Anything](https://gravitywiz.com/documentation/gravity-forms-populate-anything/) to
 * populate a Drop Down field (or any other single value field type) with WordPress users and then set the selected user
 * as the creator of the entry.
 *
 * Plugin Name:  Gravity Forms - Set Entry Creator by Field Value
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Set the entry creator by field value. The value must be a valid WordPress user ID.
 * Author:       Gravity Wiz
 * Version:      1.1
 * Author URI:   http://gravitywiz.com
 */
class GW_Set_Entry_Created_By {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_entry_post_save', array( $this, 'update_created_by' ) );

	}

	public function update_created_by( $entry ) {

		if ( $entry['form_id'] != $this->_args['form_id'] ) {
			return $entry;
		}

		$field_value = rgar( $entry, $this->_args['field_id'] );
		if ( ! $field_value ) {
			return $entry;
		}

		// set the property in the DB
		GFAPI::update_entry_property( $entry['id'], 'created_by', $field_value );

		// update the property in the current $entry object that will be used for the rest of the submission process
		$entry['created_by'] = $field_value;

		return $entry;
	}

}

# Configuration

new GW_Set_Entry_Created_By( array(
	'form_id'  => 123, // update to the ID of your form
	'field_id' => 1,    // update to the ID of the field whose value should be used for the "created_by" entry property
) );
