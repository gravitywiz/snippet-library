<?php
/**
 * Gravity Wiz // Gravity Forms // Create Coupons with Gravity Forms for Gravity Forms, WooCommerce, or Easy Digital Downloads
 *
 * Create coupons via Gravity Forms submissions. Map the coupon code to a field on the GF form and voila!
 *
 * @version 1.2.3
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    WooCommerce:   http://gravitywiz.com/creating-coupons-woocommerce-gravity-forms/
 * @link    Gravity Forms: http://gravitywiz.com/creating-coupons-for-gf-coupons-add-on-with-gravity-forms/
 */
class GW_Create_Coupon {

	var $_args;

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'         => false,
			'source_field_id' => false,
			'name_field_id'   => false,
			'plugin'          => 'gf', // accepts: 'gf', 'wc', 'edd'
			'amount'          => 0,
			'type'            => '', // accepts: 'fixed_cart', 'percent', 'fixed_product', 'percent_product'
			'meta'            => array(),
			'required_fields' => array(),
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms and that WooCommerce is active
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		add_action( 'gform_after_submission', array( $this, 'create_coupon' ), 10, 2 );

	}

	public function create_coupon( $entry, $form ) {

		if ( ! $this->is_applicable_form( $form ) || $this->should_abort( $entry ) ) {
			return;
		}

		$coupon_codes = $this->get_coupon_codes( $entry, $this->_args['source_field_id'] );

		foreach ( $coupon_codes as $coupon_code ) {

			if ( $this->_args['name_field_id'] === false ) {
				$coupon_name = $coupon_code;
			} else {
				$coupon_name = rgar( $entry, $this->_args['name_field_id'] );
				$coupon_name = $coupon_name === '' ? $coupon_code : $coupon_name;
			}

			$amount = $this->_args['amount'];
			$type   = $this->_args['type'];

			if ( is_callable( $amount ) ) {
				$amount = call_user_func( $amount );
			}

			$plugin_func = array( $this, sprintf( 'create_coupon_%s', $this->_args['plugin'] ) );

			if ( is_callable( $plugin_func ) ) {
				call_user_func( $plugin_func, $coupon_name, $coupon_code, $amount, $type, $entry, $form );
			}
		}

	}

	public function create_coupon_edd( $coupon_name, $coupon_code, $amount, $type, $entry, $form ) {

		if ( ! is_callable( 'edd_store_discount' ) ) {
			return;
		}

		$meta = wp_parse_args( $this->_args['meta'], array(
			'name'              => $coupon_name,
			'code'              => $coupon_code,
			'type'              => $type,
			'amount'            => $amount,
			'excluded_products' => array(),
			'expiration'        => '',
			'is_not_global'     => false,
			'is_single_use'     => false,
			'max_uses'          => '',
			'min_price'         => '',
			'product_condition' => '',
			'product_reqs'      => array(),
			'start'             => '',
			'uses'              => '',
		) );

		// EDD will set it's own defaults in the edd_store_discount() so let's filter out our own empty defaults (they're just here for easier reference)
		$meta = array_filter( $meta );

		// EDD takes a $details array which has some different keys than the meta, let's map the keys to the expected format
		$edd_post_keys = array(
			'max_uses'          => 'max',
			'product_reqs'      => 'products',
			'excluded_products' => 'excluded-products',
			'is_not_global'     => 'not_global',
			'is_single_use'     => 'use_once',
		);

		foreach ( $meta as $key => $value ) {
			$mod_key = rgar( $edd_post_keys, $key );
			if ( $mod_key ) {
				$meta[ $mod_key ] = $value;
			}
		}

		edd_store_discount( $meta );

	}

	public function create_coupon_gf( $coupon_name, $coupon_code, $amount, $type, $entry, $form ) {

		if ( ! class_exists( 'GFCoupons' ) ) {
			return;
		}

		// hack to load GF Coupons data.php file
		if ( is_callable( 'gf_coupons' ) ) {
			gf_coupons()->get_config( array( 'id' => 0 ), false );
		} else {
			/** @noinspection PhpDynamicAsStaticMethodCallInspection */
			GFCoupons::get_config( array( 'id' => 0 ), false );
		}

		$meta = wp_parse_args( $this->_args['meta'], array(
			'form_id'           => false,
			'coupon_name'       => $coupon_name,
			'coupon_code'       => strtoupper( $coupon_code ),
			'coupon_type'       => $type, // 'flat', 'percentage'
			'coupon_amount'     => $amount,
			'coupon_start'      => '', // MM/DD/YYYY
			'coupon_expiration' => '', // MM/DD/YYYY
			'coupon_limit'      => false,
			'coupon_stackable'  => false,
		) );

		$form_id = $meta['form_id'] ? $meta['form_id'] : 0;
		unset( $meta['form_id'] );

		foreach ( $meta as $key => $value ) {
			if ( $value instanceof Closure && is_callable( $value ) ) {
				$meta[ $key ] = call_user_func( $value, $entry, $form, $this );
			}
		}

		if ( is_callable( 'gf_coupons' ) ) {
			$meta['gravityForm']      = $form_id ? $form_id : 0;
			$meta['couponName']       = $meta['coupon_name'];
			$meta['couponCode']       = $meta['coupon_code'];
			$meta['couponAmountType'] = $meta['coupon_type'];
			$meta['couponAmount']     = $meta['coupon_amount'];
			$meta['startDate']        = $meta['coupon_start'];
			$meta['endDate']          = $meta['coupon_expiration'];
			$meta['usageLimit']       = $meta['coupon_limit'];
			$meta['isStackable']      = $meta['coupon_stackable'];
			$meta['usageCount']       = 0;
			gf_coupons()->insert_feed( $form_id, true, $meta );
		} else {
			/** @noinspection PhpUndefinedClassInspection */
			GFCouponsData::update_feed( 0, $form_id, true, $meta );
		}

	}

	/**
	 * Create a WooCommerce coupon.
	 *
	 * @link https://gist.github.com/mikejolley/3969579#file-gistfile1-txt
	 */
	public function create_coupon_wc( $coupon_name, $coupon_code, $amount, $type, $entry, $form ) {

		$start_date = rgar( $this->_args['meta'], 'start_date' );
		if ( $start_date === '' || ! strtotime( $start_date ) ) {
			$date       = current_datetime();
			$start_date = $date->format( 'Y-m-d H:i:s' );
		}

		// WooCommerce coupon uses the Post Title as the coupon code hence $coupon_code is assigned to Post Title and $coupon_name is assigned to the Post Excerpt
		$coupon = array(
			'post_title'   => $coupon_code,
			'post_excerpt' => $coupon_name,
			'post_status'  => 'publish',
			'post_author'  => 1,
			'post_type'    => 'shop_coupon',
			'post_date'    => $start_date,
		);

		$new_coupon_id = wp_insert_post( $coupon );

		$meta = wp_parse_args( $this->_args['meta'], array(
			'discount_type'              => $type,
			'coupon_amount'              => $amount,
			'individual_use'             => 'yes',
			'product_ids'                => '',
			'exclude_product_ids'        => '',
			'usage_limit'                => '1',
			'expiry_date'                => '',
			'apply_before_tax'           => 'no',
			'free_shipping'              => 'no',
			'exclude_sale_items'         => 'no',
			'product_categories'         => '',
			'exclude_product_categories' => '',
			'minimum_amount'             => '',
			'customer_email'             => '',
		) );

		foreach ( $meta as $meta_key => $meta_value ) {
			if ( $meta_value instanceof Closure && is_callable( $meta_value ) ) {
				$meta[ $meta_key ] = call_user_func( $meta_value, $entry, $form, $this );
			}
			update_post_meta( $new_coupon_id, $meta_key, $meta[ $meta_key ] );
		}

	}

	public function get_coupon_codes( $entry, $source_field_id ) {
		return array_filter( explode( "\n", rgar( $entry, $source_field_id ) ) );
	}

	function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return (int) $form_id === (int) $this->_args['form_id'];
	}

	public function should_abort( $entry ) {
		if ( empty( $this->_args['required_fields'] ) ) {
			return false;
		}

		foreach ( $this->_args['required_fields'] as $field_id ) {
			$value = rgar( $entry, (string) $field_id );

			if ( rgblank( $value ) ) {
				return true;
			}
		}

		return false;
	}

}

# Configuration

new GW_Create_Coupon( array(
	// ID of the form which will be used to create coupons
	'form_id'         => 608,
	// ID of the field whose value will be used as the coupon code
	'source_field_id' => 1,
	// ID of the field whose value will be used as the title of the coupon
	'name_field_id'   => 2,
	// which plugin the coupon should be created for (i.e. WooCommerce = 'wc')
	'plugin'          => '', // accepts: 'gf', 'wc', 'edd'
	// type of coupon code to be created, available types will differ depending on the plugin
	'type'            => '',
	// amount of the coupon discount
	'amount'          => 10,
	// The IDs of the fields that must not be empty, else the coupon won't be created
	'required_fields' => array( 1, 2, 1.3 ),
) );
