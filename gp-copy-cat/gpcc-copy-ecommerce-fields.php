<?php
/**
 * Gravity Wiz // GP Copy Cat + GP eCommerce Fields // Copy eCommerce Fields
 * https://gravitywiz.com/
 *
 * Copies values from GP Copy Cat source fields to GP eCommerce Fields (Discount/Tax) and keeps them in sync.
 *
 * Plugin Name:  GP Copy Cat - Copy eCommerce Fields
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Copies values from GP Copy Cat source fields to GP eCommerce Fields (Discount/Tax) and keeps them in sync.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
class GPCC_Copy_ECommerce_Fields {

	private $_args = array();

	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args( $args, array(
			'form_id' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gform_pre_validation', array( $this, 'sync_dynamic_ecommerce_amounts' ) );
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
	}

	public function sync_dynamic_ecommerce_amounts( $form ) {
		if ( ! $this->is_applicable_form( $form ) || empty( $form['fields'] ) ) {
			return $form;
		}

		$copy_map = $this->get_copycat_source_map( $form );

		foreach ( $form['fields'] as &$field ) {
			if ( ! in_array( $field->type, array( 'discount', 'tax' ), true ) ) {
				continue;
			}

			if ( ! isset( $copy_map[ $field->id ] ) ) {
				continue;
			}

			$input = $this->get_posted_input_value( $copy_map[ $field->id ] );
			if ( $input === null || $input === '' ) {
				continue;
			}

			$amount_prop      = $field->type . 'Amount';
			$amount_type_prop = $field->type . 'AmountType';
			$amount           = $this->parse_amount( $input, $is_percent );

			$field->{$amount_prop} = $amount;
			if ( $is_percent ) {
				$field->{$amount_type_prop} = 'percent';
			}
		}

		return $form;
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

				window.GPCC_Copy_ECommerce_Fields = function( args ) {
					var self = this;

					for ( var prop in args ) {
						if ( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.getCopyMap = function( $wrapper ) {
						var map = {};
						var regex = /copy-([0-9]+(?:\.[0-9]+)*)-to-([0-9]+(?:\.[0-9]+)*)/g;

						$wrapper.find( '.gfield' ).each( function() {
							var className = this.className || '';
							var match;
							while ( ( match = regex.exec( className ) ) ) {
								map[ match[2] ] = match[1];
							}
							regex.lastIndex = 0;
						} );

						return map;
					};

					self.getInputValue = function( $form, fieldId ) {
						var inputName = 'input_' + fieldId.replace( '.', '_' );
						var $inputs = $form.find( '[name="' + inputName + '"]' );
						if ( ! $inputs.length ) {
							return '';
						}

						if ( $inputs.is( ':checkbox, :radio' ) ) {
							var $checked = $inputs.filter( ':checked' );
							return $checked.length ? $checked.val() : '';
						}

						return $inputs.val();
					};

					self.updateTarget = function( $form, targetId, sourceValue ) {
						var $target = $form.find( '#input_' + self.formId + '_' + targetId );
						if ( ! $target.length ) {
							return;
						}

						var rawValue = sourceValue || '';
						var isPercent = typeof rawValue === 'string' && rawValue.indexOf( '%' ) !== -1;
						var cleaned = isPercent ? rawValue.replace( /%/g, '' ) : rawValue;
						var amount = typeof gformCleanNumber === 'function' ? gformCleanNumber( cleaned ) : cleaned;
						if ( isNaN( amount ) ) {
							amount = 0;
						}

						$target.attr( 'data-amount', amount ).data( 'amount', amount );
						if ( isPercent ) {
							$target.attr( 'data-amounttype', 'percent' ).data( 'amounttype', 'percent' );
						}

						if ( typeof gformCalculateTotalPrice === 'function' ) {
							gformCalculateTotalPrice( self.formId );
						}
					};

					self.init = function() {
						var $wrapper = $( '#gform_wrapper_' + self.formId );
						var $form    = $( '#gform_' + self.formId );
						if ( ! $wrapper.length || ! $form.length ) {
							return;
						}

						var map = self.getCopyMap( $wrapper );

						// Initial sync.
						$.each( map, function( targetId, sourceId ) {
							var value = self.getInputValue( $form, sourceId );
							self.updateTarget( $form, targetId, value );
						} );

						// Update on source input changes.
						$form.on( 'change.gwgpccgpecf input.gwgpccgpecf', ':input[name^="input_"]', function() {
							var inputName = $( this ).attr( 'name' );
							var sourceId = inputName.replace( /^input_/, '' ).replace( '_', '.' );
							$.each( map, function( targetId, mappedSourceId ) {
								if ( mappedSourceId == sourceId ) {
									var value = self.getInputValue( $form, mappedSourceId );
									self.updateTarget( $form, targetId, value );
								}
							} );
						} );
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
			'formId' => $this->_args['form_id'] ? $this->_args['form_id'] : $form['id'],
		);

		$script = 'new GPCC_Copy_ECommerce_Fields( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'GPCC_Copy_ECommerce_Fields', $form['id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	public function is_applicable_form( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;
		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

	private function get_copycat_source_map( $form ) {
		$map = array();

		foreach ( $form['fields'] as $field ) {
			if ( empty( $field->cssClass ) ) {
				continue;
			}

			if ( preg_match_all( '/copy-([0-9]+(?:\\.[0-9]+)*)-to-([0-9]+(?:\\.[0-9]+)*)/', $field->cssClass, $matches, PREG_SET_ORDER ) ) {
				foreach ( $matches as $match ) {
					$map[ $match[2] ] = $match[1];
				}
			}
		}

		return $map;
	}

	private function get_posted_input_value( $field_id ) {
		$input_name = 'input_' . str_replace( '.', '_', $field_id );
		$value      = rgpost( $input_name );

		if ( $value === null || $value === '' ) {
			return null;
		}

		return $value;
	}

	private function parse_amount( $input, &$is_percent ) {
		$is_percent = is_string( $input ) && strpos( $input, '%' ) !== false;
		if ( $is_percent ) {
			$input = str_replace( '%', '', $input );
		}

		$amount = GFCommon::to_number( $input );
		if ( $amount < 0 ) {
			$amount = abs( $amount );
		}

		return $amount;
	}
}

# Configuration

new GPCC_Copy_ECommerce_Fields();
