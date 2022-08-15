<?php
/**
 * Gravity Perks // Nested Forms // Add Child Entry on Trigger
 * https://gravitywiz.com/documentation/gravity-forms-nested-form/
 *
 * Instruction Video: https://www.loom.com/share/2d01000744354e7693ac4348f521992f
 *
 * This snippet allows you to auto-add a child entry to a Nested Form field, with data from your parent form. This is useful when wanting
 * to include the primary registrant as one of the child registrants in the Nested Form field.
 *
 * Plugin Name:  GP Nested Forms - Add Child Entry on Trigger
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-nested-form/
 * Description:  Auto-add a child entry to a Nested Form field, created with data from your parent form.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   https://gravitywiz.com
 */
class GPNF_Triggered_Population {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'             => 0,
			'trigger_field_id'    => 0,
			'trigger_field_value' => true,
			'field_map'           => array(),
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		add_action( 'wp_ajax_gpnf_triggered_population_add_child_entry', array( $this, 'ajax_add_child_entry' ) );
		add_action( 'wp_ajax_nopriv_gpnf_triggered_population_add_child_entry', array( $this, 'ajax_add_child_entry' ) );

	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.GPNFTriggeredPopulation = function( args ) {

					var $form;

					var self = this;

					// copy all args to current object: (list expected props)
					for( prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						$form = $( '#gform_{0}'.format( self.formId ) );

						$( '#field_{0}_{1}'.format( self.formId, self.triggerFieldId ) ).find( 'input' ).on( 'change', function() {
							var input = $( this );
							var value = input.val();
							var checked = input[0].checked;

							if ( checked && value === self.triggerFieldValue || ( value !== '' && self.triggerFieldValue === '_notempty_' ) ) {
								self.addChildEntry();
							} else {
								self.removeChildEntry();
							}
						} );

					};

					self.addChildEntry = function() {

						var request = {
							action: 'gpnf_triggered_population_add_child_entry',
							nonce: self.nonce,
							data: $form.serialize(),
							hash: self.hash,
						}

						$.post( self.ajaxUrl, request, function( response ) {
							if ( response.success ) {
								// store the entry data for later for usage in the removeChildEntry method
								window.gpnf_triggered_population_entry = response.data
								GPNestedForms.loadEntry( response.data );
							}
						} );

					}

					self.removeChildEntry = function() {

						var entryDataRowElem = $("tr[data-entryid='" + window.gpnf_triggered_population_entry.entryId + "']");
						var item = Object.assign({}, window.gpnf_triggered_population_entry);
						item.id = item.entryId;

						GPNestedForms.deleteEntry(
							item,
							entryDataRowElem,
							{ showSpinner: false },
						);

					}

					self.init();

				}

			} )( jQuery );

		</script>

		<?php
	}

	public function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'formId'            => $this->_args['form_id'],
			'triggerFieldId'    => $this->_args['trigger_field_id'],
			'triggerFieldValue' => $this->_args['trigger_field_value'],
			'nestedFormFieldId' => $this->_args['nested_form_field_id'],
			'fieldMap'          => $this->_args['field_map'],
			'ajaxUrl'           => admin_url( 'admin-ajax.php' ),
			'nonce'             => wp_create_nonce( 'gpnf_triggered_population_add_child_entry' ),
			'hash'              => $this->get_instance_hash(),
		);

		$script = 'new GPNFTriggeredPopulation( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gpnf_triggered_population', $this->_args['form_id'], $this->_args['trigger_field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}


	public function ajax_add_child_entry() {

		// Only want to handle the AJAX when current instance is the same one that requested the child entry.
		if ( rgpost( 'hash' ) !== $this->get_instance_hash() ) {
			return;
		}

		if ( ! wp_verify_nonce( rgpost( 'nonce' ), 'gpnf_triggered_population_add_child_entry' ) ) {
			wp_send_json_error( 'Invalid nonce.' );
		}

		$nested_form_field = GFAPI::get_field( $this->_args['form_id'], $this->_args['nested_form_field_id'] );
		$child_form_id     = $nested_form_field->gpnfForm;

		parse_str( rgpost( 'data' ), $data );

		$entry_data = array();

		foreach ( $this->_args['field_map'] as $source_field_id => $target_field_id ) {
			foreach ( $data as $key => $value ) {
				if ( sprintf( 'input_%s', str_replace( '.', '_', $source_field_id ) ) === $key ) {
					$entry_data[ $target_field_id ] = $value;
				}
			}
		}

		if ( empty( array_filter( $entry_data ) ) ) {
			wp_send_json_error( 'No entry data provided.' );
		}

		$entry_data['form_id'] = $child_form_id;

		$child_entry_id = GFAPI::add_entry( $entry_data );
		$child_entry    = GFAPI::get_entry( $child_entry_id );
		$field_values   = gp_nested_forms()->get_entry_display_values( $child_entry, GFAPI::get_form( $child_form_id ) );

		$child_entry = new GPNF_Entry( GFAPI::get_entry( $child_entry_id ) );
		$child_entry->set_parent_form( $this->_args['form_id'] );
		$child_entry->set_nested_form_field( $this->_args['nested_form_field_id'] );
		$child_entry->set_expiration();

		// Attach session meta to child entry.
		$session = new GPNF_Session( $this->_args['form_id'] );
		$session->add_child_entry( $child_entry->id );

		// set args passed back to entry list on front-end
		$args = array(
			'formId'      => $this->_args['form_id'],
			'fieldId'     => $nested_form_field->id,
			'entryId'     => $child_entry->id,
			'entry'       => $child_entry,
			'fieldValues' => $field_values,
			'mode'        => 'add',
		);

		wp_send_json_success( $args );

	}

	public function get_instance_hash() {
		return wp_hash( json_encode( $this->_args ) );
	}

}

# Configuration
new GPNF_Triggered_Population( array(
	'form_id'              => 123,
	'trigger_field_id'     => 4,
	'trigger_field_value'  => 'Yes',
	'nested_form_field_id' => 5,
	'field_map'            => array(
		// source field ID => target field ID
		'6.3' => '3.3',
		'6.6' => '3.6',
		'7'   => '4',
		'8'   => '5',
	),
) );
