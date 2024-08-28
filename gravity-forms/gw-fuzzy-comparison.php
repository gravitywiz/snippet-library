<?php
/**
 * Gravity Wiz // Gravity Forms // Fuzzy Comparison for operator "is"
 *
 * This snippet adds fuzzy comparison for "is" operator with the GF conditional logic rules.
 */
class GW_Fuzzy_Match {

	private $_args;

	public function __construct( $args = array() ) {
		// Set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'   => false,
			'field_id'  => false,
			'threshold' => 1,
		) );

		// Do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		// Add hooks for PHP filtering
		add_filter( 'gform_is_value_match', array( $this, 'apply_fuzzy_match' ), 10, 6 );

		// Add hooks for JS script
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
	}

	public function apply_fuzzy_match( $is_match, $field_value, $target_value, $operation, $source_field, $rule ) {
		if ( $rule['operator'] !== 'is' ) {
			return $is_match;
		}
		return $this->fuzzy_match( $field_value, trim( $target_value ) );
	}

	public function fuzzy_match( $input, $target ) {
		$normalized_input  = $this->normalize_string( $input );
		$normalized_target = $this->normalize_string( $target );
		$distance          = levenshtein( $normalized_input, $normalized_target );
		return $distance <= $this->_args['threshold'];
	}

	public function normalize_string( $str ) {
		$str = iconv( 'UTF-8', 'ASCII//TRANSLIT', $str );
		return strtolower( preg_replace( '/[^a-zA-Z0-9]/', '', $str ) );
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

				window.GW_Fuzzy_Match = function( args ) {
					var self = this;

					// Copy all args to current object: (list expected props)
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
					};

					self.init = function() {
						gform.addFilter('gform_is_value_match', function (isMatch, formId, rule) {
							if ( rule.operator !== 'is' ) {
								return isMatch;
							}
							var source = jQuery( '#input_' + formId + '_' + rule.fieldId );
							if ( source ) {
								return self.fuzzyMatch( source.val(), rule.value );
							}
							return isMatch;
						});
					};

					self.normalizeString = function( str ) {
						return str.normalize("NFD").replace( /[\u0300-\u036f]/g, "" ).toLowerCase();
					};

					self.levenshtein = function( a, b ) {
						const an = a.length;
						const bn = b.length;
						if (an === 0) return bn;
						if (bn === 0) return an;
						const matrix = [];
						for (let i = 0; i <= bn; i++) {
							matrix[i] = [i];
						}
						for (let j = 0; j <= an; j++) {
							matrix[0][j] = j;
						}
						for (let i = 1; i <= bn; i++) {
							for (let j = 1; j <= an; j++) {
								if (b.charAt(i - 1) === a.charAt(j - 1)) {
									matrix[i][j] = matrix[i - 1][j - 1];
								} else {
									matrix[i][j] = Math.min(
										matrix[i - 1][j - 1] + 1,
										Math.min(
											matrix[i][j - 1] + 1,
											matrix[i - 1][j] + 1
										)
									);
								}
							}
						}
						return matrix[bn][an];
					};

					self.fuzzyMatch = function( input, target ) {
						const normalizedInput  = self.normalizeString( input );
						const normalizedTarget = self.normalizeString( target );
						const distance         = self.levenshtein( normalizedInput, normalizedTarget );
						return distance <= self.threshold;
					};

					self.init();
				};

			} )( jQuery );
		</script>
		<?php
	}

	public function add_init_script( $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'formId'    => $this->_args['form_id'],
			'fieldId'   => $this->_args['field_id'],
			'threshold' => $this->_args['threshold'],
		);

		$script = 'new GW_Fuzzy_Match( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( strtolower( __CLASS__ ), $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	public function is_applicable_form( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;
		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}
}

# Configuration
new GW_Fuzzy_Match( array(
	'form_id'   => 1,  // Replace with your form ID.
	'field_id'  => 2,  // Replace with your field ID for which conditional logic needs to be fuzzy.
	'threshold' => 1,  // Define your threshold - It is the maximum number of allowed differences between two strings for them to be considered similar.
) );
