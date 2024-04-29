<?php
/**
 * Gravity Perks // Easy Passthrough // Edit Entry
 * https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/
 *
 * Edit the entry that was passed through via GP Easy Passthrough rather than creating a new entry.
 *
 * Plugin Name:  GP Easy Passthrough — Edit Entry
 * Plugin URI:   https://gravitywiz.com/edit-gravity-forms-entries-on-the-front-end/
 * Description:  Edit the entry that was passed through via GP Easy Passthrough rather than creating a new entry.
 * Author:       Gravity Wiz
 * Version:      1.4.3
 * Author URI:   https://gravitywiz.com/
 */
class GPEP_Edit_Entry {

	private $form_id;
	private $delete_partial;
	private $passed_through_entries;

	public function __construct( $options ) {

		if ( ! function_exists( 'rgar' ) ) {
			return;
		}

		$this->form_id        = rgar( $options, 'form_id' );
		$this->delete_partial = rgar( $options, 'delete_partial', true );
		$this->refresh_token  = rgar( $options, 'refresh_token', false );
		$this->process_feeds  = rgar( $options, 'process_feeds', false );

		add_filter( "gpep_form_{$this->form_id}", array( $this, 'capture_passed_through_entry_ids' ), 10, 3 );
		add_filter( "gform_entry_id_pre_save_lead_{$this->form_id}", array( $this, 'update_entry_id' ), 10, 2 );
		add_filter( "gform_entry_post_save_{$this->form_id}", array( $this, 'delete_values_for_conditionally_hidden_fields' ), 10, 2 );

		// Enable edit view in GP Inventory.
		add_filter( "gpi_is_edit_view_{$this->form_id}", '__return_true' );

		// Bypass limit submissions on validation
		add_filter( 'gform_validation', array( $this, 'bypass_limit_submission_validation' ) );

		add_filter( "gpi_query_{$this->form_id}", array( $this, 'exclude_edit_entry_from_inventory' ), 10, 2 );

		// If we need to reprocess any feeds on 'edit'.
		add_filter( 'gform_entry_post_save', array( $this, 'process_feeds' ), 10, 2 );
	}

	public function capture_passed_through_entry_ids( $form, $values, $passed_through_entries ) {

		// Save a runtime cache for use when releasing inventory reserved by the entry being edited.
		$this->passed_through_entries = $passed_through_entries;

		if ( empty( $passed_through_entries ) ) {
			return $form;
		}

		// Add hidden input to capture entry IDs passed through via GPEP.

		add_filter( "gform_form_tag_{$form['id']}", function( $form_tag, $form ) use ( $passed_through_entries ) {
			$entry_ids = implode( ',', wp_list_pluck( $passed_through_entries, 'entry_id' ) );
			$hash      = wp_hash( $entry_ids );
			$value     = sprintf( '%s|%s', $entry_ids, $hash );
			$input     = sprintf( '<input type="hidden" name="%s" value="%s">', $this->get_passed_through_entries_input_name( $form['id'] ), $value );
			$form_tag .= $input;
			return $form_tag;
		}, 10, 2 );

		add_filter( "gpls_rule_groups_{$this->form_id}", function( $rule_groups, $form_id ) use ( $passed_through_entries ) {
			// Bypass GPLS if we're updating an entry.
			if ( ! empty( $passed_through_entries ) ) {
				$rule_groups = array();
			}

			return $rule_groups;
		}, 10, 2 );

		return $form;
	}

	public function bypass_limit_submission_validation( $validation_result ) {
		$edit_entry_id = $this->get_edit_entry_id( rgars( $validation_result, 'form/id' ) );

		if ( ! $edit_entry_id ) {
			return $validation_result;
		}

		add_filter( "gpls_rule_groups_{$this->form_id}", function( $rule_groups, $form_id ) use ( $edit_entry_id ) {
			return array();
		}, 10, 2 );

		return $validation_result;
	}

	public function update_entry_id( $entry_id, $form ) {

		$update_entry_id = $this->get_edit_entry_id( $form['id'] );
		if ( $update_entry_id ) {
			if ( $this->delete_partial
				&& is_callable( array( 'GF_Partial_Entries', 'get_instance' ) )
				&& $entry_id !== null
				&& ! empty( GF_Partial_Entries::get_instance()->get_active_feeds( $form['id'] ) )
			) {
				GFAPI::delete_entry( $entry_id );
			}
			if ( $this->refresh_token ) {
				gform_delete_meta( $update_entry_id, 'fg_easypassthrough_token' );
				gp_easy_passthrough()->get_entry_token( $update_entry_id );
				// Remove entry from the session and prevent Easy Passthrough from resaving it.
				$session = gp_easy_passthrough()->session_manager();
				$session[ gp_easy_passthrough()->get_slug() . '_' . $form['id'] ] = null;
				remove_action( 'gform_after_submission', array( gp_easy_passthrough(), 'store_entry_id' ) );
			}
			return $update_entry_id;
		}

		return $entry_id;
	}

