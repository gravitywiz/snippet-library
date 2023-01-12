<?php
/**
 * Gravity Wiz // Gravity Forms // Choice Counter
 *
 * Instruction Video: https://www.loom.com/share/0a5e502b49b34031bc7ed73e56dbae68
 *
 * Get the total number of checkboxes checked or multi-select options selected. Useful when wanting to apply conditional
 * logic based on those totals.
 *
 * @version   1.2
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/
 */
class GW_Choice_Count {

	private static $is_script_output;

	function __construct( $args ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'          => false,
			'count_field_id'   => false,
			'choice_field_id'  => null,
			'choice_field_ids' => false,
			'values'           => false,
		) );

		if ( isset( $this->_args['choice_field_id'] ) ) {
			$this->_args['choice_field_ids'] = array( $this->_args['choice_field_id'] );
		}

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );
		//add_action( 'gform_pre_validation',        array( $this, 'override_submitted_value') );

	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.GWChoiceCount = function( args ) {

					var self = this;

					// Copy all args to current object: formId, fieldId.
					for ( prop in args ) {
						if ( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[prop];
						}
					}

					self.updateEventHandlers = function() {
						for( var i = 0; i < self.choiceFieldIds.length; i++ ) {

							var choiceFieldId       = self.choiceFieldIds[i],
								choiceFieldSelector = '#input_' + self.formId + '_' + choiceFieldId,
								$choiceField        = $(choiceFieldSelector),
								$parentForm         = $choiceField.parents('form');

							$parentForm.off( 'click', choiceFieldSelector, self.updateChoiceEventHander );
							$parentForm.off( 'change', choiceFieldSelector, self.updateChoiceEventHander );

							if ( self.isCheckableField( $choiceField ) ) {
								$parentForm.on( 'click', choiceFieldSelector, self.updateChoiceEventHandler );
							} else {
								$parentForm.on( 'change', choiceFieldSelector, self.updateChoiceEventHandler );
							}

						}
					};

					// Event handler for all listeners to avoid DRY and to maintain a pointer reference to the function
					// which we can use to explicity unbind event handlers
					self.updateChoiceEventHandler = function() {
						self.updateChoiceCount( self.formId, self.choiceFieldIds, self.countFieldId, self.values );
					};

					self.init = function() {

						self.updateEventHandlers();
						self.updateChoiceEventHandler();

						// Listen for `gppa_updated_batch_fields` and update count as GPPA may have re-written choice fields
						$( document ).on( 'gppa_updated_batch_fields', function( e, formId ) {
							if ( parseInt( formId ) === self.formId ) {
								self.updateEventHandlers();
								self.updateChoiceEventHandler();
							}
						} );
					};

					self.isCheckableField = function($field ) {
						return Boolean( $field.find( ':checkbox, :radio' ).length );
					}

					self.updateChoiceCount = function( formId, choiceFieldIds, countFieldId, values ) {

						var countField = $( '#input_' + formId + '_' + countFieldId ),
							count      = 0;

						// Prevent count field from being recalculated if it's hidden.
						if ( ! gformIsHidden( countField ) ) {
							for ( var i = 0; i < choiceFieldIds.length; i++ ) {

								var $choiceField = $( '#input_' + formId + '_' + choiceFieldIds[ i ] );
								if ( ! values ) {
									// If no values provided in the config, just get the number of checkboxes checked.
									if ( self.isCheckableField( $choiceField ) ) {
										count += $choiceField.find( ':checked' ).not(' #choice_' + choiceFieldIds[ i ] + '_select_all').length;
									} else {
										count += $choiceField.find( 'option:selected' ).length;
									}
								} else {
									// When values are provided, match the values before adding them to count.
									var selectedValues = [];
									$choiceField.find( ':checked' ).each( function( k, $selectedChoice ) {
										selectedValues.push( $selectedChoice.value );
									});
									values.forEach( function( val ) {
										count += selectedValues.indexOf( val ) >= 0;
									});
								}

							}

							if( parseInt( countField.val() ) != parseInt( count ) ) {
								countField.val( count ).change();
							}
						}

					};

					// Recalculate Count field when it is revealed by conditional logic.
					gform.addAction( 'gform_post_conditional_logic_field_action', function ( formId, action, targetId ) {
						var id = targetId.split( '_' ).pop();
						if ( id == args.countFieldId && action === 'show' ) {
							self.updateChoiceEventHandler();
						}
					} );

					self.init();

				}

			} )( jQuery );

		</script>

		<?php
		self::$is_script_output = true;
	}

	function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form['id'] ) ) {
			return;
		}

		$args = array(
			'formId'         => $this->_args['form_id'],
			'countFieldId'   => $this->_args['count_field_id'],
			'choiceFieldIds' => $this->_args['choice_field_ids'],
			'values'         => $this->_args['values'],
		);

		$script = 'new GWChoiceCount( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gw_choice_count', $this->_args['form_id'], $this->_args['count_field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

		return;
	}

	function override_submitted_value( $form ) {
		//$_POST["input_{$this->count_field_id}"] = $day_count;
		return $form;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || intval( $form_id ) === intval( $this->_args['form_id'] );
	}

	public function is_ajax_submission( $form_id, $is_ajax_enabled ) {
		return isset( GFFormDisplay::$submission[ $form_id ] ) && $is_ajax_enabled;
	}

}

# Configuration

new GW_Choice_Count( array(
	'form_id'          => 123,                          // The ID of your form.
	'count_field_id'   => 4,                            // Any Number field on your form in which the number of checked checkboxes should be dynamically populated; you can configure conditional logic based on the value of this field.
	'choice_field_ids' => array( 5, 6 ),                // Any array of Checkbox or Multi-select field IDs which should be counted.
	'values'           => array( 'None of the above' ), // Array of values, all of which should match.
) );
