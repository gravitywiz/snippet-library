<?php
/**
 * Gravity Perks // Nested Forms // Dynamically Set Entry Min/Max From Field Value
 * http://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
class GP_Nested_Forms_Dynamic_Entry_Min_Max {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'parent_form_id'       => false,
			'nested_form_field_id' => false,
			'default_max'          => 0, // Max to fallback to if the dependent field isn't populated
			'default_min'          => 0, // Min to fallback to if the dependent field isn't populated
			'max_field_id'         => null,
			'min_field_id'         => null, // (Optional)
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		// time for hooks
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 5, 2 );
		add_action( 'gform_validation', array( $this, 'validate' ) );

	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function validate( $validation_result ) {

		if ( ! $this->is_applicable_form( $validation_result['form']['id'] ) ) {
			return $validation_result;
		}

		$form                    = $validation_result['form'];
		$nested_form_field_id    = $this->_args['nested_form_field_id'];
		$nested_form_field_value = rgpost( "input_{$nested_form_field_id}" );
		$nested_form_field       = GFAPI::get_field( $form, $nested_form_field_id );

		$entry_ids   = explode( ',', $nested_form_field_value );
		$entry_count = empty( $nested_form_field_value ) ? 0 : count( $entry_ids );

		$min_field_id = str_replace( '.', '_', $this->_args['min_field_id'] );
		$max_field_id = str_replace( '.', '_', $this->_args['max_field_id'] );

		$raw_min = rgpost( "input_{$min_field_id}" );
		$raw_max = rgpost( "input_{$max_field_id}" );

		if ( empty( $raw_min ) ) {
			$raw_min = $this->_args['default_min'];
		}

		if ( empty( $raw_max ) ) {
			$raw_max = $this->_args['default_max'];
		}

		$min = apply_filters( 'gpnf_entry_limit_min', $raw_min, $entry_count, $entry_ids, null, $form );
		$max = apply_filters( 'gpnf_entry_limit_max', $raw_max, $entry_count, $entry_ids, null, $form );

		$has_validation_error = false;

		foreach ( $validation_result['form']['fields'] as &$field ) {

			if ( $field['failed_validation'] ) {
				$has_validation_error = true;
			}

			if ( $field['id'] !== $nested_form_field_id || ! $this->should_field_be_validated( $form, $field ) ) {
				continue;
			}

			if ( $min !== null && $min !== '' && $entry_count < $min ) {
				$field['failed_validation'] = true;
				// translators: first placeholder is number, second is a noun
				$field['validation_message'] = sprintf( __( 'Please enter a minimum of %1$d %2$s', 'gp-nested-forms' ), $min, $min > 1 ? $nested_form_field->get_items_label() : $nested_form_field->get_item_label() );

				$has_validation_error = true;
			}

			if ( $max !== null && $max !== '' && $entry_count > $max ) {
				$field['failed_validation'] = true;
				// translators: first placeholder is number, second is a noun
				$field['validation_message'] = sprintf( __( 'Please enter a maximum of %1$d %2$s', 'gp-nested-forms' ), $max, $max > 1 ? $nested_form_field->get_items_label() : $nested_form_field->get_item_label() );

				$has_validation_error = true;
			}
		}

		$validation_result['is_valid'] = ! $has_validation_error;

		return $validation_result;

	}

	public function output_script() {
		?>

		<script type="text/javascript">

			(function ($) {

				window.GPNFDynamicEntryMax = function (args) {

					var self = this;

					// copy all args to current object: (list expected props)
					for (prop in args) {
						if (args.hasOwnProperty(prop))
							self[prop] = args[prop];
					}

					self.init = function () {

						var attrReadyMaxFieldId = self.maxFieldId;
						var maxFieldId = 'input_' + self.parentFormId + '_' + attrReadyMaxFieldId;

						// GF uses "1" for the input index in the HTML id attribute for Single Product fields. Weird.
						var $maxField = $( '#input_' + self.parentFormId + '_' + parseInt( self.maxFieldId ) );
						if ( $maxField.parents( '.gfield' ).hasClass( 'gfield--input-type-singleproduct' ) ) {
							maxFieldId = 'input_' + self.parentFormId + '_' + parseInt( self.maxFieldId ) + '_1';
						}

						gform.addFilter('gpnf_entry_limit_max', function (max, currentFormId, currentFieldId) {
							if (self.parentFormId != currentFormId || self.nestedFormFieldId != currentFieldId) {
								return max;
							}

							var $maxField = $( '#' + maxFieldId );
							var value = parseInt($maxField.val());

							return value ? value : self.defaultMax;
						});

						gform.addAction( 'gform_input_change', function( el, formId, fieldId ) {
							if ( el.id === maxFieldId ) {
								// Force Knockout to recalculate the max when the number has changed
								window[ 'GPNestedForms_{0}_{1}'.format( self.parentFormId, self.nestedFormFieldId ) ].viewModel.entries.valueHasMutated();
							}
						} );

					};

					self.init();

				}

			})(jQuery);

		</script>

		<?php
	}

	public function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'parentFormId'      => $this->_args['parent_form_id'],
			'nestedFormFieldId' => $this->_args['nested_form_field_id'],
			'defaultMax'        => $this->_args['default_max'],
			'maxFieldId'        => $this->_args['max_field_id'],
		);

		$script = 'new GPNFDynamicEntryMax( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array(
			'gpnf_dynamic_entry_max_template',
			$this->_args['parent_form_id'],
			$this->_args['nested_form_field_id'],
		) );

		GFFormDisplay::add_init_script( $this->_args['parent_form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['parent_form_id'] ) || (int) $form_id === (int) $this->_args['parent_form_id'];
	}

	public function should_field_be_validated( $form, $field ) {

		if ( (int) $field['pageNumber'] !== (int) GFFormDisplay::get_source_page( $form['id'] ) ) {
			return false;
		}

		if ( GFFormsModel::is_field_hidden( $form, $field, array() ) ) {
			return false;
		}

		return true;
	}

}
