<?php
/**
 * Gravity Perks // Address Autocomplete // Autocomplete By City
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Instruction Video: https://www.loom.com/share/4649b62e6cf54ac5b4dcf2c9ab00d568
 *
 * Autocomplete cities (including state and country) instead of full addresses.
 */
class GPAA_Autocomplete_By_City {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		add_filter( 'gpaa_init_args', array( $this, 'set_city_input_as_autocomplete_input' ) );
		add_filter( 'gform_form_post_get_meta', array( $this, 'disable_unused_inputs' ) );

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

				window.GPPAAutocompleteByCity = function( args ) {

					var self = this;

					self.init = function() {

						window.gform.addFilter( 'gpaa_autocomplete_options', function( options, gpaa, formId, fieldId ) {
							if ( formId == args.formId && fieldId == args.fieldId ) {
								options.types = [ '(cities)' ];
							}
							return options;
						} );

					};

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
			'formId'  => $this->_args['form_id'],
			'fieldId' => $this->_args['field_id'],
		);

		$script = 'new GPPAAutocompleteByCity( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gw_js_snippet_template', $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function set_city_input_as_autocomplete_input( $args ) {
		if ( ! $this->is_applicable_form( $args['formId'] ) || ! $this->is_applicable_field( $args['fieldId'] ) ) {
			return $args;
		}
		$args['inputSelectors']['autocomplete'] = "#input_{$args['formId']}_{$args['fieldId']}_3";
		return $args;
	}

	public function disable_unused_inputs( $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}
		foreach ( $form['fields'] as &$field ) {
			if ( ! $this->is_applicable_field( $field->id ) ) {
				continue;
			}
			$field->inputs[0]['isHidden'] = true;
			$field->inputs[1]['isHidden'] = true;
			$field->inputs[4]['isHidden'] = true;
		}
		return $form;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

	public function is_applicable_field( $field_id ) {
		return $this->_args['field_id'] == $field_id;
	}

}

# Configuration

new GPAA_Autocomplete_By_City( array(
	'form_id'  => 123, // Update "123" to your form ID.
	'field_id' => 4,   // Update "4" to your Address field ID.
) );
