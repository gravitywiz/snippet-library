<?php
/**
 * Gravity Wiz // Gravity Forms // Prevent Duplicate Selections
 * https://gravitywiz.com/
 *
 * Prevent duplicate selections in choice-based fields. Currently works with Checkbox, Radio Button, Drop Down and
 * Enhanced-UI-enabled Multi Select fields.
 *
 * Instructions:
 *
 * 1. Install this snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Add 'gw-prevent-duplicates' to the CSS Class Name setting for any field in which duplicate selections
 *    should be prevented.
 *
 * Plugin Name:  Gravity Forms Prevent Duplicate Selections
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Prevent duplicate selections in choice-based fields. Currently works with Checkbox, Radio Button, Drop Down and Enhanced-UI-enabled Multi Select fields.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   http://gravitywiz.com
 */

class GW_Prevent_Duplicate_Selections {
	public function __construct() {
		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function add_init_script( $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array();

		$script = 'new ' . __CLASS__ . '( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( strtolower( __CLASS__ ) ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	public function output_script() {
		?>

		<script type="text/javascript">
			window.<?php echo __CLASS__; ?> = function() {
				var $ = jQuery;
				/**
				 * Cache for storing previous values of GP Advanced Select enabled multi-select
				 * fields. We use this to check against in order to determine which option was
				 * changed on a change event.
				 */
				const gpadvsPreviousValues = {};

				window.gform.addFilter( 'gplc_excluded_input_selectors', function( selectors ) {
					selectors.push( '.gw-disable-duplicates-disabled' );
					return selectors;
				});

				// Bind events, use .on with delegation and always get fresh selectors for AJAX-refreshed fields
				$( '.gw-prevent-duplicates' ).on( 'change', 'input, select', function( event, selected ) {
					gwDisableDuplicates( $( this ), $( '.gw-prevent-duplicates' ).find( 'input, select' ), selected );
				} );

				// Handle on-load
				$inputs = $( '.gw-prevent-duplicates' ).find( 'input, select' );

				$inputs.each( function( event ) {
					gwDisableDuplicates( $( this ), $inputs.not('.gw-disable-duplicates-disabled') );
				} );

				/**
				 * Given a select element, determines which option was changed.
				 *
				 * @param {HTMLSelectElement} $select
				 * @param {object} selected
				 * @param {string} selected.selected
				 * @param {string} selected.deselected
				 * @returns HTMLOptionElement
				 */
				function getChangedOptionElFromSelect( $select, selected ) {
					/**
					 * Handle multi select fields with "Enhanced UI" enabled.
					 *
					 * Multi Selects fields require Chosen to be enabled. It provides the `selected` data payload
					 * on the jQuery event which indicates which option was selected/deselected.
					 *
					 * - If the option was selected, then selected.selected will be the value of the selected option.
					 * - If the option was deselected, the selected.deslected will be the value of the deselected option.
					 */
					if ( selected ) {
						let value = selected.selected ? selected.selected : selected.deselected;
						return findOptionByValue( $select, value );
					}

					/**
					 * Handle multi select fields with GP Advanced Select enabled.
					 */
					if ($select.siblings('.ts-wrapper').length) {
						const val = $select.val();

						// this is a single select field so the value is a string
						if ( typeof val === 'string' ) {
							return findOptionByValue( $select, val );
						}

						const selectId = $select.attr('id');
						const prevVal = gpadvsPreviousValues[selectId] || null;

						// Cache the current value so that we can compare against it on
						// on the next change event to determine which option was changed.
						gpadvsPreviousValues[selectId] = val;

						let changedOptVal;

						if ( ! prevVal ) {
							changedOptVal = val[0];
						} else if ( prevVal.length > val.length ) {
							changedOptVal = getArrayDiff( prevVal, val );
						} else {
							changedOptVal = getArrayDiff( val, prevVal );
						}

						return findOptionByValue( $select, changedOptVal );
					}

					return $select.find( 'option:selected' );
				}

				/**
				 * Get the value that changed between two arrays.
				 * This expects that the length of array 1 is greater than the length of array 2.
				 *
				 * @param {array} arr1
				 * @param {array} arr2
				 *
				 * @returns {string}
				 */
				function getArrayDiff( arr1, arr2 ) {
					return arr1.filter( x => ! arr2.includes( x ) )[ 0 ];
				}

				function findOptionByValue( $select, value ) {
					return $select.find( '[value="' + value + '"]' );
				}

				function gwDisableDuplicates( $elem, $group, selected ) {
					// Some elements have a parent element (e.g. a <select>) that contains the actual elements (e.g. <option>) we want enable/disable.
					let $parent = $elem;

					if ( $elem.is( 'select' ) ) {
						$elem = getChangedOptionElFromSelect( $elem, selected );

						// Note: This prevents selects from working with other field types.
						$group = $group.find( 'option' );
					}

					let value     = $elem.val();
					let $targets  = $group.not( $elem ).not( '.gplc-disabled' ).not( '.gpi-disabled' ).not( '.gf_placeholder' );
					let isChecked = $elem.is( ':checked' );

					// We use this to instruct Gravity Forms not to re-enable disabled duplicate options when
					// that option is revealed by conditional logic.
					let disabledClass = 'gf-default-disabled gw-disable-duplicates-disabled';
					let previousValue;

					// Only one choice can be selected in a Radio Button or Drop Down field while multiple choices
					// can be selected in a Checkbox or Multi Select field. This logic handles saving/retrieving the
					// previous value and re-enabling inputs/options with the previous value.
					if ( $elem.is( ':radio, option' ) && ! $parent.prop( 'multiple' ) ) {
						previousValue = $elem.parents( '.gfield' ).data( 'previous-value' );
						$elem.parents( '.gfield' ).data( 'previous-value', $elem.val() );
						if ( previousValue ) {
							$targets
								.filter( '[value="{0}"]'.gformFormat( previousValue ) )
								.prop( 'disabled', false )
								.removeClass( disabledClass );
						}
					}

					let $filteredTargets = $targets
						.filter( '[value="{0}"]'.gformFormat( value ) )
						.prop( 'disabled', isChecked );

					// For Drop Down and Multi Selects, we need to loop through each field and select the first available option - and -
					// trigger Chosen to update the select box so newly disabled options are displayed as disabled.
					if ( $elem.is( 'option' ) ) {
						$filteredTargets.parents( 'select' ).each( function() {
							let $options = $( this ).find( 'option' );
							if ( $options.filter( ':selected:disabled' ).length ) {
								$options.not( ':disabled' ).first().prop( 'selected', true );
							}
							$( this ).trigger( 'chosen:updated' );
						} );
					}

					if ( isChecked ) {
						$filteredTargets.addClass( disabledClass );
					} else {
						$filteredTargets.removeClass( disabledClass );
					}

				}
			};
		</script>

		<?php
	}

	public function is_applicable_form( $form ) {
		foreach ( $form['fields'] as $field ) {
			if ( str_contains( $field['cssClass'], 'gw-prevent-duplicates' ) ) {
				return true;
			}
		}

		return false;
	}

}

# Configuration
new GW_Prevent_Duplicate_Selections();
