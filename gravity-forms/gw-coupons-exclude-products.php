<?php
/**
 * Gravity Wiz // Gravity Forms Coupons // Exclude Products from Coupon Discount
 *
 * Exclude specific products when calculating discounts with the Gravity Forms Coupons add-on.
 *
 * Requires Gravity Forms Coupons v1.1
 *
 * @version 1.4
 */
class GW_Coupons_Exclude_Products {

	protected static $is_script_output = false;

	public $_args          = array();
	public $excluded_total = null;

	public function __construct( $args ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'                        => false,
			'exclude_fields'                 => array(),
			'exclude_fields_without_options' => array(),
			'exclude_fields_by_form'         => array(),
			'skip_for_100_percent'           => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	function init() {

		$has_gravity_forms = property_exists( 'GFCommon', 'version' ) && version_compare( GFCommon::$version, '1.8', '>=' );
		$has_gf_coupons    = class_exists( 'GFCoupons' );

		// make sure we're running the required minimum version of Gravity Forms and that GF Coupons is installed
		if ( ! $has_gravity_forms || ! $has_gf_coupons ) {
			return;
		}

		// render
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ) );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );

		// submission
		add_action( 'gform_product_info', array( $this, 'stash_excluded_total' ), 4 /* Coupons run on 5 */, 2 );
		add_filter( 'gform_coupons_discount_amount', array( $this, 'modify_coupon_discount_amount' ), 10, 3 );

	}

	function load_form_script( $form ) {

		if ( $this->is_applicable_form( $form ) && ! self::$is_script_output ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	function output_script() {
		?>

		<script>

			( function( $ ) {

				if( window.gform ) {

					gform.addFilter( 'gform_coupons_discount_amount', function( discount, couponType, couponAmount, price, totalDiscount, formId ) {

						price -= getExcludedAmount( formId );

						if( couponType == 'percentage' ) {
							discount = price * Number( ( couponAmount / 100 ) );
						} else if( couponType == 'flat' ) {
							discount = Number( couponAmount );
							if( discount > price ) {
								discount = price;
							}
						}

						if( ! window.onTimeout && window.applyingCoupon ) {

							window.onTimeout = setTimeout( function() {

								window.onTimeout = false;
								window.applyingCoupon = false;

								$( document ).trigger( 'gform_post_conditional_logic' );

							}, 1 );

						}

						return discount;
					}, 15 );

				}

				function getExcludedAmount( formId ) {

					var excludeFields               = gf_global.gfcep[ formId ].exclude_fields,
						excludeFieldsWithoutOptions = gf_global.gfcep[ formId ].exclude_fields_without_options,
						amount                      = 0;

					if( ! excludeFields && ! excludeFieldsWithoutOptions ) {
						return 0;
					}

					for( var i = 0; i < excludeFields.length; i++ ) {
						var productAmount = gformCalculateProductPrice( formId, excludeFields[ i ] );
						amount += productAmount;
					}

					for( var i = 0; i < excludeFieldsWithoutOptions.length; i++ ) {
						var productAmount = gformCalculateProductPrice( formId, excludeFieldsWithoutOptions[ i ] );
						var price = gformGetBasePrice( formId, excludeFieldsWithoutOptions[ i ] );
						var quantity = gformGetProductQuantity( formId, excludeFieldsWithoutOptions[ i ] );
						amount += ( productAmount - ( price * quantity ) );
					}

					return amount;
				}

			} )( jQuery );

		</script>

		<?php

		self::$is_script_output = true;

	}

	function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$base_json                           = json_encode( array( 'skipFor100Percent' => $this->_args['skip_for_100_percent'] ) );
		$exclude_fields_json                 = json_encode( $this->_args['exclude_fields'] );
		$exclude_fields_without_options_json = json_encode( $this->_args['exclude_fields_without_options'] );

		$script = "if( typeof gf_global != 'undefined' ) {
			if( typeof gf_global.gfcep == 'undefined' ) {
				gf_global.gfcep = {$base_json};
			}
			gf_global.gfcep[ {$this->_args['form_id']} ] = {
				exclude_fields: {$exclude_fields_json},
				exclude_fields_without_options: {$exclude_fields_without_options_json}
			};
		}";

		GFFormDisplay::add_init_script( $this->_args['form_id'], 'gfcep', GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	function stash_excluded_total( $product_data, $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $product_data;
		}

		$this->excluded_total = 0;

		foreach ( $product_data['products'] as $field_id => $data ) {
			if ( in_array( $field_id, $this->_args['exclude_fields'] ) ) {
				$this->excluded_total += GFCommon::to_number( $data['price'] );
			}
			if ( in_array( $field_id, $this->_args['exclude_fields_without_options'] ) ) {
				$this->excluded_total += GFCommon::to_number( $data['price'] );
			}
		}

		return $product_data;
	}

	function modify_coupon_discount_amount( $discount, $coupon, $price ) {

		if ( ! $this->excluded_total ) {
			return $discount;
		}

		$orig_price = $price;
		$price      = $price - $this->excluded_total;
		$currency   = new RGCurrency( GFCommon::get_currency() );
		$amount     = $currency->to_number( $coupon['amount'] );

		if ( $this->_args['exclude_fields_without_options'] ) {
			if ( $coupon['type'] == 'percentage' && ! ( $amount == 100 && $this->_args['skip_for_100_percent'] ) ) {
				$discount = $discount - ( $price * ( $amount / 100 ) );
			} else {
				$discount = $this->excluded_total;
			}
		} elseif ( $coupon['type'] == 'percentage' && ! ( $amount == 100 && $this->_args['skip_for_100_percent'] ) ) {
			$discount = $price * ( $amount / 100 );
		} elseif ( $coupon['type'] == 'flat' ) {
			$discount = $amount;
			if ( $discount > $price ) {
				$discount = $price;
			}
		}

		return $discount;
	}

	function is_applicable_form( $form ) {

		$coupon_fields = GFCommon::get_fields_by_type( $form, array( 'coupon' ) );

		if ( sizeof( $this->_args['exclude_fields_by_form'] ) > 0 ) {
			$is_applicable_form_id = in_array( $form['id'], array_keys( $this->_args['exclude_fields_by_form'] ) );
			if ( $is_applicable_form_id && $form['id'] != $this->_args['form_id'] ) {
				$this->_args['form_id']        = $form['id'];
				$this->_args['exclude_fields'] = $this->_args['exclude_fields_by_form'][ $form['id'] ];
			}
		}

		$is_applicable_form_id = $form['id'] == $this->_args['form_id'];

		return $is_applicable_form_id && ! empty( $coupon_fields );
	}

}

/**
 * Configuration
 * - for a single form, set form_id to your form ID, and exclude_fields to an array of the fields you wish to exclude or use exclude_fields_without_options for fields without options
 * - for multiple forms, set exclude_fields_by_form to an array with form IDs as its keys, and arrays of field IDs as its values
 * - set skip_for_100_percent to true to ignore these exclusions when a 100% off coupon is used
 */

// Single form

new GW_Coupons_Exclude_Products( array(
	'form_id'                        => 123,
	'exclude_fields_without_options' => array( 6 ),
	'skip_for_100_percent'           => false,
) );

// Single form (exclude fields without options)

new GW_Coupons_Exclude_Products( array(
	'form_id'              => 123,
	'exclude_fields'       => array( 4, 5 ),
	'skip_for_100_percent' => false,
) );

// Multiple forms

new GW_Coupons_Exclude_Products( array(
	'exclude_fields_by_form' => array(
		123 => array( 4, 5 ),
		456 => array( 7, 8 ),
	),
	'skip_for_100_percent'   => false,
) );
