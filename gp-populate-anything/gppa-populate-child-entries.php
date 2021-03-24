<?php
/**
 * Gravity Perks // Populate Anything // Populate Child Entries
 *
 * Populate child entries from a Nested Form field into any multi-choice field.
 *
 * Instructional Video:
 * https://www.loom.com/share/7b26b18f78624e0ca4bcd2b574636b8b
 *
 * Step 1 - Configure a Parent Entry Field
 *
 * 1. Add a Hidden field to your form.
 * 2. Select the Advanced tab.
 * 3. Check the "Allow field to be populated dynamically" option.
 * 4. Enter "gpnf_parent_entry_id" as the "Parameter Name".
 *
 * Step 2 - Add Entries
 *
 * 1. Add any multi-choice field (e.g. Radio Buttons, Checkboxes, Drop Down).
 * 2. Check the "Populate choices dynamically" option.
 * 3. Select the desired child form for which you wish to fetch child entries.
 * 4. Add a filter and filter where Parent Entry ID entry meta is equal to the the Hidden field you created in Step 1.
 *
 * @version  1.2
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Plugin Name:  GP Populate Anything — Populate Child Entries
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  A brief description about this snippet and the functionality it provides. Might also include basic usage instructions if applicable.
 * Author:       Gravity Wiz
 * Version:      1.2
 * Author URI:   http://gravitywiz.com
 */
class GPPA_Populate_Child_Entries {

	public function __construct( $args = array() ) {

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( ! is_callable( 'gp_nested_forms' ) ) {
			return;
		}

		add_filter( 'gform_field_value_gpnf_parent_entry_id', array( $this, 'populate_parent_entry_id' ), 10, 2 );
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		// Priority 11 so that it will initialize *after* Nested Forms.
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 11, 2 );

	}

	public function populate_parent_entry_id( $value, $field ) {

		$session = new GPNF_Session( $field->formId );
		$cookie  = $session->get_cookie();

		return rgar( $cookie, 'hash', '' );
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

				window.GPPAPopulateChildEntries = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						self.$peidField = $( '#input_{0}_{1}'.format( self.formId, self.fieldId ) );

						gform.addAction( 'gpnf_session_initialized', function() {
							var gpnfCookie = $.parseJSON( self.getCookie( 'gpnf_form_session_{0}'.format( self.formId ) ) );
							if ( ! self.$peidField.val() ) {
								self.$peidField
									.val( gpnfCookie.hash )
									.change();
							}
						} );

						for ( var i = 0; i < self.nestedFormFieldIds.length; i++ ) {
							window[ 'GPNestedForms_{0}_{1}'.format( self.formId, self.nestedFormFieldIds[ i ] ) ].viewModel.entries.subscribe( function( entries ) {
								self.$peidField.data( 'lastValue', '' ).change();
							} );
						}

					};

					self.getCookie = function( name ) {

						var cookieArr = document.cookie.split( ';' );

						for( var i = 0; i < cookieArr.length; i++ ) {
							var cookiePair = cookieArr[ i ].split( '=' );
							if ( name == cookiePair[0].trim() ) {
								return decodeURIComponent( cookiePair[1] );
							}
						}

						return null;
					}

					$(function(){
						self.init();
					});

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
			'formId'             => $form['id'],
			'fieldId'            => $this->get_parent_entry_id_field( $form ),
			'nestedFormFieldIds' => wp_list_pluck( $this->get_nested_form_fields( $form ), 'id' ),
		);

		$script = 'new GPPAPopulateChildEntries( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gppa_populate_child_entries', $args['formId'], $args['fieldId'] ) );

		GFFormDisplay::add_init_script( $args['formId'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {
		return $this->get_parent_entry_id_field( $form ) !== false && ! rgempty( $this->get_nested_form_fields( $form ) );
	}

	public function get_parent_entry_id_field( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( $field->inputName === 'gpnf_parent_entry_id' ) {
				return $field->id;
			}
		}
		return false;
	}

	public function get_nested_form_fields( $form ) {
		return GFAPI::get_fields_by_type( $form, 'form', false );
	}

}

# Configuration

new GPPA_Populate_Child_Entries();
