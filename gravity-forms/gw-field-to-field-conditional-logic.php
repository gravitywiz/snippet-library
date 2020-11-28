<?php
/**
 * ---------------------------------------
 * DO NOT USE. THIS IS A WORK IN PROGRESS.
 * ---------------------------------------
 *
 * Gravity Wiz // Gravity Forms // Field to Field Conditional Logic
 *
 * Compare fields in Gravity Forms conditional logic. Is Field A greater than Field B? Is the emergency contact's name different than
 * the patient's? Is Date A after Date B?
 *
 * Plugin Name:  GF Field to Field Conditional Logic
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Compare fields in Gravity Forms conditional logic.
 * Author:       Gravity Wiz
 * Version:      0.9
 * Author URI:   http://gravitywiz.com
 *
 * @todo
 *  - Add UI for selecting merge tags.
 *  - Add support for CL on next/prev buttons.
 */
class GF_Field_To_Field_Conditional_Logic {

	public function __construct() {

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_admin_pre_render', array( $this, 'enqueue_inline_admin_script' ) );

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
		add_filter( 'gform_rule_pre_evaluation', array( $this, 'modify_rule' ), 10, 5 );

	}

	public function enqueue_inline_admin_script( $return ) {
		add_filter( 'admin_footer', array( $this, 'output_admin_inline_script' ) );
		return $return;
	}

	public function output_admin_inline_script() {
		?>

		<script>
			gform.addFilter( 'gform_conditional_logic_values_input', function( markup, objectType, ruleIndex, selectedFieldId, selectedValue ) {
				var selectedField = GetFieldById( selectedFieldId );
				if ( ! selectedField || ! selectedField.choices.length ) {
					return markup;
				}
				var matches = markup.matchAll( /(<select.+?>)(.+?)(<\/select>)/g );
				for ( var match of matches ) {

					var choiceOptions = match[2];
					var fieldOptions  = [];

					for ( var field of window.form.fields ) {
						if ( ! IsConditionalLogicField( field ) ) {
							continue;
						}
						var value = '{:' + field.id + ':value}';
						var isSelected = value === selectedValue;
						fieldOptions.push( '<option value="{0}" {2}>{1}</option>'.format( value, GetLabel( field ), isSelected ? 'selected' : '' ) );
						if ( isSelected ) {
							var $choiceSelect = jQuery( '<select>' + choiceOptions + '</select>' );
							$choiceSelect.find( 'option:selected' ).remove();
							choiceOptions = $choiceSelect.html();
							$choiceSelect.remove();
						}
					}

					markup = match[1] + '<optgroup label="Field Choices">' + choiceOptions + '</optgroup><optgroup label="Fields">' + fieldOptions.join( "\n" ) + '</optgroup>' + match[3];

				}
				return markup;
			}, 9 );
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

				window.GWFieldToFieldConditionalLogic = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						// Let GF know that when are target field's value changes, we need to re-evaluate conditional logic.
						for ( var prop in self.dependencies ) {
							if( self.dependencies.hasOwnProperty( prop ) ) {
								if ( typeof window.gf_form_conditional_logic[ self.formId ].fields[ prop ] === 'undefined' ) {
									window.gf_form_conditional_logic[ self.formId ].fields[ prop ] = [];
								}
								window.gf_form_conditional_logic[ self.formId ].fields[ prop ] = window.gf_form_conditional_logic[ self.formId ].fields[ prop ].concat( self.dependencies[ prop ] );
							}
						}

						// Replace the field merge tag in the rule value before the rule is evaluated.
						gform.addFilter( 'gform_rule_pre_evaluation', function( rule, formId ) {

							var mergeTags = GFMergeTag.parseMergeTags( rule.value );
							if ( ! mergeTags.length ) {
								return rule;
							}

							rule.value = GFMergeTag.getMergeTagValue( formId, mergeTags[0][1], mergeTags[0][3] );

							return rule;
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

		$args = array(
			'formId'       => $form['id'],
			'dependencies' => $this->get_cl_dependencies( $form ),
		);

		$script = 'new GWFieldToFieldConditionalLogic( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gw_field_to_field_conditional_logic', $form['id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function get_cl_dependencies( $object, $dependencies = array() ) {

		foreach ( $object as $prop => $value ) {
			if ( $prop === 'conditionalLogic' && ! empty( $value ) ) {
				foreach ( $object[ $prop ]['rules'] as $rule ) {
					// GF core only supports comparing fields to values but Gravity Perks supports other comparisons.
					if ( ! is_numeric( $rule['fieldId'] ) ) {
						continue;
					}
					$matches = $this->parse_merge_tags( $rule['value'] );
					if ( ! empty( $matches ) ) {
						$tag_field_id = $matches[0][1];
						if ( ! isset( $dependencies[ $tag_field_id ] ) ) {
							$dependencies[ $tag_field_id ] = array();
						}
						// Submit button conditional logic is 0.
						array_push( $dependencies[ $tag_field_id ], rgar( $object, 'id', 0 ) );
					}
				}
			} elseif ( is_array( $value ) || is_a( $value, 'GF_Field' ) ) {
				$dependencies = $this->get_cl_dependencies( $value, $dependencies );
			}
		}

		return $dependencies;
	}

	public function parse_merge_tags( $string, $pattern = '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi' ) {
		preg_match_all( $pattern, $string, $matches, PREG_SET_ORDER );
		return $matches;
	}

	public function is_applicable_form( $form ) {
		// @todo we will need to recursively search for field-to-field conditional logic (see GPCLD).
		return true;
	}

	/**
	 * Parse merge tags in rule values on submission (and other times conditional logic is evaluated).
	 *
	 * @param $rule
	 * @param $form
	 * @param $logic
	 * @param $field_values
	 * @param $entry
	 *
	 * @return mixed
	 */
	public function modify_rule( $rule, $form, $logic, $field_values, $entry ) {

		static $_is_modifying_rule;
		static $_rule_cache;

		if ( $_is_modifying_rule ) {
			return $rule;
		}

		if ( $entry === null ) {
			$_is_modifying_rule = true;
			$entry              = GFFormsModel::get_current_lead();
			$_is_modifying_rule = false;
		}

		if ( ! isset( $_rule_cache[ $entry['id'] ] ) ) {
			$_rule_cache[ $entry['id'] ] = array();
		}

		if ( isset( $_rule_cache[ $entry['id'] ][ $rule['value'] ] ) ) {
			$value = $_rule_cache[ $entry['id'] ][ $rule['value'] ];
		} else {
			$value = GFCommon::replace_variables( $rule['value'], $form, $entry );
			$_rule_cache[ $entry['id'] ][ $rule['value'] ] = $value;
		}

		$rule['value'] = $value;

		return $rule;
	}

}

# Configuration

new GF_Field_To_Field_Conditional_Logic();
