<?php
/**
 * Gravity Wiz // Gravity Forms // Conditional Logic Operator: "Does Not Contain"
 * 
 * Instruction Video: https://www.loom.com/share/8e1b27ec47b341dbb4f0da2bec6a960b
 *
 * Check if a source field value does NOT contain a specific substring using the "does not contain" conditional logic operator.
 *
 * Plugin Name:  GF Conditional Logic Operator: "Does Not Contain"
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Adds support for the "does not contain" conditional logic operator in Gravity Forms.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com
 */
class GF_CLO_Does_Not_Contain {

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

	}

	public function output_admin_inline_script() {
		if ( ! GFForms::get_page() && ! is_admin() && ! in_array( rgget( 'page' ), array( 'gp-email-users' ) ) ) {
			return;
		}
		?>
		<script>
			if ( window.gf_vars ) {
				gf_vars['does_not_contain'] = '<?php esc_html_e( 'does not contain' ); ?>';
			}

			gform.addFilter( 'gform_conditional_logic_operators', function( operators ) {
				operators.does_not_contain = 'does not contain';
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

				window.GWCLODoesNotContain = function( args ) {

					var self = this;

					for ( var prop in args ) {
						if ( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {
						gform.addFilter( 'gform_is_value_match', function( isMatch, formId, rule ) {

							if ( rule.operator !== 'does_not_contain' ) {
								return isMatch;
							}

							var fieldValue = $( '#input_' + formId + '_' + rule.fieldId ).val();
							isMatch = fieldValue.indexOf( rule.value ) === -1;

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

		$script = 'new GWCLODoesNotContain();';
		$slug   = implode( '_', array( 'gwclo_does_not_contain', $form['id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	public function whitelist_operator( $is_valid, $operator ) {
		if ( $operator === 'does_not_contain' ) {
			$is_valid = true;
		}
		return $is_valid;
	}

	public function evaluate_operator( $is_match, $field_value, $target_value, $operation, $source_field, $rule ) {

		if ( $rule['operator'] !== 'does_not_contain' || rgar( $rule, 'gwclodncEvaluatingOperator' ) ) {
			return $is_match;
		}

		$rule['gwclodncEvaluatingOperator'] = true;

		// If the field contains the target value, it's not a match.
		$is_match = strpos( $field_value, $target_value ) === false;

		$rule['gwclodncEvaluatingOperator'] = false;

		return $is_match;
	}

	public function convert_conditional_logic_to_field_filters( $field_filters ) {

		foreach ( $field_filters as &$field_filter ) {
			if ( ! is_array( $field_filter ) ) {
				continue;
			}

			switch ( $field_filter['operator'] ) {
				case 'does_not_contain':
					$field_filter['operator'] = 'NOT LIKE';
					$field_filter['value']    = '%' . $field_filter['value'] . '%';
					break;
			}
		}

		return $field_filters;
	}

	public function is_applicable_form( $form ) {
		return GFFormDisplay::has_conditional_logic( $form );
	}
}

# Configuration

new GF_CLO_Does_Not_Contain();