	/**
	 * Delete values that exist for the entry in the database for fields that are now conditionally hidden.
	 *
	 * If we find any instance where a conditionally hidden field has a value, we'll update the DB with the passed entry,
	 * which was just submitted and will not contain conditionally hidden values.
	 *
	 * Note: There's a good case for us to simply call GFAPI::update_entry() with the passed entry without all the other
	 * fancy logic to that only makes the call if it identifies a conditionally hidden field with a DB value. A thought
	 * for future us.
	 *
	 * @param $entry
	 * @param $form
	 *
	 * @return mixed
	 */
	public function delete_values_for_conditionally_hidden_fields( $entry, $form ) {

		// We'll only update the entry if we identify a field value that needs to be deleted.
		$has_change = false;

		// The passed entry does not reflect what is actually in the database.
		$db_entry = null;

		/**
		 * @var \GF_Field $field
		 */
		foreach ( $form['fields'] as $field ) {

			if ( ! GFFormsModel::is_field_hidden( $form, $field, array(), $entry ) ) {
				continue;
			}

			if ( ! $db_entry ) {
				$db_entry = GFAPI::get_entry( $entry['id'] );
			}

			$inputs = $field->get_entry_inputs();
			if ( ! $inputs ) {
				$inputs = array(
					array(
						'id' => $field->id,
					),
				);
			}

			foreach ( $inputs as $input ) {
				if ( ! empty( $db_entry[ $input['id'] ] ) ) {
					$has_change = true;
					break 2;
				}
			}
		}

		if ( $has_change ) {
			GFAPI::update_entry( $entry );
		}

		return $entry;
	}

	public function get_passed_through_entries_input_name( $form_id ) {
		return "gpepee_passed_through_entries_{$form_id}";
	}

	public function get_passed_through_entry_ids( $form_id ) {

		$entry_ids = array();

		if ( ! empty( $_POST ) ) {

			$posted_value = rgpost( $this->get_passed_through_entries_input_name( $form_id ) );
			if ( empty( $posted_value ) ) {
				return $entry_ids;
			}

			list( $entry_ids, $hash ) = explode( '|', $posted_value );
			if ( $hash !== wp_hash( $entry_ids ) ) {
				return $entry_ids;
			}

			$entry_ids = explode( ',', $entry_ids );

		} elseif ( ! empty( $this->passed_through_entries ) ) {

			$entry_ids = wp_list_pluck( $this->passed_through_entries, 'entry_id' );

		}

		return $entry_ids;
	}

	public function get_edit_entry_id( $form_id ) {

		$entry_ids = $this->get_passed_through_entry_ids( $form_id );
		$entry_id  = array_shift( $entry_ids );

		/**
		 * Filter the ID that will be used to fetch assign the entry to be edited.
		 *
		 * @since 1.3
		 *
		 * @param int|bool $edit_entry_id The ID of the entry to be edited.
		 * @param int      $form_id       The ID of the form that was submitted.
		 */
		return gf_apply_filters( array( 'gpepee_edit_entry_id', $form_id ), $entry_id, $form_id );
	}

	/**
	 * Exclude the entry being edited in GravityView from inventory counts.
	 *
	 * Without this, you can't reselect choices that the current entry has consumed.
	 */
	public function exclude_edit_entry_from_inventory( $query, $field ) {
		global $wpdb;

		$entry_ids = $this->get_passed_through_entry_ids( $field->formId );

		// @todo Update to work with multiple passed through entries.
		$current_entry_id = array_pop( $entry_ids );
		if ( ! $current_entry_id ) {
			return $query;
		}

		$query['where'] .= $wpdb->prepare( "\nAND em.entry_id != %d", $current_entry_id );

		return $query;
	}

	public function process_feeds( $entry, $form ) {
		if ( ! $this->process_feeds ) {
			return $entry;
		}

		/**
		 * Disable asynchronous feed process on edit otherwise async feeds will not be re-ran due to a check in
		 * class-gf-feed-processor.php that checks `gform_get_meta( $entry_id, 'processed_feeds' )` and there isn't
		 * a way to bypass it.
		 */
		$filter_priority = rand( 100000, 999999 );
		add_filter( 'gform_is_feed_asynchronous', '__return_false', $filter_priority );

		foreach ( GFAddOn::get_registered_addons( true ) as $addon ) {
			if ( method_exists( $addon, 'maybe_process_feed' ) && ( $this->process_feeds === true || strpos( $this->process_feeds, $addon->get_slug() ) !== false ) ) {
				$addon->maybe_process_feed( $entry, $form );
			}
		}

		remove_filter( 'gform_is_feed_asynchronous', '__return_false', $filter_priority );
		return $entry;
	}

}

// Configurations
new GPEP_Edit_Entry( array(
	'form_id'        => 123,   // Set this to the form ID.
	'delete_partial' => false, // Set this to false if you wish to preserve partial entries after an edit is submitted.
	'refresh_token'  => false,  // Set this to true to generate a fresh Easy Passthrough token after updating an entry.
	'process_feeds'  => false,  // Set this to true to process all feed addons on Edit Entry, or provide a comma separated list of addon slugs like 'gravityformsuserregistration', etc.
) );
