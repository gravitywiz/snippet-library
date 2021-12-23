<?php
/**
 * Gravity Wiz // Gravity Forms // Validate that a Value Exists
 *
 * Ensure that a value entered in Form A has been previously submitted on Form B. This is useful if you're generating a reference number of some sort
 * on Form B and would like the user to enter it on Form A.
 *
 * @version   1.8.1
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      https://gravitywiz.com/require-existing-value-submission-gravity-forms/
 */
class GW_Value_Exists_Validation {

	protected static $is_script_output = false;

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'target_form_id'          => false,
			'target_field_id'         => false,
			'source_form_id'          => false,
			'source_field_id'         => false,
			'validation_message'      => __( 'Please enter a valid value.' ),
			'field_map'               => array(),
			'disable_ajax_validation' => false
		) );

		// Map source and target fields to field map if field map is not set.
		if( empty( $this->_args['field_map'] ) ) {
			$this->_args['field_map'] = array( $this->_args['source_field_id'] => $this->_args['target_field_id'] );
		}

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		add_filter( 'gform_validation', array( $this, 'validation' ) );

		if( ! $this->_args['disable_ajax_validation'] ) {

			add_action( 'gform_enqueue_scripts',       array( $this, 'enqueue_form_script' ) );
			add_filter( 'gform_pre_render',            array( $this, 'load_form_script' ), 10, 2 );
			add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );

			add_action( 'wp_ajax_gwvev_does_value_exist',        array( $this, 'ajax_does_value_exist' ) );
			add_action( 'wp_ajax_nopriv_gwvev_does_value_exist', array( $this, 'ajax_does_value_exist' ) );

		}

	}

	public function enqueue_form_script( $form ) {
		if( $this->is_applicable_form( $form ) ) {
			wp_enqueue_script( 'gform_gravityforms' );
		}
		return $form;
	}

	public function validation( $result ) {

		if( ! $this->is_applicable_form( $result['form'] ) ) {
			return $result;
		}

		foreach( $result['form']['fields'] as &$field ) {

			if( $this->is_applicable_field( $field ) && ! GFFormsModel::is_field_hidden( $result['form'], $field, array() ) ) {

				if( ! $this->do_values_exists( $this->get_values(), $this->_args['source_form_id'] ) ) {
					$field['failed_validation'] = true;
					$field['validation_message'] = $this->_args['validation_message'];
					$result['is_valid'] = false;
				}
			}

		}

		return $result;
	}

	public function ajax_does_value_exist() {

		if( ! wp_verify_nonce( rgpost( 'nonce' ), 'gwvev_does_value_exist' ) ) {
			die( __( 'Invalid nonce.' ) );
		}

		$form_id  = rgpost( 'form_id' );
		$input_id = rgpost( 'input_id' );
		$value    = rgpost( 'value' );
		$entries  = $this->get_matching_entry( array( $input_id => $value ), $form_id );

		echo json_encode( array(
			'doesValueExist' => ! empty( $entries ),
			'entries'        => $entries,
		) );

		die();
	}

	public function does_value_exist( $value, $form_id, $field_id ) {

		$entries = $this->get_matching_entry( array( $field_id => $value ), $form_id );

		return count( $entries ) > 0;
	}

	public function do_values_exists( $values, $form_id ) {

		$entries = $this->get_matching_entry( $values, $form_id );

		return $entries && count( $entries ) > 0;
	}

	public function get_matching_entry( $values, $form_id ) {

		$field_filters = array();

		foreach( $values as $field_id => $value ) {
			$field_filters[] = array(
				'key'   => $field_id,
				'value' => $value
			);
		}

		$entries = GFAPI::get_entries( $form_id, array( 'status' => 'active', 'field_filters' => $field_filters ) );

		return reset( $entries );
	}

	public function get_field_map() {

		if( ! empty( $this->_args['field_map'] ) ) {
			$field_map = $this->_args['field_map'];
		} else {
			$field_map = array( $this->_args['target_field_id'] => $this->_args['source_field_id'] );
		}

		return $field_map;
	}

	public function get_values() {

		$field_map = $this->get_field_map();
		$values = array();

		foreach( $field_map as $source_field_id => $target_field_id ) {
			$value = rgpost( 'input_' . $target_field_id );
			if( $value ) {
				$values[ $source_field_id ] = $value;
			}
		}

		return $values;
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		// Do not output main script if AJAX is enabled
		if( ! $is_ajax_enabled && $this->is_applicable_form( $form ) && ! self::$is_script_output && ! $this->is_ajax_submission( $form['id'], $is_ajax_enabled ) ) {
			$this->output_script();
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.GWValueExistsValidation = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						$( self.selectors.join( ', ' ) ).on( 'change', function() {
							var inputId = gf_get_input_id_by_html_id( $( this ).attr( 'id' ) );
							for( var sourceFieldId in self.fieldMap ) {
								if( self.fieldMap.hasOwnProperty( sourceFieldId ) && sourceFieldId == inputId ) {
									break;
								}
							}
							self.doesValueExist( inputId, sourceFieldId, $( this ).val(), $( this ) );
						} );

					};

					self.doesValueExist = function( inputId, sourceFieldId, value, $elem ) {

						if( ! value ) {
							return;
						}

						var spinner        = new self.spinner( $elem, false, 'position:relative;top:2px;left:-25px;' ),
							responseHtmlId = 'response_{0}_{1}'.format( self.targetFormId, inputId),
							$buttons       = $( '.gform_button' );

						$buttons.prop( 'disabled', true );
						$elem.prop( 'disabled', true );
						$( '#' + responseHtmlId ).remove();

						$.post( self.ajaxUrl, {
							nonce:    self.nonce,
							action:   'gwvev_does_value_exist',
							input_id: sourceFieldId,
							value:    value,
							form_id:  self.sourceFormId
						}, function( response ) {

							$elem.prop( 'disabled', false );
							$buttons.prop( 'disabled', false );
							spinner.destroy();

							if( ! response ) {
								return;
							}

							var template = '<span id="{0}" class="gwvev-response {1}">{2}</span>';

							response = $.parseJSON( response );

							if( response.doesValueExist ) {
								$elem.after( template.format( responseHtmlId, 'gwvev-response-success', '&#10004;' ) );
							} else {
								$elem.after( template.format( responseHtmlId, 'gwvev-response-error', '&#10008;' ) );
							}

							gform.doAction( 'gwvev_post_ajax_validation', self, response );

						} );

					};

					self.spinner = function( elem, imageSrc, inlineStyles ) {

						imageSrc     = typeof imageSrc == 'undefined' || ! imageSrc ? window.gf_global.spinnerUrl : imageSrc;
						inlineStyles = typeof inlineStyles != 'undefined' ? inlineStyles : '';

						this.elem = elem;
						this.image = '<img class="gfspinner" src="' + imageSrc + '" style="' + inlineStyles + '" />';

						this.init = function() {
							this.spinner = jQuery(this.image);
							jQuery(this.elem).after(this.spinner);
							return this;
						};

						this.destroy = function() {
							jQuery(this.spinner).remove();
						};

						return this.init();
					};

					self.init();

				}

			} )( jQuery );

		</script>

		<?php

		self::$is_script_output = true;

	}

	public function add_init_script( $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
			'nonce'         => wp_create_nonce( 'gwvev_does_value_exist' ),
			'targetFormId'  => $this->_args['target_form_id'],
			'sourceFormId'  => $this->_args['source_form_id'],
			'selectors'     => $this->get_selectors( $form ),
			'fieldMap'      => $this->get_field_map(),
			'gfBaseUrl'     => GFCommon::get_base_url(),
		);

		$script = 'new GWValueExistsValidation( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gw_value_exists_validation', $this->_args['target_form_id'], $this->_args['target_field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['target_form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function get_selectors( $form ) {

		$selectors = array();

		foreach( $form['fields'] as $field ) {

			if( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$prefix = sprintf( '#input_%d_%d', $form['id'], $field->id );

			if( is_array( $field->inputs ) ) {
				foreach( $field->inputs as $input ) {
					$bits = explode( '.', $input['id'] );
					$input_id = $bits[1];
					$selectors[] = "{$prefix}_{$input_id}";
				}
			} else {
				$selectors[] = $prefix;
			}

		}

		return $selectors;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return $form_id == $this->_args['target_form_id'];
	}

	public function is_applicable_field( $field ) {
		$field_map = $this->get_field_map();
		return $this->is_applicable_form( $field->formId ) && in_array( $field->id, $field_map );
	}

	public function is_ajax_submission( $form_id, $is_ajax_enabled ) {
		// Ensure GFFormDisplay is available before continuing to check (edge case with GPPA loading a pseudo form. See HS#25828)
		return class_exists( 'GFFormDisplay' ) && isset( GFFormDisplay::$submission[ $form_id ] ) && $is_ajax_enabled;
	}

}

# Configuration

new GW_Value_Exists_Validation( array(
	'target_form_id'  => 123,
	'target_field_id' => 1,
	'source_form_id'  => 124,
	'source_field_id' => 1,
	'validation_message' => 'Hey! This isn\'t a valid reference number.'
) );
