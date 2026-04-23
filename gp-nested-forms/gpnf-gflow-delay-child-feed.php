<?php
/**
 * Gravity Perks // Nested Forms // Process Nested Entry Feed with Parent Entry Feed
 *
 * Make Nested Entry feed processing process with Parent Entry feed processing.
 * Now supports optional parameters.
 */
class GPNF_GFlow_Delay_Child_Feed {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'         => false,
			'nested_field_id' => false,
			'step_id'         => false,
			'addon_slug'      => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// addon_slug is required
		if ( empty( $this->_args['addon_slug'] ) ) {
			return;
		}

		$addon_slug = str_replace( '_', '-', $this->_args['addon_slug'] );

		// Build the correct hook
		if ( $this->_args['form_id'] && $this->_args['nested_field_id'] ) {

			$hook = sprintf(
				'gpnf_should_process_%s_feed_%s_%s',
				$addon_slug,
				$this->_args['form_id'],
				$this->_args['nested_field_id']
			);

		} else {

			// Fallback to global hook
			$hook = sprintf(
				'gpnf_should_process_%s_feed',
				$addon_slug
			);

		}

		add_filter( $hook, array( $this, 'addon_should_process' ), 9, 6 );

		add_action( 'gravityflow_step_complete', array( $this, 'handle_gravityflow_step_completion' ), 10, 4 );
		add_action( 'gform_after_submission', array( $this, 'handle_zero_total_submission' ), 10, 2 );
	}

	function addon_should_process( $should_process_feed, $feed, $context, $parent_form, $nested_form_field, $entry ) {
		// Disable immediate processing of add-on Feed for the nested entries.
		return false;
	}

	function handle_gravityflow_step_completion( $step_id, $entry_id, $form_id, $status ) {

		// Respect optional filters
		if (
			$status !== 'complete' ||
			( $this->_args['step_id'] && $step_id != $this->_args['step_id'] ) ||
			( $this->_args['form_id'] && $form_id != $this->_args['form_id'] )
		) {
			return;
		}

		// When add-on workflow step is complete on parent form.
		$entry       = GFAPI::get_entry( $entry_id );
		$parent_form = GFAPI::get_form( $form_id );

		// Process nested feeds
		$this->process_nested_feeds( $entry, $parent_form );
	}

	function handle_zero_total_submission( $entry, $form ) {

		// Only process if this is a parent form we're watching
		if ( $this->_args['form_id'] && $form['id'] != $this->_args['form_id'] ) {
			return;
		}

		// Check if total is 0 (no payment required)
		$total = GFCommon::get_order_total( $form, $entry );

		if ( $total > 0 ) {
			return; // Payment required, let normal workflow handle it
		}

		// No payment required - process nested feeds immediately
		$this->process_nested_feeds( $entry, $form );
	}

	private function process_nested_feeds( $entry, $parent_form ) {

		// Get all nested entries.
		$nested_entries = array();

		foreach ( $parent_form['fields'] as $field ) {

			if ( ! ( $field instanceof GP_Field_Nested_Form ) ) {
				continue;
			}

			// Respect optional nested field filter
			if ( $this->_args['nested_field_id'] && $field->id != $this->_args['nested_field_id'] ) {
				continue;
			}

			$_entries = explode( ',', $entry[ $field->id ] );
			$nested_entries = array_merge( $nested_entries, $_entries );
		}

		$nested_entries = array_unique( array_filter( $nested_entries ) );

		// Process each nested entry.
		foreach ( $nested_entries as $nested_entry_id ) {

			$nested_entry = GFAPI::get_entry( $nested_entry_id );

			if ( empty( $nested_entry ) || is_wp_error( $nested_entry ) ) {
				continue;
			}

			$nested_form = GFAPI::get_form( $nested_entry['form_id'] );

			if ( ! function_exists( $this->_args['addon_slug'] ) ) {
				return;
			}

			$addon_instance = $this->_args['addon_slug']();

			$addon_instance->maybe_process_feed( $nested_entry, $nested_form );

			gf_feed_processor()->save()->dispatch();
		}
	}
}

# Configuration 1
new GPNF_GFlow_Delay_Child_Feed( array(
	'form_id'         => 6, // Set this to the parent form ID
	'nested_field_id' => 4, // Set this to nested form field if (on parent form)
	'step_id'         => 6, // Set this to workflow step id on the feed (on parent form)
	'addon_slug'      => 'gp_google_sheets', // Set this to the slug of the addon we are working with (like, gp_google_sheets)
) );

# Configuration 2
new GPNF_GFlow_Delay_Child_Feed( array(
	'addon_slug' => 'gp_google_sheets',
) );
