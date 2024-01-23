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

		if ( ! is_callable( 'gp_advanced_select' ) ) {
			return;
		}

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );

		// Advanced Select has to beat GF's default Chosen init script to the punch so uses `gform_pre_render` to register
		// its init script early. We need to beat it to the punch, so we use the same filter on a higher priority.
		add_filter( 'gform_pre_render', array( $this, 'add_init_script' ), 9, 2 );

		add_filter( 'gform_pre_render', array( $this, 'allow_created_choices_in_save_and_continue' ), 10, 3 );

		add_filter( 'gform_pre_render', array( $this, 'disable_state_validation_for_advanced_select_field' ), 10, 1 );

		add_filter( 'gform_pre_render', array( $this, 'add_new_option_to_choices' ), 10, 1 );
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

	public function is_applicable_field( $field ) {
		if ( ! gp_advanced_select()->is_advanced_select_field( $field ) ) {
			return false;
		}

		// Check if this instance is targeting all Advanced Select fields or a specific field.
		if ( ! empty( $this->_args['field_id'] ) && $this->_args['field_id'] != $field->id ) {
			return false;
		}

		return true;
	}

	public function allow_created_choices_in_save_and_continue( $form, $ajax, $field_values ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( ! $this->is_applicable_field( $field ) ) {
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

	/**
	 * Add whatever new option the user entered to the list of choices so it is not removed on multi-page forms.
	 *
	 * @param array $form The form object currently being processed.
	 *
	 * @return array
	 */
	public function add_new_option_to_choices( $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			// Get the value of the field from the $_POST data.
			$field_value = rgpost( 'input_' . $field->id );

			if ( empty( $field_value ) ) {
				continue;
			}

			// Convert non-multi select fields to an array of values so we can treat them all the same.
			if ( $field->get_input_type() !== 'multiselect' ) {
				$field_value = array( $field_value );
			}

			$existing_choice_values = wp_list_pluck( $field->choices, 'value' );

			// Add the new options to the list of choices.
			foreach ( $field_value as $value ) {
				// If the value is already in the list of choices, don't add it again.
				if ( in_array( $value, $existing_choice_values ) ) {
					continue;
				}

				$field->choices[] = array(
					'text'       => $value,
					'value'      => $value,
					'isSelected' => true,
				);
			}
		}

		return $form;
	}

	public function disable_state_validation_for_advanced_select_field( $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$field->validateState = false;
		}

		return $form;
	}

}

# Configuration

new GPASVS_Enable_Add_New_Option(array(
	'form_id' => 123,
    // 'field_id' => 4,
));
