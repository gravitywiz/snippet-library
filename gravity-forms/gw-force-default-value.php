<?php
/**
 * Gravity Wiz // Gravity Forms // Force Default Value
 * https://gravitywiz.com/
 *
 * Force the default value to be captured for fields hidden by conditional logic. Also reprocesses default values that
 * contain merge tags not available for prepopulation.
 *
 * The latter functionality is useful when wanting to combine data into specifically formatted values. For example, you
 * can use "{entry:gpaa_lat_1},{entry:gpaa_lng_1}" to format the latitude and longitude captured by GP Address Autocomplete
 * into a comma-delimited pair.
 *
 * Currently, this will only work with single input fields.
 *
 * Plugin Name:  Gravity Forms - Force Default Value
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Force the default value to be captured for fields hidden by conditional logic.
 * Author:       Gravity Wiz
 * Version:      1.2
 * Author URI:   https://gravitywiz.com/
 */
class GW_Force_Default_Value {

    public function __construct( $args = array() ) {

        // set our default arguments, parse against the provided arguments, and store for use throughout the class
        $this->_args = wp_parse_args( $args, array(
            'form_id'   => false,
            'field_ids' => array()
        ) );

        // do version check in the init to make sure if GF is going to be loaded, it is already loaded
        add_action( 'init', array( $this, 'init' ) );

    }

    public function init() {

        // make sure we're running the required minimum version of Gravity Forms
        if( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.9', '>=' ) ) {
            return;
        }

        // carry on
	    add_filter( 'gform_entry_post_save', array( $this, 'add_default_values_to_entry' ), 10, 2 );

    }

	public function add_default_values_to_entry( $entry, $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return $entry;
		}

		$requires_update = false;

		/** @var GF_Field $field */
		foreach( $form['fields'] as $field ) {

			if( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$entry_value = rgar( $entry, $field->id );

			// Get default value if field is hidden.
			if ( GFFormsModel::is_field_hidden( $form, $field, array(), $entry ) ) {
				$value = $field->get_value_default_if_empty( $field->get_value_submission( array(), false ) );
			} else {
				$value = $entry_value;
			}

			if ( rgblank( $value ) ) {
				continue;
			}

			$value = GFCommon::replace_variables( $value, $form, $entry );

			if ( $value != $entry_value ) {
				$requires_update = true;
				$entry[ $field->id ] = $value;
			}

		}

		if( $requires_update ) {
			GFAPI::update_entry( $entry );
		}

		return $entry;
	}

	function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

	function is_applicable_field( $field ) {
		return empty( $this->_args['field_ids'] ) || in_array( $field->id, $this->_args['field_ids'] );
	}

}

# Configuration

new GW_Force_Default_Value( array(
	'form_id' => 123,
	'field_ids' => array( 4, 5 )
) );
