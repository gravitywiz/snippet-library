<?php
/**
 * Gravity Wiz // Gravity Forms // Multiple Entries by List Field
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/e253325af6d24cefa20cfd2bdb44fb61
 *
 * Create multiple entries based on the rows of a List field. All other field data will be duplicated for each entry.
 * List field inputs are mapped to Admin-only fields on the form.
 *
 * Plugin Name:  Gravity Forms - Multiple Entries by List Field
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Create multiple by entries based on the rows of a List field.
 * Author:       Gravity Wiz
 * Version:      0.8
 * Author URI:   https://gravitywiz.com/
 *
 * Usage:
 *
 * 1. With `append_list_data` set to false (the default), the first row of the list data is actually appended - counter-intuitive, uh? - to the main entry via the admin-only fields and not stored as a separate entry. Subsequent rows are always stored as separate entries.
 * 2. Feeds are processed per list data row when `process_feeds` is `true`.
 * 3. Use the admin-only fields - NOT the original List field - in the field maps for feeds.
 * 4. The original List field data is also stored in the 'child' entries when `preserve_list_data` is `true`.
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
			'formatter'          => function( $value, $field_id, $instance ) {
				return $value;
			},
			'send_notifications' => false,
			'process_feeds'      => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		// We use 5 to ensure that this runs before the ones in GF core.
		add_filter( 'gform_entry_post_save', array( $this, 'create_multiple_entries' ), 5, 2 );
		add_filter( 'gform_entry_meta', array( $this, 'register_entry_meta' ), 10, 2 );
		add_filter( 'gform_entries_field_value', array( $this, 'display_entry_meta' ), 10, 4 );
		add_filter( "gform_disable_notification_{$this->_args['form_id']}", array( $this, 'maybe_disable_parent_notification' ), 10, 4 );
	}

	public function create_multiple_entries( $entry, $form ) {

		if ( ! $this->is_applicable_form( $entry['form_id'] ) ) {
			return $entry;
		}

		$data = rgar( $entry, $this->_args['field_id'] );
		if ( empty( $data ) ) {
			return $entry;
		}

		$data          = maybe_unserialize( $data );
		$working_entry = $entry;

		if ( ! $this->_args['preserve_list_data'] ) {
			$working_entry[ $this->_args['field_id'] ] = null;
		}

		foreach ( $data as $index => $row ) {

			$row = array_values( $row );

			foreach ( $this->_args['field_map'] as $column => $field_id ) {
				$working_entry[ (string) $field_id ] = $this->_args['formatter']( $row[ $column - 1 ], $field_id, $this );
			}

			if ( $index == 0 && ! $this->_args['append_list_data'] ) {

				GFAPI::update_entry( $working_entry );

				/**
				 * Sync the parent entry with our working entry so when it is passed onto other plugins using this filter,
				 * it is up-to-date and if the entry is updated via this filter (looking at you, GFPaymentAddOn::entry_post_save()),
				 * our changes will be preserved.
				 */
				$entry = $working_entry;

			} else {

				$working_entry['id'] = null;
				$entry_id            = GFAPI::add_entry( $working_entry );

				if ( $this->_args['process_feeds'] ) {
					remove_filter( 'gform_entry_post_save', array( $this, 'create_multiple_entries' ), 5 );

					gf_apply_filters( array( 'gform_entry_post_save', $form['id'] ), GFAPI::get_entry( $entry_id ), $form );

					add_filter( 'gform_entry_post_save', array( $this, 'create_multiple_entries' ), 5, 2 );
				}

				// group entry ID refers to the parent entry ID that created the group of entries
				gform_add_meta( $entry_id, 'gwmelf_parent_entry', false );
				gform_add_meta( $entry_id, 'gwmelf_group_entry_id', $entry['id'] );
			}

			// send Gravity Forms notifications, if enabled
			if ( $this->_args['send_notifications'] ) {
				GFAPI::send_notifications( $form, $working_entry );
			}
		}

		gform_add_meta( $entry['id'], 'gwmelf_parent_entry', true );
		gform_add_meta( $entry['id'], 'gwmelf_group_entry_id', $entry['id'] );

		// Update the passed entry for other filters
		// that it may be passed to afterwards.
		$entry['gwmelf_parent_entry']   = true;
		$entry['gwmelf_group_entry_id'] = $entry['id'];

		if ( $this->_args['append_list_data'] && $this->_args['process_feeds'] ) {
			/**
			 * 1st row of list data is added to the DB as a separate entry
			 * so feed shouldn't be processed for the parent, else we get
			 * e.g. (for GPGS) an extra row with empty list column values.
			 */
			add_filter( "gform_addon_pre_process_feeds_{$form['id']}", '__return_empty_array' );
		}

		return $entry;
	}

	public function maybe_disable_parent_notification( $disable, $notification, $form, $entry ) {
		// Do a check for notifications that existed (before this snippet) that might be re-triggered.
		if ( ! array_key_exists( 'gwmelf_parent_entry', $entry ) ) {
			return $disable;
		}

		if ( rgar( $entry, 'gwmelf_parent_entry' )
			&& ! $this->_args['append_list_data']
			&& $this->_args['preserve_list_data']
			&& $this->_args['send_notifications'] ) {
			/**
			 * The 'parent' & the first row notifications will have exactly the same
			 * content - unnecessary duplication. Disable the 'parent' notification.
			 */
			$disable = true;
		}

		return $disable;
	}

	public function register_entry_meta( $entry_meta, $form_id ) {

		if ( ! $this->is_applicable_form( $form_id ) ) {
			return $entry_meta;
		}

		$entry_meta['gwmelf_parent_entry'] = array(
			'label'             => __( 'Primary Entry' ),
			'is_numeric'        => false,
			'is_default_column' => true,
		);

		$entry_meta['gwmelf_group_entry_id'] = array(
			'label'             => __( 'Group ID' ),
			'is_numeric'        => true,
			'is_default_column' => true,
		);

		return $entry_meta;
	}

	public function display_entry_meta( $value, $form_id, $field_id, $entry ) {
		switch ( $field_id ) {
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
	'form_id'            => 123,
	'field_id'           => 4,
	'field_map'          => array(
		1 => 5, // column => fieldId
		2 => 6,
		3 => 7,
	),
	'preserve_list_data' => true,
	'append_list_data'   => true,
	'send_notifications' => false,
) );
