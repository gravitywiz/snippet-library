<?php
/**
 * Gravity Perks // Address Autocomplete // Use Single Line Text field as Autocomplete Input
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Instruction Video: https://www.loom.com/share/2a8b9d546bf345cfa2e18294af0dbfdb
 *
 * Use a single line text field as autocomplete input and populate the single line text field with the full address.
 *
 * Plugin Name:  GP Address Autocomplete - Use Single Line Text field as Autocomplete Input
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 * Description:  Use a single line text field as autocomplete input and populate the single line text field with the full address.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
class GPAA_Single_Line_Input {

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'              => false,
			'address_field_id'     => false,
			'single_line_field_id' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
		add_filter( 'gpaa_init_args_' . $this->_args['form_id'] . '_' . $this->_args['address_field_id'], array( $this, 'add_gpaa_init' ), 10, 2 );

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
				window.GPAASingleLineInput = function( args ) {

					gform.addFilter('gpaa_values', function (values, place, instance) {
						if ( args.useFullAddress ) {
							// Logic borrowed from https://github.com/gravitywiz/snippet-library/pull/730
							var fullAddress     = instance.inputs.autocomplete.value;
							values.autocomplete = fullAddress;
							values.address1     = fullAddress.split(',')[0].trim();
						} else {
							values.autocomplete = place.formatted_address;
						}
						return values;
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
			'formId'            => $this->_args['form_id'],
			'addressFieldId'    => $this->_args['address_field_id'],
			'singleLineFieldId' => $this->_args['single_line_field_id'],
			'useFullAddress'    => $this->_args['use_full_address'],
		);

		$script = 'new GPAASingleLineInput( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gw_js_snippet_template', $this->_args['form_id'], $this->_args['address_field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function add_gpaa_init( $args ) {

		$args['inputSelectors']['autocomplete'] = '#input_' . $this->_args['form_id'] . '_' . $this->_args['single_line_field_id'];

		return $args;

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

}

// Configuration
new GPAA_Single_Line_Input( array(
	'form_id'              => 123,     // The ID of your form.
	'address_field_id'     => 4,       // The ID of the Address field.
	'single_line_field_id' => 5,       // The ID of the Single Line Text field.
	// 'use_full_address'     => true,    // Uncomment to use the full street address if you don't want an abbreviated street address.
) );
