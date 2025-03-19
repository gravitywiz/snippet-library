<?php
/**
 * Gravity Wiz // Gravity Perks // GP Limit Checkboxes // Conditional Limits
 *
 * Provide conditional min/max for your checkbox fields.
 *
 * @version   0.5
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/...
 *
 * Plugin Name: GP Limit Checkboxes - Conditional Limits
 * Plugin URI: http://gravitywiz.com/...
 * Description: Provide conditional min/max for your checkbox fields.
 * Author: Gravity Wiz
 * Version: 0.4
 * Author URI: http://gravitywiz.com
 */
class GPLCB_Conditional_Limits {

	protected static $is_script_output = false;

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

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		add_filter( 'gform_pre_render', array( $this, 'add_trigger_class' ), 10, 2 );
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_enqueue_scripts', array( $this, 'enqueue_form_scripts' ) );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		add_filter( 'gplc_group', array( $this, 'handle_conditionals' ), 10, 2 );

	}

	public function add_trigger_class( $form ) {
		foreach ( $form['fields'] as &$field ) {
			$field->cssClass .= ' gfield_trigger_change';
		}
		return $form;
	}

	public function enqueue_form_scripts( $form ) {
		if ( $this->is_applicable_form( $form ) ) {
			wp_enqueue_script( 'gform_conditional_logic' );
		}
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && ! self::$is_script_output && ! $this->is_ajax_submission( $form['id'], $is_ajax_enabled ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function is_ajax_submission( $form_id, $is_ajax_enabled ) {
		return isset( GFFormDisplay::$submission[ $form_id ] ) && $is_ajax_enabled;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				// Temporary solution until GF 2.3.
				if( ! window['gf_form_conditional_logic'] ) {
					window['gf_form_conditional_logic'] = [];
				}

				window.GPLCBConditionalLimits = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( prop in args ) {
						if( args.hasOwnProperty( prop ) )
							self[prop] = args[prop];
					}

					self.init = function() {

						gform.addFilter( 'gplc_group', function( group, fieldId, $elem, gpLimitCheckboxes ) {

							// Only evaluate for conditional logic triggered checkbox clicks.
							// if( fieldId !== null ) {
							//     return group;
							// }
							if( $.inArray( self.fieldId, group.fields ) === -1 ) {
								return group;
							}

							for( var i = 0; i < self.conditionals.length; i++ ) {
								if( gf_get_field_action( self.formId, self.conditionals[ i ].conditionalLogic ) == 'show' ) {
									if( group.max != self.conditionals[ i ].max ) {
										group.max = self.conditionals[ i ].max;
										// @todo uncheck all checkboxes
									}
									break;
								}
							}

							return group;
						} );

						gform.addAction( 'gform_input_change', function( elem, formId, fieldId ) {
							for( var i = 0; i < self.conditionals.length; i++ ) {
								for( var j = 0; j < self.conditionals[ i ].conditionalLogic.rules.length; j++ ) {
									var rule = self.conditionals[ i ].conditionalLogic.rules[ j ];
									if( parseInt( rule.fieldId ) == parseInt( fieldId ) ) {
										window.GPLimitCheckboxes.instances[ self.formId ].handleCheckboxClick();
									}
								}
							}
						} );

						// force GPLimitCheckbox groups to be re-evaluated anytime conditional logic changes; this is a
						// short term solution; long term we will need to listen for the specific inputs because we can't
						// always assume the trigger field will be using Gravity Forms default conditional logic.
						// $( document ).on( 'gform_post_conditional_logic', function( event, formId ) {
						// 	if( self.formId == formId && window.GPLimitCheckboxes ) {
						//   window.GPLimitCheckboxes.instances[ self.formId ].handleCheckboxClick();
						//     }
						// } );

					};

					self.init();

				}

			} )( jQuery );

		</script>

		<?php

		self::$is_script_output = true;

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

		$script = 'new GPLCBConditionalLimits( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( __class__, $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function handle_conditionals( $group, $form ) {

		if ( ! in_array( $this->_args['field_id'], $group['fields'] ) ) {
			return $group;
		}

		foreach ( $this->_args['conditionals'] as $conditional ) {
			if ( GFCommon::evaluate_conditional_logic( $conditional['conditionalLogic'], $form, GFFormsModel::get_current_lead() ) ) {
				$group['min'] = $conditional['min'];
				$group['max'] = $conditional['max'];
				break;
			}
		}

		return $group;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

}

# Configuration

new GPLCB_Conditional_Limits( array(
	'form_id'      => 1654,
	'field_id'     => 7,
	'conditionals' => array(
		array(
			'min'              => 2,
			'max'              => 2,
			'conditionalLogic' => array(
				'logicType'  => 'all',
				'actionType' => 'show',
				'rules'      => array(
					array(
						'fieldId'  => 2,
						'operator' => 'is',
						'value'    => 'MX250-2',
					),
				),
			),
		),
		array(
			'min'              => 3,
			'max'              => 3,
			'conditionalLogic' => array(
				'logicType'  => 'all',
				'actionType' => 'show',
				'rules'      => array(
					array(
						'fieldId'  => 2,
						'operator' => 'is',
						'value'    => 'MX 250-3',
					),
				),
			),
		),
	),
) );
