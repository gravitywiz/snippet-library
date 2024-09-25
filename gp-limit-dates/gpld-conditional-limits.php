<?php
/**
 * Gravity Perks // Limit Dates // Conditional Limits
 * https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 *
 * Provide conditional date options for your date fields.
 *
 */
class GPLD_Conditional_Limits {

	private $_args = array();

	public function __construct( $args = array() ) {
		if ( ! function_exists( 'gp_limit_dates' ) ) {
			return;
		}

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
		add_filter( 'gform_enqueue_scripts', array( $this, 'enqueue_form_scripts' ) );
		add_action( 'gform_field_validation', array( $this, 'validate' ), 11, 4 );
	}

	public function enqueue_form_scripts( $form ) {
		if ( $this->is_applicable_form( $form ) ) {
			wp_enqueue_script( 'gform_conditional_logic' );
		}
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

				window.GPLDConditionalLimits = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( prop in args ) {
						if( args.hasOwnProperty( prop ) )
							self[prop] = args[prop];
					}

					self.init = function() {

						var $input = $( '#input_' + self.formId + '_' + self.fieldId );

						gform.addAction( 'gform_input_change', function( elem, formId, fieldId ) {

							if ( !window.GPLimitDates ) {
								return;
							};

							for ( var i = 0; i < self.conditionals.length; i++ ) {
								if ( gf_get_field_action( self.formId, self.conditionals[ i ].conditionalLogic ) == 'show' ) {

									gform.addFilter( 'gpld_datepicker_data', function( data ) {
										return {
											...data, [args.fieldId]: { ...data[args.fieldId], ...self.conditionals[ i ].options }
										}
									} );

									$input.removeClass( 'hasDatepicker' );
									gformInitSingleDatepicker( $input );

									break;
								}
							}
						} );

						// Trigger `gform_input_change' action on multipage form navigation
						gf_input_change( $input, self.formId, self.fieldId );
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
			'formId'       => $this->_args['form_id'],
			'fieldId'      => $this->_args['field_id'],
			'conditionals' => $this->_args['conditionals'],
		);

		$script = 'new GPLDConditionalLimits( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( __class__, $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	public function is_applicable_form( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

	public function is_applicable_field( $field ) {
		$field_id = isset( $field['id'] ) ? $field['id'] : $field;

		return $field['type'] == 'date' && $field['dateType'] == 'datepicker' && $field_id == $this->_args['field_id'];
	}

	public function validate( $result, $value, $form, $field ) {
		if ( ! $this->is_applicable_field( $field ) || ! $this->is_applicable_form( $form ) ) {
			return $result;
		}

		foreach ( $this->_args['conditionals'] as $conditional ) {
			if ( GFCommon::evaluate_conditional_logic( $conditional['conditionalLogic'], $form, GFFormsModel::get_current_lead() ) ) {
				add_filter( "gpld_limit_dates_options_{$this->_args['form_id']}_{$this->_args['field_id']}", function( $options ) use ( $conditional ) {
					return array_merge( $options, $conditional['options'] );
				} );

				break;
			}
		}

		if ( ! rgblank( $value ) && ! gp_limit_dates()->is_valid_date( $value, $field ) ) {
			$result['is_valid'] = false;
			$result['message']  = __( 'Please enter a valid date.', 'gp-limit-dates' );
		}

		return $result;
	}
}

# Configuration

new GPLD_Conditional_Limits( array(
	'form_id'      => 30,
	'field_id'     => 3,
	'conditionals' => array(
		array(
			'options'          => array(
				'minDate'    => '{today}',
				'minDateMod' => '+2 days',
				'daysOfWeek' => array( 1 ),
			),
			'conditionalLogic' => array(
				'logicType'  => 'any',
				'actionType' => 'show',
				'rules'      => array(
					array(
						'fieldId'  => '1',
						'operator' => 'is',
						'value'    => '68462',
					),
				),
			),
		),
		array(
			'options'          => array(
				'minDate'    => '{today}',
				'minDateMod' => '+2 days',
				'daysOfWeek' => array( 1, 2 ),
			),
			'conditionalLogic' => array(
				'logicType'  => 'any',
				'actionType' => 'show',
				'rules'      => array(
					array(
						'fieldId'  => '1',
						'operator' => 'is',
						'value'    => '68502',
					),
				),
			),
		),
	),
) );
