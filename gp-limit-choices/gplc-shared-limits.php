<?php
/**
 * Gravity Perks // GP Limit Choices // Share Limits Across Multiple Fields (and Forms)
 *
 * NOTE: This snippet only works with Gravity Forms 2.3 or greater.
 *
 * Adds support for specifying groups of fields which share the same limit. For example, if you had two fields with a
 * limit of 10, selections from both fields would contribute to that limit.
 *
 * @version   2.1
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/
 */
class GPLS_Shared_Limits {

	public $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'      => false,
			'field_groups' => array(),
			'form_groups'  => array()
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if( ! is_callable( array( 'GFFormsModel', 'get_database_version' ) ) ) {
			return;
		}

		add_filter( 'gwlc_choice_counts_query', array( $this, 'modify_choice_counts_query' ), 10, 2 );
		add_filter( 'gplc_requested_count', array( $this, 'modify_requested_count' ), 10, 2 );

	}

	public function modify_choice_counts_query( $query, $field ) {

		if( ! $this->is_applicable_form( $field->formId ) ) {
			return $query;
		}

		if( ! empty( $this->_args['form_groups'] ) ) {
			$query = $this->modify_query_for_form_groups( $query, $field );
		}

		if( ! empty( $this->_args['field_groups'] ) ) {
			$query = $this->modify_query_for_field_groups( $query, $field );
		}

		return $query;
	}

	public function modify_query_for_field_groups( $query, $field ) {
		global $wpdb;

		$from_search = $wpdb->prepare( "(em.meta_key = %s OR em.meta_key LIKE %s)", $field['id'], $wpdb->esc_like( $field['id'] ) . '.%' );
		$from_replace = array();

		foreach( $this->_args['field_groups'] as $field_group ) {

			if( ! in_array( $field['id'], $field_group ) ) {
				continue;
			}

			foreach( $field_group as $field_id ) {
				$from_replace[] = $wpdb->prepare( "em.meta_key = %s OR em.meta_key LIKE %s", $field_id, $wpdb->esc_like( $field_id ) . '.%' );
			}

		}

		if( ! empty( $from_replace ) ) {
			$from_replace = sprintf( '( %s )', implode( ' OR ', $from_replace ) );
			$query['from'] = str_replace( $from_search, $from_replace, $query['from'] );
		}

		return $query;
	}

	public function modify_query_for_form_groups( $query, $field ) {
		global $wpdb;

		foreach( $this->_args['form_groups'] as $form_group ) {

			if( ! $this->is_form_group_field( $field, $form_group ) ) {
				continue;
			}

			$query['where'] = str_replace( sprintf( 'AND em.form_id = %d', $field->formId ), '', $query['where'] );

			$join_conditions = array();
			foreach( $form_group as $form_id => $field_ids ) {
				$field_clauses = array();
				if ( ! is_array( $field_ids ) ) {
					$field_ids = array( $field_ids );
				}
				foreach ( $field_ids as $field_id ) {
					$field_clauses[] = "em.meta_key = '{$field_id}' OR em.meta_key LIKE '{$field_id}%'";
				}
				$join_conditions[] = "( em.form_id = {$form_id} AND ( " . implode( " OR ", $field_clauses ) . " ) )";
			}

			$query['from'] = sprintf( "FROM {$wpdb->prefix}gf_entry e INNER JOIN {$wpdb->prefix}gf_entry_meta em ON em.entry_id = e.id AND ( %s )", implode( "\nOR\n", $join_conditions ) );

		}

		return $query;
	}

	public function modify_requested_count( $requested_count, $field ) {

		if( ! $this->is_applicable_form( $field['formId'] ) ) {
			return $requested_count;
		}

		foreach( $this->_args['field_groups'] as $field_group ) {

			if( ! in_array( $field->id, $field_group ) ) {
				continue;
			}

			$selected_choices       = gp_limit_choices()->get_selected_choices( $field );
			$primary_choice         = reset( $selected_choices );
			$shared_requested_count = 0;

			remove_filter( 'gplc_requested_count', array( $this, 'modify_requested_count' ) );

			foreach( $field_group as $field_id ) {

				$form        = GFAPI::get_form( $field->formId );
				$group_field = GFFormsModel::get_field( $form, $field_id );

				$selected_choices = gp_limit_choices()->get_selected_choices( $group_field );
				$selected_choice  = reset( $selected_choices );

				if( $selected_choice['value'] == $primary_choice['value'] ) {
					$shared_requested_count += gp_limit_choices()->get_requested_count( $group_field );
				}

			}

			add_filter( 'gplc_requested_count', array( $this, 'modify_requested_count' ), 10, 2 );

			break;

		}

		return isset( $shared_requested_count ) ? intval( $shared_requested_count ) : $requested_count;
	}

	public function is_applicable_form( $form ) {

		$form_id            = is_array( $form ) ? $form['id'] : $form;
		$is_applicable_form = false;

		if ( ! empty( $this->_args['form_groups'] ) ) {
			foreach( $this->_args['form_groups'] as $form_group ) {
				$is_applicable_form = $this->is_form_group_form( $form, $form_group );
				if( $is_applicable_form ) {
					break;
				}
			}
		} else {
			$is_applicable_form = $form_id == $this->_args['form_id'];
		}

		return $is_applicable_form;
	}

	public function is_form_group_form( $form, $form_group ) {
		$form_id = is_array( $form ) ? $form['id'] : $form;
		return in_array( $form_id, array_keys( $form_group ) );
	}

	public function is_form_group_field( $field, $form_group ) {
		return $this->is_form_group_form( $field->formId, $form_group ) && in_array( $field->id, $this->get_form_group_fields( $form_group ) );
	}

	public function get_form_group_fields( $form_group ) {
		$form_group_fields = array();
		foreach( $form_group as $form_id => $field_ids ) {
			if ( ! is_array( $field_ids ) ) {
				$field_ids = array( $field_ids );
			}
			$form_group_fields = array_merge( $form_group_fields, $field_ids );
		}
		return $form_group_fields;
	}

}

# Configuration

new GPLS_Shared_Limits( array(
	'form_id' => 123,
	'field_groups' => array(
		array( 1, 2 ),
	),
) );

/**
 * Share fields across multiple forms by using the `form_groups` parameter and an array for each form group with the
 * key of the form ID and the value of the field ID on that form to share limits.
 *
 * Note: This method cannot be used to share limits for fields on the same form.
 */
new GPLS_Shared_Limits( array(
	'form_groups' => array(
		array(
			123 => 3,
			124 => 4
		),
		array(
			123 => 5,
			124 => 6
		),
	)
) );

/**
 * Share fields across the same *and* other forms by using the `form_groups` parameter and passing each form's fields
 * as an array.
 */
new GPLS_Shared_Limits( array(
	'form_groups' => array(
		array(
			123 => array( 1, 2 ),
			124 => array( 3, 4 )
		),
	),
) );