<?php
/**
 * Gravity Perks // Advanced Select // Enable "Add New" Option
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Enable Advanced Select's "Add New" option that allows users to create new items that aren't in the initial list of options.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Enable "Allow field to be populated dynamically" option under your Advanced-Select-enabled field's Advanced settings.
 *    NOTE: This step is not required if you are dynamically populating choices via Populate Anything.
 */
class GPASVS_Enable_Add_New_Option {

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

		// time for hooks
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		add_filter( 'gform_pre_render', array( $this, 'allow_created_choices_in_save_and_continue' ), 10, 3 );

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

				window.GPADVSEnableAddNewOption = function( args ) {
					gform.addFilter( 'gpadvs_settings', function( settings, gpadvs ) {
						if ( (gpadvs.formId && gpadvs.formId != args.formId) || (gpadvs.fieldId && gpadvs.fieldId != args.fieldId) ) {
							return settings;
						}

						settings.create = true;

						/**
						 * Uncomment the below code to customize the display of the "Add New" option.
						 */
						// if ( ! settings.render ) {
						// 	settings.render = {};
						// }

						// settings.render.option_create = function( data, escape ) {
						// 	return '<div class="create">Add <strong>' + escape(data.input) + '</strong>&hellip;</div>';
						// }

						return settings;
					} );
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

		$script = 'new GPADVSEnableAddNewOption( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gpadvs_enable_add_new_option', $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

	public function allow_created_choices_in_save_and_continue( $form, $ajax, $field_values ) {
		foreach ( $form['fields'] as &$field ) {
			if ( ! gp_advanced_select()->is_advanced_select_field( $field ) ) {
				continue;
			}

			$incomplete_submission_info = GFFormsModel::get_draft_submission_values( $_GET['gf_token'] );
			$submission_details_json    = $incomplete_submission_info['submission'];
			$submission_details         = json_decode( $submission_details_json, true );

			/*
			 * If there is a value for this field in $field_values that is not present in $field->choices, add it and mark
			 * it as selected.
			 */
			$field_value   = rgars( $submission_details, 'partial_entry/' . $field->id );
			$choice_values = wp_list_pluck( $field->choices, 'value' );

			if ( ! in_array( $field_value, $choice_values ) ) {
				$field->choices[] = array(
					'text'       => $field_value,
					'value'      => $field_value,
					'isSelected' => true,
				);
			}
		}

		return $form;
	}

}

# Configuration

new GPASVS_Enable_Add_New_Option();
