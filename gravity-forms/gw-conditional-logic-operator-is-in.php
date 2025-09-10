<?php
/**
 * Gravity Wiz // Gravity Forms // Conditional Logic Operator: "Is In"
 *
 * Instruction Video: https://www.loom.com/share/03c2d8bd75ed46b29d66a3c8b44eac8c
 *
 * Check if a source value is in a comma-delimited list of values with the new "is in" conditional logic operator!
 *
 * Plugin Name:  GF Conditional Logic Operator: "Is In"
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Check if a source value is in a comma-delimited list of values.
 * Author:       Gravity Wiz
 * Version:      1.2
 * Author URI:   https://gravitywiz.com
 */
class GF_CLO_Is_In {

	public function __construct() {

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'admin_footer', array( $this, 'output_admin_inline_script' ) );
		add_filter( 'gform_is_valid_conditional_logic_operator', array( $this, 'whitelist_operator' ), 10, 2 );
		add_filter( 'gpeu_field_filters_from_conditional_logic', array( $this, 'convert_conditional_logic_to_field_filters' ) );

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
		add_filter( 'gform_is_value_match', array( $this, 'evaluate_operator' ), 10, 6 );

		// Add support for Conditional Pricing import operators
		if ( class_exists( 'GP_Conditional_Pricing' ) ) {
			add_filter( 'gpcp_supported_import_operators', array( $this, 'add_import_operator' ) );
		}

	}

	public function output_admin_inline_script() {
		if ( ! GFForms::get_page() && is_admin() && ! in_array( rgget( 'page' ), array( 'gp-email-users' ) ) ) {
			return;
		}
		?>

		<script>

			/**
			 * Gravity Forms handles conditional logic operator labels differently depending on context. In the conditional
			 * logic flyout, whatever value is passed as the value is used as the label. Everywhere else, the value is
			 * used as a key to look up the label in the gf_vars variable.
			 *
			 * Let's add our property to the gf_vars variable to account for both scenarios.
			 */
			if ( window.gf_vars ) {
				gf_vars['is in'] = '<?php esc_html_e( 'is in' ); ?>';
			}

			gform.addFilter( 'gform_conditional_logic_operators', function( operators ) {
				operators.is_in = 'is in';
				return operators;
			} );

		</script>

		<?php
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

				window.GWCLOIsIn = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						gform.addFilter( 'gform_is_value_match', function( isMatch, formId, rule ) {

							if ( rule.operator !== 'is_in' ) {
								return isMatch;
							}

							rule.value.split( ',' ).every( function( currentValue, index, arr ) {
								var tempRule = {
									fieldId:  rule.fieldId,
									operator: 'is',
									value:    currentValue.trim()
								}
								if ( gf_is_match( formId, tempRule ) ) {
									isMatch = true;
									return false;
								}
								return true;
							} );

							return isMatch;
						} );

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

		$script = 'new GWCLOIsIn();';
		$slug   = implode( '_', array( 'gwclo_isin', $form['id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function whitelist_operator( $is_valid, $operator ) {
		if ( $operator === 'is_in' ) {
			$is_valid = true;
		}
		return $is_valid;
	}

	public function evaluate_operator( $is_match, $field_value, $target_value, $operation, $source_field, $rule ) {

		if ( $rule['operator'] !== 'is_in' || rgar( $rule, 'gwcloinEvaluatingOperator' ) ) {
			return $is_match;
		}

		$rule['gwcloinEvaluatingOperator'] = true;

		$values = explode( ',', $rule['value'] );

		foreach ( $values as $value ) {
			if ( GFFormsModel::is_value_match( $field_value, trim( $value ), 'is', $source_field, $rule ) ) {
				$is_match = true;
				break;
			}
		}

		$rule['gwcloinEvaluatingOperator'] = false;

		return $is_match;

	}

	public function convert_conditional_logic_to_field_filters( $field_filters ) {

		foreach ( $field_filters as &$field_filter ) {

			// The "mode" (any/all) is typically the first key in the $field_filters. Let's ignore it.
			if ( ! is_array( $field_filter ) ) {
				continue;
			}

			switch ( $field_filter['operator'] ) {
				case 'is_in':
					$field_filter['operator'] = 'IN';
					$field_filter['value']    = array_map( 'trim', explode( ',', $field_filter['value'] ) );
					break;
				// Thinking ahead. "not in" is not added in the UI yet.
				case 'not_in':
					$field_filter['operator'] = 'NOT IN';
					$field_filter['value']    = array_map( 'trim', explode( ',', $field_filter['value'] ) );
					break;
			}
		}

		return $field_filters;
	}

	public function is_applicable_form( $form ) {
		// @todo we will need to recursively search conditional logic for "is in" operator (see GPCLD).
		return GFFormDisplay::has_conditional_logic( $form );
	}

	// Register 'is_in' operator for GP Conditional Pricing CSV imports.
	public function add_import_operator( $operators ) {
		$operators['~'] = 'is_in';
		return $operators;
	}

}

# Configuration

new GF_CLO_Is_In();
