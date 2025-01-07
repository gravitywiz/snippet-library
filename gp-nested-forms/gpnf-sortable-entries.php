<?php
/**
 * Gravity Perks // Nested Forms // Sortable Entries
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Experimental Snippet 🧪
 *
 * Instructions:
 *
 * 1. Install this code as a plugin or as a snippet (https://gravitywiz.com/documentation/how-do-i-install-a-snippet/)
 * 2. Customize the configuration as needed at the bottom of this file. By default, this snippet will enable sorting
 *   for all Nested Form fields on all forms.
 *
 * Plugin Name:  GP Nested Forms - Sortable Entries
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * Description:  Enable sorting for Nested Form entries.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */

class GPNF_Sortable_Entries {

	/**
	 * @var array{
	 *    form_id?: int,
	 *    field_id?: int|int[]
	 * } $_args
	 */
	private $_args = array();

	/**
	 * @param array{
	 *     form_id?: int,
	 *     field_id?: int|int[]
	 * } $args
	 */
	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	/**
	 * @return void
	 */
	public function init() {

		// time for hooks
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
		add_filter( 'gpnf_should_use_static_value', array( $this, 'use_static_value' ), 10, 3 );
		add_action( 'wp_ajax_gpnf_update_entry_order', array( $this, 'update_entry_order' ) );
		add_action( 'wp_ajax_no_priv_gpnf_update_entry_order', array( $this, 'update_entry_order' ) );
		add_filter( 'gpnf_init_script_args', array( $this, 'add_init_script_args' ), 10, 3 );

	}

	/**
	 * Filter the init script to sort the entries based on what's set in the cookie.
	 *
	 * @param array $args {
	 *
	 *     @var int    $formId              The current form ID.
	 *     @var int    $fieldId             The field ID of the Nested Form field.
	 *     @var int    $nestedFormId        The form ID of the nested form.
	 *     @var string $modalTitle          The title to be displayed in the modal header.
	 *     @var string $editModalTitle      The title to be displayed in the modal header when editing an existing entry.
	 *     @var array  $displayFields       The fields which will be displayed in the Nested Forms entries view.
	 *     @var array  $entries             An array of modified entries, including only their display values.
	 *     @var string $ajaxUrl             The URL to which AJAX requests will be posted.
	 *     @var int    $modalWidth          The default width of the modal; defaults to 700.
	 *     @var mixed  $modalHeight         The default height of the modal; defaults to 'auto' which will automatically size the modal based on its contents.
	 *     @var string $modalClass          The class that will be attached to the modal for styling.
	 *     @var string $modalHeaderColor    A HEX color that will be set as the default background color of the modal header.
	 *     @var bool   $hasConditionalLogic Indicate whether the current form has conditional logic enabled.
	 *     @var bool   $hasConditionalLogic Indicate whether the current form has conditional logic enabled.
	 *     @var bool   $enableFocusTrap     Whether the nested form should use a focus trap when open to prevent tabbing outside the nested form.
	 *
	 * }
	 * @param GF_Field $field The current Nested Form field.
	 * @param array    $form  The current form.
	 */
	public function add_init_script_args( $args, $field, $form ) {
		if ( ! $this->is_applicable_form( $form ) || ! $this->is_applicable_field( $field->id ) ) {
			return $args;
		}

		$session     = new GPNF_Session( $form['id'] );
		$cookie_name = $session->get_cookie_name();

		$cookie_raw = rgar( $_COOKIE, $cookie_name );

		if ( ! $cookie_raw ) {
			return $args;
		}

		$cookie         = json_decode( stripslashes( $_COOKIE[ $cookie_name ] ), true );
		$cookie_entries = rgars( $cookie, 'nested_entries/' . $field->id );

		if ( empty( $cookie_entries ) ) {
			return $args;
		}

		// Sort $args['entries'] which contains Gravity Forms entries arrays. They contain an 'id' key.
		// $cookie_entries contains the entry IDs in the order they should be displayed.
		$sorted_entries = array();

		foreach ( $cookie_entries as $entry_id ) {
			foreach ( $args['entries'] as $entry ) {
				if ( $entry['id'] == $entry_id ) {
					$sorted_entries[] = $entry;
					break;
				}
			}
		}

		$args['entries'] = $sorted_entries;

		return $args;
	}

	/**
	 * Handles updating the entry order in the GPNF session after a sort event.
	 *
	 * @return void
	 */
	public function update_entry_order() {
		check_ajax_referer( 'gpnf_refresh_markup', 'nonce' );

		$form_id   = rgpost( 'formId' );
		$field_id  = rgpost( 'fieldId' );
		$entry_ids = rgpost( 'entryIds' );

		$session     = new GPNF_Session( $form_id );
		$cookie_name = $session->get_cookie_name();

		// Most of GPNF_Session is private so we need to modify the cookie directly.
		$cookie = json_decode( stripslashes( $_COOKIE[ $cookie_name ] ), true );

		// If 'nested_entries' is not set for the field, just die.
		if ( ! isset( $cookie['nested_entries'][ $field_id ] ) ) {
			die();
		}

		// Update the nested_entries array with the new order.
		$cookie['nested_entries'][ $field_id ] = $entry_ids;

		// Update the cookie.
		setcookie( $cookie_name, json_encode( $cookie ), 0, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );

		die();
	}

