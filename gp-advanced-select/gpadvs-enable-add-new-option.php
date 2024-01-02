<?php
/**
 * Gravity Perks // Advanced Select // Enable "Add New" Option
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Enable Advanced Select's "Add New" option that allows users to create new items that aren't in the initial list of options.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the instructions here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Customize the form_id and field_id properties at the bottom of this snippet.
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

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );

		// Advanced Select has to beat GF's default Chosen init script to the punch so uses `gform_pre_render` to register
		// its init script early. We need to beat it to the punch, so we use the same filter on a higher priority.
		add_filter( 'gform_pre_render', array( $this, 'add_init_script' ), 9, 2 );

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

			window.GPADVSEnableAddNewOption = function( args ) {
				console.log( 'gwiz' );
				gform.addFilter( 'gpadvs_settings', function( settings, gpadvs ) {
					if ( args.formId && gpadvs.formId != args.formId ) {
						return settings;
					}

					if ( args.fieldId && gpadvs.fieldId != args.fieldId ) {
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
			}

		</script>

		<?php
	}

	public function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		$args = array(
			'formId'  => $this->_args['form_id'],
			'fieldId' => $this->_args['field_id'],
		);

		$script = 'new GPADVSEnableAddNewOption( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gpadvs_enable_add_new_option', $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

		return $form;

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

	public function allow_created_choices_in_save_and_continue( $form, $ajax, $field_values ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( ! gp_advanced_select()->is_advanced_select_field( $field ) ) {
				continue;
			}

			// Check if this instance is targeting all Advanced Select fields or a specific field.
			if ( ! empty( $this->_args['field_id'] ) && $this->_args['field_id'] != $field->id ) {
				continue;
			}

			$incomplete_submission_info = GFFormsModel::get_draft_submission_values( rgget( 'gf_token' ) );
			if ( ! $incomplete_submission_info ) {
				continue;
			}

			$submission_details = json_decode( $incomplete_submission_info['submission'], true );

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

new GPASVS_Enable_Add_New_Option(array(
	'form_id' => 123,
    // 'field_id' => 4,
));
