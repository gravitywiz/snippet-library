<?php
/**
 * Gravity Perks // Entry Blocks // Filter values by Nested Entry.
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Instruction Video: https://www.loom.com/share/f795e8b5ef58489794dca96e83fcd230
 *
 */
class GPEB_Filter_By_Nested_Entry {

	private $parent_form_id;
	private $nested_form_id;
	private $parent_hidden_field_id;
	private $nested_target_field_id;

	public function __construct( $config = array() ) {
		$this->parent_form_id         = rgar( $config, 'parent_form_id' );
		$this->nested_form_id         = rgar( $config, 'nested_form_id' );
		$this->parent_hidden_field_id = rgar( $config, 'parent_hidden_field_id' );
		$this->nested_target_field_id = rgar( $config, 'nested_target_field_id' );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gpeb_filter_form', array( $this, 'modify_filter_form' ) );
		add_filter( 'gpeb_queryer_entries', array( $this, 'filter_entries_by_nested_value' ), 10, 2 );
	}

	private function is_applicable_form( $form_id = null ) {
		if ( $form_id === null ) {
			$form_id = rgget( 'filters_form_id' );
		}
		return $form_id == $this->parent_form_id;
	}

	public function modify_filter_form( $form ) {
		if ( ! $this->is_applicable_form( $form['id'] ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( $field->id == $this->parent_hidden_field_id ) {
				$nested_form_field = GFAPI::get_field( $this->nested_form_id, $this->nested_target_field_id );
				$field = $nested_form_field;
				$field->id = $this->parent_hidden_field_id;
			}
		}

		return $form;
	}

	public function filter_entries_by_nested_value( $entries, $gf_queryer ) {
		if ( ! $this->is_applicable_form() ) {
			return $entries;
		}

		$filters = rgget( 'filters' );
		if ( ! isset( $filters[ $this->parent_hidden_field_id ] ) ) {
			return $entries;
		}

		$nested_entries = GFAPI::get_entries( $this->nested_form_id, array(
			'field_filters' => array(
				array(
					'key'   => $this->nested_target_field_id,
					'value' => $filters[ $this->parent_hidden_field_id ],
				),
			),
		) );

		$entries = array();
		$parent_entry_ids = array();
		
		foreach ( $nested_entries as $nested_entry ) {
			$parent_entry_id = rgar( $nested_entry, 'gpnf_entry_parent' );
			$entry = GFAPI::get_entry( $parent_entry_id );
			if ( ! in_array( $parent_entry_id, $parent_entry_ids ) && $entry && ! is_wp_error( $entry ) ) {
				$parent_entry_ids[] = $parent_entry_id;
				$entries[] = $entry;
			}
		}

		return $entries;
	}
}

new GPEB_Filter_By_Nested_Entry( array(
	'parent_form_id'         => 4,
	'nested_form_id'         => 3,
	'parent_hidden_field_id' => 10,
	'nested_target_field_id' => 4,
) );
