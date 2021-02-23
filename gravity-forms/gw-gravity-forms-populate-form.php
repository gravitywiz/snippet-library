<?php
/**
 * Gravity Wiz // Gravity Forms // Populate Form with Entry (Optionally Update Entry on Submission)
 *
 * Pass an entry ID and populate the form automatically. No form configuration required. Optionally update the entry on
 * submission.
 *
 * @version  1.5
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/
 * @see      https://www.screencast.com/t/SdNyfbNyC5C
 *
 * Plugin Name:  Gravity Forms - Populate Form w/ Entry
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Pass an entry ID and populate the form automatically. No form configuration required. Optionally update the entry on submission.
 * Author:       Gravity Wiz
 * Version:      1.4
 * Author URI:   http://gravitywiz.com
 */
class GW_Populate_Form {

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'   => false,
			'query_arg' => 'eid',
			'update'    => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		add_filter( 'gform_form_args', array( $this, 'prepare_field_values_for_population' ) );
		add_filter( 'gform_entry_id_pre_save_lead', array( $this, 'set_submission_entry_id' ), 10, 2 );

	}

	public function prepare_field_values_for_population( $args ) {

		if ( ! $this->is_applicable_form( $args['form_id'] ) || ! $this->get_entry_id() ) {
			return $args;
		}

		$entry = GFAPI::get_entry( $this->get_entry_id() );
		if ( is_wp_error( $entry ) ) {
			return $args;
		}

		$args['field_values'] = $this->prepare_entry_for_population( $entry );

		add_filter( sprintf( 'gform_pre_render_%d', $args['form_id'] ), array( $this, 'prepare_form_for_population' ) );

		return $args;
	}

	public function prepare_form_for_population( $form ) {

		foreach ( $form['fields'] as &$field ) {

			$field['allowsPrepopulate'] = true;

			if ( is_array( $field['inputs'] ) ) {
				$inputs = $field['inputs'];
				foreach ( $inputs as &$input ) {
					$input['name'] = (string) $input['id'];
				}
				$field['inputs'] = $inputs;
			}

			$field['inputName'] = $field['id'];

		}

		return $form;
	}

	public function prepare_entry_for_population( $entry ) {

		$form = GFFormsModel::get_form_meta( $entry['form_id'] );

		foreach ( $form['fields'] as $field ) {

			if ( $field->type == 'post_category' ) {
				$value               = explode( ':', $entry[ $field->id ] );
				$entry[ $field->id ] = $value[1];
			}

			switch ( GFFormsModel::get_input_type( $field ) ) {
				case 'checkbox':
					$values = $this->get_field_values_from_entry( $field, $entry );
					if ( is_array( $values ) ) {
						$value = implode( ',', array_filter( $values ) );
					} else {
						$value = $values;
					}
					$entry[ $field['id'] ] = $value;

					break;

				case 'list':
					$value       = maybe_unserialize( rgar( $entry, $field->id ) );
					$list_values = array();

					if ( is_array( $value ) ) {
						foreach ( $value as $vals ) {
							if ( is_array( $vals ) ) {
								$vals = implode( '|', array_map( function( $value ) {
									$value = str_replace( ',', '&#44;', $value );
									return $value;
								}, $vals ) );
							}
							array_push( $list_values, $vals );
						}
						$entry[ $field->id ] = implode( ',', $list_values );
					}

					break;

			}
		}

		return $entry;
	}

	public function get_field_values_from_entry( $field, $entry ) {

		$values = array();

		foreach ( $entry as $input_id => $value ) {
			$fid = intval( $input_id );
			if ( $fid == $field['id'] ) {
				$values[] = $value;
			}
		}

		return count( $values ) <= 1 ? $values[0] : $values;
	}

	public function set_submission_entry_id( $entry_id, $form ) {

		if ( ! $this->_args['update'] || ! $this->is_applicable_form( $form['id'] ) || ! $this->get_entry_id() ) {
			return $entry_id;
		}

		add_filter( 'gform_use_post_value_for_conditional_logic_save_entry', '__return_true' );

		return $this->get_entry_id();
	}

	public function get_entry_id() {
		return rgget( $this->_args['query_arg'] );
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return ! $this->_args['form_id'] || $form_id == $this->_args['form_id'];
	}

}

# Configuration

new GW_Populate_Form( array(
	'update' => true,
) );