	/**
	 * Use static values if sorting is enabled, otherwise a GF_Query will be used to get the child entries and
	 * the sorting will not be applied.
	 *
	 * @param bool                  $should_use_static_value Should the field's value be static?
	 * @param \GP_Field_Nested_Form $field                   The current Nested Form field.
	 * @param array                 $entry                   The current entry.
	 */
	public function use_static_value( $should_use_static_value, $field, $entry ) {
		if ( ! $this->is_applicable_form( $field->formId ) || ! $this->is_applicable_field( $field->id ) ) {
			return $should_use_static_value;
		}

		return true;
	}

	/**
	 * @param array $form
	 * @param bool  $is_ajax_enabled
	 *
	 * @return array
	 */
	public function load_form_script( $form, $is_ajax_enabled ) {
		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	/**
	 * @return void
	 */
	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.<?php echo __CLASS__; ?> = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.isApplicableField = function(fieldId) {
						if ( typeof fieldId !== 'number' ) {
							fieldId = parseInt( fieldId );
						}

						if ( ! self.fieldId ) {
							return true;
						}

						if ( typeof self.fieldId !== 'object' ) {
							self.fieldId = [ self.fieldId ];
						}

						// Ensure fieldIds are all numbers
						self.fieldId = self.fieldId.map( function( fieldId ) {
							if ( typeof fieldId === 'string' ) {
								fieldId = parseInt( fieldId );
							}

							return fieldId;
						} );

						return self.fieldId.indexOf( fieldId ) !== -1;
					}

					gform.addAction( 'gpnf_session_initialized', function(gpnf) {
						if ( gpnf.formId != self.formId ) {
							return;
						}

						var $form = $( '#gform_' + self.formId );
						var $field = $form.find( '#field_' + self.formId + '_' + self.fieldId );
						var $entries = $field.find('.gpnf-nested-entries tbody');

						$entries.sortable({
							axis: 'y',
							// Logic to help with keeping the width of <tr>'s correct when dragging them
							helper: function(e, tr) {
								var helperRow = tr
									.clone()
									.css('display', 'table');

								return helperRow;
							},
							update: function(event, ui) {
								var sortedEntryIds = $entries.find('tr').map(function() {
									return $(this).data('entryid');
								}).get();

								var currentEntries = gpnf.viewModel.entries();

								// Sort the entries based on the sortedEntryIds
								var sortedEntries = sortedEntryIds.map(function(entryId) {
									return currentEntries.find(function(entry) {
										return entry.id == entryId;
									});
								});

								gpnf.viewModel.entries( sortedEntries );

								// Send AJAX request to update the entry order
								$.post( gpnf.ajaxUrl, {
									action: 'gpnf_update_entry_order',
									gpnf_context: gpnf.sessionData.gpnf_context,
									nonce: GPNFData.nonces.refreshMarkup, // Using an existing nonce.
									formId: gpnf.formId,
									fieldId: gpnf.fieldId,
									entryIds: sortedEntryIds
								} );
							}
						});

						console.log(gpnf)

						gpnf.viewModel.entries.subscribe( function( entries ) {
							$entries.sortable( 'refresh' );
						} );
					} );

				}

			} )( jQuery );

		</script>

		<?php
	}

	/**
	 * @param array $form
	 *
	 * @return void
	 */
	public function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'formId'  => $this->_args['form_id'],
			'fieldId' => $this->_args['field_id'],
		);

		// Enqueue jQuery UI Sortable
		wp_enqueue_script( 'jquery-ui-sortable' );

		$script = 'new ' . __CLASS__ . '( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( strtolower( __CLASS__ ), $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	/**
	 * @param array $form
	 *
	 * @return bool
	 */
	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

	/**
	 * Check if the field is applicable for the current instance.
	 *
	 * @param int $field_id
	 *
	 * @return bool
	 */
	public function is_applicable_field( $field_id ) {
		$field_ids = isset( $this->_args['field_id'] ) ? $this->_args['field_id'] : array();

		if ( empty( $field_ids ) ) {
			return true;
		}

		if ( ! is_array( $field_ids ) ) {
			$field_ids = array( $field_ids );
		}

		if ( in_array( $field_id, $field_ids, false ) ) {
			return true;
		}

		return false;
	}

}

# Configuration

// Enable sorting for all Nested Form fields on all forms.
new GPNF_Sortable_Entries();

// Enable sorting for Nested Form field with ID 1 on form with ID 8.
//new GPNF_Sortable_Entries( array(
//	'form_id'  => 8,
//	'field_id' => 1,
//) );

// Enable sorting for all Nested Form fields on form with ID 8.
//new GPNF_Sortable_Entries( array(
//	'form_id' => 8,
//) );

