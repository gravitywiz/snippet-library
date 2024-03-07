<?php
/**
 * Gravity Wiz // Limit Multi Selects
 * https://gravitywiz.com/
 *
 * Set a minimum and maximum number of choices that can be selected in a Multi Select field. Optionally, set the minimum
 * or maximum number of choices based on the value of another field.
 */
class GW_Limit_Multi_Select {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'      => false,
			'field_id'     => false,
			'min'          => false,
			'max'          => false,
			'min_field_id' => false,
			'max_field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		add_filter( 'gform_field_validation', array( $this, 'validate' ), 10, 4 );

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

					self.init = function() {

						let $select = $( `#input_${self.formId}_${self.fieldId}` );

						self.limitMultiSelect( $select, self.getMax() );

						$select.on( 'change', function () {
							self.limitMultiSelect( $( this ), self.getMax() );
						} );

						if ( self.maxFieldId ) {
							self.$maxField().on( 'change', function() {
								self.limitMultiSelect( $select, self.getMax() );
							} );
						}

						let namespace = `gwlms_${self.formId}_${self.fieldId}`;

						$( document )
							.off( `gppa_updated_batch_fields.${namespace}` )
							.on( `gppa_updated_batch_fields.${namespace}`, function( e, formId ) {
								if ( parseInt( formId ) === parseInt( self.formId ) ) {
									self.init();
								}
							} );

					};

					self.limitMultiSelect = function( $select, max ) {
						var selectedCount = $select.find( 'option:selected' ).length;

						if ( selectedCount <= max ) {
							lastAcceptedValue = $select.val();
						} else {
							if ( lastAcceptedValue.length > max ) {
								// Remove elements from array until it is less than the max variable using array.splice.
								lastAcceptedValue.splice( max, lastAcceptedValue.length - max );
							}
							$select.val( lastAcceptedValue );
							$select.blur();
						}

						// Blur selector on mobile after max number of options are selected
						if ( !! navigator.platform.match( /iPhone|iPod|iPad/ ) || !! navigator.userAgent.match( /android/i ) ) {
							if ( selectedCount >= max ) {
								$select.blur();
							}

							if ( selectedCount > max ) {
								alert('Please select ' + max + ' choices or fewer.');
							}
						} else {
							// If not on iOS, disable the options as disabled options do not update live on iOS
							$select
								.find( 'option:not(:checked)' )
								.prop( 'disabled', selectedCount >= max )
								.trigger( 'chosen:updated' );
						}
					}

					self.getMax = function() {
						return self.maxFieldId ? self.$maxField().val() : self.max;
					}

					self.$maxField = function() {
						return $( `#input_${self.formId}_${self.maxFieldId}` );
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
			'formId'     => $this->_args['form_id'],
			'fieldId'    => $this->_args['field_id'],
			'max'        => $this->_args['max'],
			'maxFieldId' => $this->_args['max_field_id'],
		);

		$script = 'new ' . __CLASS__ . '( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( strtolower( __CLASS__ ), $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {
		$form_id = $form['id'] ?? $form;
		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

	public function is_applicable_field( $field ) {
		return $this->is_applicable_form( $field->formId ) && (int) $this->_args['field_id'] === (int) $field->id;
	}

	public function validate( $result, $value, $form, $field ) {

		if ( ! $this->is_applicable_field( $field ) || ! is_array( $value ) ) {
			return $result;
		}

		$min = $this->get_min();
		$max = $this->get_max();

		$count = count( $value );

		if ( $count < $min ) {
			$result['is_valid'] = false;
			$result['message']  = __( "Please select {$min} or more options." );
		} elseif ( $count > $max ) {
			$result['is_valid'] = false;
			$result['message']  = __( "Please select less than {$max} options." );
		}

		return $result;
	}

	public function get_max() {

		if ( $this->_args['max_field_id'] ) {
			return rgpost( 'input_' . $this->_args['max_field_id'] );
		}

		return $this->_args['max'];
	}

	public function get_min() {

		if ( $this->_args['min_field_id'] ) {
			return rgpost( 'input_' . $this->_args['min_field_id'] );
		}

		return $this->_args['min'];
	}

}

# Configuration

new GW_Limit_Multi_Select( array(
	'form_id'  => 123,
	'field_id' => 4,
	'min'      => 5,
	'max'      => 6,
) );

# Set Limits by Field

// new GW_Limit_Multi_Select( array(
// 	'form_id'      => 123,
// 	'field_id'     => 4,
// 	'min_field_id' => 5,
// 	'max_field_id' => 6,
// ) );
