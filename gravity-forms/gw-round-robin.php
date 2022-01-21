<?php
/**
 * Gravity Wiz // Gravity Forms // Round Robin
 *
 * Cycle through the choices of a designated field assigning the next available choice when an entry is submitted. The
 * cycle starts with the first choice progressing to the next available choice on each submission. After each choice has
 * been assigned it will restart from the first choice.
 *
 * This functionality is useful when distributing leads evenly to a group of sales reps, scheduling shifts such that
 * employees are assigned to the next available shift, and/or balancing the responsibility of any task-oriented
 * submission (e.g. support request, job application, contest entry).
 *
 * @version  1.5
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/
 */
class GW_Round_Robin {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args(
			$args,
			array(
				'form_id'    => false,
				'field_id'   => false,
				'hide_field' => true,
			)
		);

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'hide_round_robin_field' ) );
		add_filter( 'gform_entry_post_save', array( $this, 'apply_round_robin' ), 7, 2 );

		add_filter( 'gform_email_fields_notification_admin', array( $this, 'add_round_robin_field_to_notification_email_fields' ), 10, 2 );

	}

	public function hide_round_robin_field( $form ) {

		if ( ! $this->_args['hide_field'] || ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( $field->id == $this->_args['field_id'] ) {
				$field->visibility = 'hidden';
			}
		}

		return $form;
	}

	public function apply_round_robin( $entry, $form ) {

		if ( ! $this->is_applicable_form( $entry['form_id'] ) ) {
			return $entry;
		}

		$rotation   = $this->get_rotation_values( $this->_args['field_id'], $form );
		$last_entry = $this->get_last_entry( $entry, GFAPI::get_field( $entry['form_id'], $this->_args['field_id'] ) );

		// Get the value submitted for our designated field in the last entry.
		$last_value = rgar( $last_entry, $this->_args['field_id'] );

		// Determine the next index at which to fetch our value.
		$next_index = empty( $last_value ) ? 0 : array_search( $last_value, $rotation ) + 1;
		if ( $next_index > count( $rotation ) - 1 ) {
			$next_index = 0;
		}

		// Get the next value based on our rotation.
		$next_value = $rotation[ $next_index ];

		// Update the value of our designated field in the database.
		GFAPI::update_entry_field( $entry['id'], $this->_args['field_id'], $next_value );

		// Update the value of our designated field in the $entry object that will be used to continuing processing the current submission.
		$entry[ $this->_args['field_id'] ] = $next_value;

		return $entry;
	}

	public function get_last_entry( $entry, $field ) {

		$field_filters = array();

		// GPPA integration only supports the first group of filters and filters that are field-specific (no custom values).
		if ( is_callable( 'gp_populate_anything' ) && $field->{'gppa-choices-enabled'} ) {
			$gppa_filters = rgar( $field->{'gppa-choices-filter-groups'}, 0 );
			foreach ( $gppa_filters as $gppa_filter ) {
				if ( strpos( $gppa_filter['value'], 'gf_field:' ) !== false ) {
					// Extract the field ID from the value property (e.g. `gf_field:3` → `3`).
					$bits = explode( 'gf_field:', $gppa_filter['value'] );
					$field_filters[] = array(
						'key'   => $bits[1],
						'value' => rgar( $entry, $bits[1] ),
					);
				}
			}
		}

		$last_entry = rgar(
			GFAPI::get_entries(
				$entry['form_id'],
				array(
					'status'        => 'active',
					'field_filters' => $field_filters,
				),
				array( 'direction' => 'desc' ),
				array(
					'offset'    => 1,
					'page_size' => 1,
				)
			),
			0
		);

		return $last_entry;
	}

	public function add_round_robin_field_to_notification_email_fields( $fields, $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $fields;
		}

		$field = GFAPI::get_field( $form, $this->_args['field_id'] );
		if ( is_callable( 'gp_populate_anything' ) && $field->{'gppa-choices-enabled'} ) {
			$fields[] = $field;
		}

		$rotation = $this->get_rotation_values( $this->_args['field_id'], $form );
		if ( filter_var( $rotation[0], FILTER_VALIDATE_EMAIL ) ) {
			$fields[] = $field;
		}

		return $fields;
	}

	public function get_rotation_values( $field_id, $form ) {

		$field = clone GFAPI::get_field( $form, $field_id );

		// Add support for GP Limit Choices.
		if ( is_callable( 'gp_limit_choices' ) ) {
			$field->choices = gp_limit_choices()->apply_choice_limits( $field->choices, $field, $form );
		}

		$rotation = array_filter( wp_list_pluck( $field->choices, 'value' ) );

		return $rotation;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Configuration

new GW_Round_Robin( array(
	'form_id'  => 123,
	'field_id' => 4,
) );
