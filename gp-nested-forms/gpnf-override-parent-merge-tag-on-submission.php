<?php
/**
 * Gravity Perks // Nested Forms // Force {Parent} Merge Tag Replacement on Submission
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/1ccbaa94d6b94f0f97829916a8396ac7
 *
 * Override all {Parent} merge tags when the parent form is submitted or a parent entry is updated.
 */

class GPNF_Override_Parent_Merge_Tags {

	/**
	 * @var int|null child form ID to apply the override to. If null, override will apply to all child forms.
	 */
	private $child_form_id = null;

	/**
	 * @var int[] array field ID's to exclude when overriding a child form's {Parent} merge tags.
	 */
	private $exclude_field_ids = array();

	/**
	 * @var int[] array field ID's to include when overriding a child form's {Parent} merge tags. If included, will override any usage of $exclude_field_ids.
	 */
	private $include_field_ids = array();

	function __construct( $config = null ) {
		$this->set_config_data( $config );
		$this->init_hooks();
	}

	function set_config_data( $config ) {
		if ( empty( $config ) ) {
			return;
		}

		$this->child_form_id = $config['child_form_id'];

		if ( isset( $config['exclude_field_ids'] ) ) {
			$this->exclude_field_ids = $config['exclude_field_ids'];
		}

		if ( isset( $config['include_field_ids'] ) ) {
			$this->include_field_ids = $config['include_field_ids'];
		}
	}

	function init_hooks() {
		add_filter( 'gform_entry_post_save', array( $this, 'override_parent_merge_tags' ), 11, 2 );

		add_action( 'gform_after_update_entry', function ( $form, $entry_id ) {
			$entry = GFAPI::get_entry( $entry_id );
			$this->override_parent_merge_tags( $entry, $form );
		}, 11, 2 );

		add_filter( 'gravityview-inline-edit/entry-updated', function( $return, $entry, $form_id ) {
			$this->override_parent_merge_tags( $entry, GFAPI::get_form( $form_id ) );
			return $return;
		}, 10, 3 );
	}

	function override_parent_merge_tags( $entry, $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field->get_input_type() !== 'form' ) {
				continue;
			}

			if ( $this->child_form_id !== null && $field->gpnfForm != $this->child_form_id ) {
				continue;
			}

			$child_form = GFAPI::get_form( $field->gpnfForm );

			foreach ( $child_form['fields'] as $child_field ) {
				/**
				 * note: if include_field_ids exists, we should ignore exclude_field_ids
				 */
				$should_include = in_array( $child_field->id, $this->include_field_ids );
				$should_exclude = in_array( $child_field->id, $this->exclude_field_ids );

				if ( empty( $this->include_field_ids ) && $should_exclude ) {
					continue;
				}

				if ( ! $should_include ) {
					continue;
				}

				if ( $child_field->get_entry_inputs() ) {
					$inputs = $child_field->get_entry_inputs();
				} else {
					$inputs = array(
						array(
							'id'           => $child_field->id,
							'defaultValue' => $child_field->defaultValue,
						),
					);
				}

				foreach ( $inputs as $input ) {
					$this->override_child_entry_input_value( $entry, $field, $input['id'], rgar( $input, 'defaultValue' ) );
				}
			}
		}

		return $entry;
	}

	function override_child_entry_input_value( $entry, $field, $input_id, $default_value ) {

		preg_match_all( '/{Parent:(\d+(\.\d+)?)[^}]*}/i', $default_value, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) ) {
			return;
		}

		$value = $default_value;
		foreach ( $matches as $match ) {
			$value = str_replace( $match[0], rgar( $entry, $match[1] ), $value );
		}

		$child_entry_ids = explode( ',', rgar( $entry, $field->id ) );
		foreach ( $child_entry_ids as $child_entry_id ) {
			GFAPI::update_entry_field( $child_entry_id, $input_id, $value );
		}

	}
}

# -------------------------------------------------
# Configuration Options:
#
# child_form_id      (int)   optional id of child form to apply this to. If excluded, then this will apply to all child forms
# exclude_field_ids  (array) optional array of field IDs to exclude from the override.
# included_field_ids (array) optional array of field IDs to apply this to. If included, then exclude_field_ids will be ignored.
# -------------------------------------------------

# Example - apply to all fields of a specific child form
// new GPNF_Override_Parent_Merge_Tags( array(
// 	'child_form_id' => 1,
// ) );

# Example - apply to all fields, except those in exclude_field_ids, of a specific child form
// new GPNF_Override_Parent_Merge_Tags( array(
// 	'child_form_id' => 1,
// 	'exclude_field_ids' => array( 2, 3, 4 ), // optional fields to skip the override on
// ) );

# Example - apply to specific child and apply only to specified fields
// new GPNF_Override_Parent_Merge_Tags( array(
// 	'child_form_id'     => 1,
// 	'include_field_ids' => array( 2 ), // optional fields to skip the override on
// ) );

# Example - apply to all fields in all child forms
// new GPNF_Override_Parent_Merge_Tags();
