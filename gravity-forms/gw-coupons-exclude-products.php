<?php
/**
 * Gravity Wiz // Gravity Forms Coupons // Exclude Products from Coupon Discount
 *
 * Exclude specific products when calculating discounts with the Gravity Forms Coupons add-on.
 *
 * Requires Gravity Forms Coupons v1.1
 *
 * @version 1.2.1
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    http://gravitywiz.com/...
 */
class GW_Coupons_Exclude_Products {

	protected static $is_script_output = false;
	public static $excluded_total = null;

	public $_args = array();

	public function __construct( $args ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'        => false,
			'exclude_fields' => array()
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	function init() {

		$has_gravity_forms = property_exists( 'GFCommon', 'version' ) && version_compare( GFCommon::$version, '1.8', '>=' );
		$has_gf_coupons = class_exists( 'GFCoupons' );

		// make sure we're running the required minimum version of Gravity Forms and GF Coupons
		if( ! $has_gravity_forms || ! $has_gf_coupons ) {
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

		if( $this->is_applicable_form( $form ) && ! self::$is_script_output ) {
			$this->output_script();
		}

		return $form;
	}

	function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				if( window.gform ) {

					gform.addFilter( 'gform_coupons_discount_amount', function( discount, couponType, couponAmount, price, totalDiscount ) {

						// pretty hacky... work our way up the chain to see if the 4th func up is the expected func
						var caller = arguments.callee.caller.caller.caller.caller;

						if( caller.name != 'PopulateDiscountInfo' ) {
							return discount;
						}

						var formId = caller.arguments[1],
							price  = price - getExcludedAmount( formId );

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

					var excludeFields = gf_global.gfcep[ formId ],
						amount        = 0;

					if( ! excludeFields ) {
						return 0;
					}

					for( var i = 0; i < excludeFields.length; i++ ) {
						var productAmount = gformCalculateProductPrice( formId, excludeFields[ i ] );
						amount += productAmount;
					}

					return amount;
				}

			} )( jQuery );

		</script>

		<?php

		self::$is_script_output = true;

	}

	function add_init_script( $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$exclude_fields_json = json_encode( $this->_args['exclude_fields'] );

		$script = "if( typeof gf_global != 'undefined' ) {
			if( typeof gf_global.gwcep == 'undefined' ) {
				gf_global.gfcep = [];
			}
			gf_global.gfcep[ {$this->_args['form_id']} ] = {$exclude_fields_json};
		}";

		GFFormDisplay::add_init_script( $this->_args['form_id'], 'gfcep', GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	function stash_excluded_total( $product_data, $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return $product_data;
		}

		self::$excluded_total = 0;

		foreach( $product_data['products'] as $field_id => $data ) {
			if( in_array( $field_id, $this->_args['exclude_fields'] ) ) {
				self::$excluded_total += GFCommon::to_number( $data['price'] );
			}
		}

		return $product_data;
	}

	function modify_coupon_discount_amount( $discount, $coupon, $price ) {

		if( ! self::$excluded_total ) {
			return $discount;
		}

		$price    = $price - self::$excluded_total;
		$currency = new RGCurrency( GFCommon::get_currency() );
		$amount   = $currency->to_number( $coupon['amount'] );

		if( $coupon['type'] == 'percentage' ) {
			$discount = $price * ( $amount / 100 );
		} else if( $coupon['type'] == 'flat' ) {
			$discount = $amount;
			if( $discount > $price ) {
				$discount = $price;
			}
		}

		return $discount;
	}

	function is_applicable_form( $form ) {

		$coupon_fields         = GFCommon::get_fields_by_type( $form, array( 'coupon' ) );
		$is_applicable_form_id = $form['id'] == $this->_args['form_id'];

		return $is_applicable_form_id && ! empty( $coupon_fields );
	}

}

# Configuration

new GW_Coupons_Exclude_Products( array(
	'form_id' => 123,
	'exclude_fields' => array( 4, 5 )
) );
