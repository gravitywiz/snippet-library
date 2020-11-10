<?php
/**
 * Gravity Wiz // Gravity Forms // Better Inventory with Gravity Forms
 *
 * Implements the concept of "inventory" with Gravity Forms by allowing the specification of a limit determined by the
 * sum of a specific field, typically a quantity field.
 *
 * @version   2.19
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/better-inventory-with-gravity-forms/
 */
class GW_Inventory {

	public $_args;

	public function __construct( $args ) {

		$this->_args = $this->parse_args( $args );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		add_filter( "gform_pre_render_{$this->_args['form_id']}", array( $this, 'limit_by_field_values' ) );
		add_filter( "gform_validation_{$this->_args['form_id']}", array( $this, 'limit_by_field_values_validation' ) );

		// check 'field_group' for date fields; if found, limit based on exhausted inventory days.
		if( ! empty( $this->_args['field_group'] ) ) {
			add_filter( "gpld_limit_dates_options_{$this->_args['form_id']}", array( $this, 'limit_date_fields' ), 10, 2 );
		}

		// add 'sum' action for [gravityforms] shortcode
		add_filter( 'gform_shortcode_sum',       array( $this, 'shortcode_sum' ), 10, 2 );
		add_filter( 'gform_shortcode_remaining', array( $this, 'shortcode_remaining' ), 10, 2 );

		add_action( 'gwinv_before_get_sum', array( $this, 'before_get_sum' ) );
		add_action( 'gwinv_after_get_sum', array( $this, 'after_get_sum' ) );

		add_filter( 'gform_product_info', array( $this, 'handle_calculated_product_fields' ), 10, 3 );

		if( $this->_args['enable_notifications'] ) {
			$this->enable_notifications();
		}

	}

	public function parse_args( $args ) {

		$args = wp_parse_args( $args, array(
			'form_id'                  => false,
			'field_id'                 => false,
			'input_id'                 => false,
			'stock_qty'                => false,
			'out_of_stock_message'     => __( 'Sorry, this item is out of stock.' ),
			'not_enough_stock_message' => __( 'You ordered %1$s of this item but there are only %2$s of this item left.' ),
			'approved_payments_only'   => false,
			'hide_form'                => false,
			'enable_notifications'     => false,
			'field_group'              => array()
		) );

		/**
		 * @var $stock_qty
		 * @var $field_group
		 */
		extract( $args );

		if( ! $stock_qty && isset( $limit ) ) {
			$args['stock_qty'] = $limit;
			unset( $args['limit'] );
		}

		if( isset( $limit_message ) ) {
			$args['out_of_stock_message'] = $limit_message;
			unset( $args['limit_message'] );
		}

		if( isset( $validation_message ) ) {
			$args['not_enough_stock_message'] = $validation_message;
			unset( $args['validation_message'] );
		}

		if( ! $args['input_id'] ) {
			$args['input_id'] = $args['field_id'];
			unset( $args['field_id'] );
		}

		if( $field_group && ! is_array( $field_group ) ) {
			$args['field_group'] = array( $field_group );
		}

		return $args;
	}

	public function enable_notifications() {

		if( ! class_exists( 'GW_Notification_Event' ) ) {

			_doing_it_wrong( 'GW_Inventory::$enable_notifications', __( 'Inventory notifications require the \'GW_Notification_Event\' class.' ), '1.0' );

		} else {

			$event_slug = "gwinv_out_of_stock_{$this->_args['input_id']}";
			$event_name = GFForms::get_page() == 'notification_edit' ? $this->get_notification_event_name() : __( 'Event name is only populated on Notification Edit view; saves a DB call to get the form on every ' );

			$this->_notification_event = new GW_Notification_Event( array(
				'form_id'    => $this->_args['form_id'],
				'event_name' => $event_name,
				'event_slug' => $event_slug,
				'trigger'    => array( $this, 'notification_event_listener' )
			) );

		}

	}

	public function limit_by_field_values( $form ) {

		if( $this->is_in_stock() ) {
			return $form;
		}

		if( $this->_args['hide_form'] ) {
			add_filter( "gform_get_form_filter_{$form['id']}", array( $this, 'get_out_of_stock_message' ) );
		} else if( empty( $this->_args['field_group'] ) ) {
			add_filter( 'gform_field_input', array( $this, 'hide_field' ), 10, 2 );
		}

		return $form;
	}

	public function limit_by_field_values_validation( $validation_result ) {

		$input_id           = $this->_args['input_id'];
		$limit              = $this->get_stock_quantity();
		$validation_message = $this->_args['not_enough_stock_message'];

		$form = $validation_result['form'];
		$exceeded_limit = false;

		foreach( $form['fields'] as &$field ) {

			if( $field['id'] != intval( $input_id ) ) {
				continue;
			}

			$requested_qty = rgpost( 'input_' . str_replace( '.', '_', $input_id ) );
			$field_sum = $this->get_sum();

			if( rgblank( $requested_qty ) || $field_sum + $requested_qty <= $limit ) {
				continue;
			}

			$exceeded_limit = true;
			$stock_left     = $limit - $field_sum >= 0 ? $limit - $field_sum : 0;

			$field['failed_validation'] = true;
			$field['validation_message'] = sprintf( $validation_message, $requested_qty, $stock_left );

		}

		if( $exceeded_limit && ! empty( $this->_args['field_group'] ) ) {
			foreach( $form['fields'] as &$field ) {
				if( in_array( $field->id, $this->_args['field_group'] ) ) {
					$field['failed_validation'] = true;
					$field['validation_message'] = sprintf( $validation_message, $requested_qty, $stock_left );
				}
			}
		}

		$validation_result['form'] = $form;
		$validation_result['is_valid'] = ! $validation_result['is_valid'] ? false : ! $exceeded_limit;

		return $validation_result;
	}

	public function limit_by_field_group( $query, $form_id, $input_id ) {
		global $wpdb;

		if( $input_id != $this->_args['input_id'] ) {
			return $query;
		}

		$form = GFAPI::get_form( $form_id );
		$join = $where = array();

		foreach( $this->_args['field_group'] as $index => $field_id ) {

			$field = GFFormsModel::get_field( $form, $field_id );
			$alias = sprintf( 'fgld%d', $index + 1 );

			// Fetch entry from submission if available. Otherwise, get default/dynpop value.
			if( rgpost( 'gform_submit' ) == $form_id ) {
				$value = $field->get_value_save_entry( GFFormsModel::get_field_value( $field ), $form, null, null, null );
			} else {
				$value = $field->get_value_default_if_empty( GFFormsModel::get_parameter_value( $field->inputName, array(), $field ) );
			}

			$join[] = "\nINNER JOIN {$wpdb->prefix}gf_entry_meta {$alias} ON em.entry_id = {$alias}.entry_id";
			$where[] = $wpdb->prepare( "CAST( {$alias}.meta_key as unsigned ) = %d AND {$alias}.meta_value = %s ", $field_id, $value );

		}

		$query['join']  .= implode( "\n", $join );
		$query['where'] .= sprintf( "\n AND %s", implode( "\nAND ", $where ) );

		return $query;
	}

	public function limit_date_fields( $options, $form ) {
		global $wpdb;

		foreach( $form['fields'] as $field ) {

			if( ! in_array( $field->id, $this->_args['field_group'] ) || $field->get_input_type() != 'date' || $field->dateType != 'datepicker' ) {
				continue;
			}

			$query = self::get_sum_query( $field->formId, $this->_args['input_id'] );

			if( $this->_args['approved_payments_only'] ) {
				$query = $this->limit_by_approved_payments_only( $query );
			}

			if( ! empty( $this->_args['field_group'] ) ) {

				// add our Date field to the front of the array so we can reliably target it when replacing the queries below
				array_unshift( $this->_args['field_group'], $field->id );
				$this->_args['field_group'] = array_unique( $this->_args['field_group'] );

				$query = $this->limit_by_field_group( $query, $field->formId, $this->_args['input_id'] );

			}

			$regex = sprintf( '/(CAST\( fgld1.meta_key as unsigned \) = %d) AND fgld1.meta_value = \'(?:[\w\d]*)\'/', $field->id );
			preg_match( $regex , $query['where'], $match );
			if( ! empty( $match ) ) {
				list( $search, $replace ) = $match;
				$query['where'] = str_replace( $search, $replace, $query['where'] );
			}

			$query['select']   = 'SELECT sum( em.meta_value ) as total, fgld1.meta_value as date';
			$query['group_by'] = 'GROUP BY date';
			$query['having']   = sprintf( 'HAVING total >= %d', $this->get_stock_quantity() );

			$sql     = implode( "\n", $query );
			$results = $wpdb->get_results( $sql );

			foreach( $results as $result ) {
				$options[ $field->id ]['exceptionsMode'] = 'disable';
				if ( ! is_array( $options[ $field->id ]['exceptions'] ) ) {
					$options[ $field->id ]['exceptions'] = array();
				}
				$options[ $field->id ]['exceptions'][] = date( 'm/d/Y', strtotime( $result->date ) );
			}

		}

		return $options;
	}

	public function get_stock_quantity() {

		$stock = $this->_args['stock_qty'];

		if( is_callable( $stock ) ) {
			$stock = call_user_func( $stock );
		}

		return $stock;
	}

	public function is_in_stock() {
		$count = $this->get_sum();//self::get_field_values_sum( $this->_args['form_id'], $this->_args['input_id'] );
		return $count < $this->get_stock_quantity();
	}

	public function hide_field( $field_content, $field ) {

		if( $field['id'] == intval( $this->_args['input_id'] ) )  {

			$quantity_input = '';
			// GF will default to a quantity of 1 if it can't find the input for a Quantity field.
			if ( $field->type === 'quantity' ) {
				$quantity_input = sprintf( '<input type="hidden" name="input_%d_%d" value="0" /></div>', $field->formId, $field->id );
			}

			return sprintf( '<div class="ginput_container">%s%s</div>', $this->_args['out_of_stock_message'], $quantity_input );
		}

		return $field_content;
	}

	public function notification_event_listener() {

		// really is no better hook to use to send custom notifications just yet
		add_filter( "gform_confirmation_{$this->_args['form_id']}", array( $this, 'send_out_of_stock_notifications' ), 10, 3 );

	}

	public function send_out_of_stock_notifications( $return, $form, $entry ) {

		// if product is still in stock or the entry is spam, don't sent notification
		if( $this->is_in_stock() || $entry['status'] == 'spam' )
			return $return;

		// if product is out of stock and no qty of the product is in current order, assume that out of stock notifications have already been sent
		$requested_qty = intval( rgar( $entry, (string) $this->_args['input_id'] ) );
		if( $requested_qty <= 0 )
			return $return;

		$this->_notification_event->send_notifications( $this->_notification_event->get_event_slug(), $form, $entry );

		return $return;
	}

	public function get_notification_event_name() {

		$form = GFAPI::get_form( $this->_args['form_id'] );
		$field = GFFormsModel::get_field( $form, $this->_args['input_id'] );

		$event_name = sprintf( __( '%s: Out of Stock' ), GFCommon::get_label( $field ) );

		return $event_name;
	}

	public function shortcode_sum( $output, $atts ) {

		$atts = shortcode_atts( array(
			'id' => false,
			'input_id' => false
		), $atts );

		/**
		 * @var $id
		 * @var $input_id
		 */
		extract( $atts ); // gives us $id, $input_id

		return intval( self::get_field_values_sum( $id, $input_id ) );
	}

	public function shortcode_remaining( $output, $atts ) {

		/**
		 * @var $id
		 * @var $input_id
		 * @var $limit
		 */
		$atts = shortcode_atts( array(
			'id'       => false,
			'input_id' => false,
			'limit'    => false,
		), $atts );

		extract( $atts ); // gives us $id, $input_id

		if( $input_id == $this->_args['input_id'] && $id == $this->_args['form_id'] ) {
			$limit     = $limit ? $limit : $this->get_stock_quantity();
			$remaining = $limit - intval( $this->get_sum() );
			$output    = max( 0, $remaining );
		}

		return $output;
	}

	public function get_sum() {

		if( $this->_args['approved_payments_only'] ) {
			add_filter( 'gwinv_query', array( $this, 'limit_by_approved_payments_only' ) );
		}

		if( ! empty( $this->_args['field_group'] ) ) {
			add_filter( 'gwinv_query', array( $this, 'limit_by_field_group' ), 10, 3 );
		}

		$sum = self::get_field_values_sum( $this->_args['form_id'], $this->_args['input_id'] );

		remove_filter( 'gwinv_query', array( $this, 'limit_by_approved_payments_only' ) );
		remove_filter( 'gwinv_query', array( $this, 'limit_by_field_group' ) );

		return $sum;
	}

	public function limit_by_approved_payments_only( $query ) {
		$valid_statuses = array( 'Approved' /* old */, 'Paid', 'Active' );
		$query['where'] .= sprintf( ' AND ( e.payment_status IN ( %s ) OR e.payment_status IS NULL )', self::prepare_strings_for_mysql_in_statement( $valid_statuses ) );
		return $query;
	}

	public function get_out_of_stock_message() {
		return $this->_args['out_of_stock_message'];
	}

	/**
	 * Calculated Product fields limited by their default quantity should be excluded from the order summary.
	 *
	 * Since their value is calculated post submission, they function uniquely from other product fields.
	 *
	 * @param $order
	 * @param $form
	 * @param $entry
	 *
	 * @return mixed
	 */
	public function handle_calculated_product_fields( $order, $form, $entry ) {

		foreach( $order['products'] as $field_id => $product ) {

			// Check for if target input ID is for the current field and is targeting a Product field default quantity input (i.e. 1.3).
			if( $field_id != intval( $this->_args['input_id'] ) || rgar( explode( '.', $this->_args['input_id'] ), 1 ) != '3' ) {
				continue;
			}

			$field = GFAPI::get_field( $form, $field_id );
			if( $field->get_input_type() !== 'calculation' ) {
				continue;
			}

			if( ! $this->is_in_stock() ) {
				unset( $order['products'][ $field_id ] );
			}

		}

		return $order;
	}

	public static function get_field_values_sum( $form_id, $input_id ) {
		global $wpdb;

		$query  = self::get_sum_query( $form_id, $input_id );
		$sql    = implode( "\n", $query );
		$result = $wpdb->get_var( $sql );

		return intval( $result );
	}

	public static function get_sum_query( $form_id, $input_id, $suppress_filters = false ) {
		global $wpdb;

		$query = array(
			'select' => 'SELECT sum( em.meta_value )',
			'from'   => "FROM {$wpdb->prefix}gf_entry_meta em",
			'join'   => "INNER JOIN {$wpdb->prefix}gf_entry e ON e.id = em.entry_id",
			'where'  => $wpdb->prepare( "
                WHERE em.form_id = %d
                AND em.meta_key = %s 
                AND e.status = 'active'\n",
				$form_id, $input_id
			)
		);

		if( class_exists( 'GF_Partial_Entries' ) ) {
			$query['where'] .= "and em.entry_id NOT IN( SELECT entry_id FROM {$wpdb->prefix}gf_entry_meta WHERE meta_key = 'partial_entry_id' )";
		}

		if( ! $suppress_filters ) {
			$query  = apply_filters( 'gwlimitbysum_query',                 $query, $form_id, $input_id );
			$query  = apply_filters( 'gwinv_query',                        $query, $form_id, $input_id );
			$query  = apply_filters( "gwinv_query_{$form_id}",             $query, $form_id, $input_id );
			$query  = apply_filters( "gwinv_query_{$form_id}_{$input_id}", $query, $form_id, $input_id );
		}

		return $query;
	}

	public static function prepare_strings_for_mysql_in_statement( $strings ) {
		$wrapped = array();
		foreach( $strings as $string ) {
			$wrapped[] = sprintf( '"%s"', $string );
		}
		return implode( ', ', $wrapped );
	}

}

class GWLimitBySum extends GW_Inventory { }

# Configuration

new GW_Inventory( array(
	'form_id'                  => 661,
	'field_id'                 => 1.3,
	'stock_qty'                => 100,
	'out_of_stock_message'     => 'Sorry, there are no more tickets!',
	'not_enough_stock_message' => 'You ordered %1$s tickets. There are only %2$s tickets left.',
	'approved_payments_only'   => false,
	'hide_form'                => false
) );


//
//$gw_inv1 = new GW_Inventory( array(
//	'form_id'     => 86,
//	'field_id'    => '1.3',
//	'stock_qty'   => 10,
//) );
//
//add_filter( 'gform_field_value_sum1', function() {
//	global $gw_inv1;
//	return $gw_inv1->get_sum();
//} );

//add_filter( 'wp', function() {
//	global $post;
//	if( $post ) {
//		$gwinv = new GW_Inventory( array(
//			'form_id' => 363,
//			'field_id' => 5.3,
//			'stock_qty' => get_post_meta( $post->ID, 'stock', true ),
//		) );
//		$gwinv->init();
//	}
//}, 8 );

//new GW_Inventory( array(
//	'form_id'     => 239,
//	'field_id'  => 2.3,
//	'stock_qty' => 10,
//	'not_enough_stock_message' => __( 'You ordered %1$s of this item but there are %2$s of this item left for the selected date.' ),
//	'out_of_stock_message'     => 'Sorry, but like, whatever.',
//	'approved_payments_only'   => true
//) );
//
//new GW_Inventory( array(
//	'form_id'   => 1874,
//	'field_id'  => 1.3,
//	'stock_qty' => 20,
//) );
//
//new GW_Inventory( array(
//	'form_id'     => 1827,
//	'field_id'    => 9,
//	'stock_qty'   => 1,
//) );
//
//new GW_Inventory( array(
//	'form_id'     => 239,
//	'field_id'    => 13,
//	'stock_qty'   => 110,
//	'not_enough_stock_message' => __( 'You ordered %1$s of this item but there are %2$s of this item left for the selected date.' ),
//	'out_of_stock_message' => 'Sorry, but like, whatever.',
//	'approved_payments_only' => true
//) );
//
//new GW_Inventory( array(
//	'form_id'     => 239,
//	'field_id'    => 14,
//	'stock_qty'   => 5000,
//	'not_enough_stock_message' => __( 'You ordered %1$s of this item but there are %2$s of this item left for the selected date.' ),
//	'out_of_stock_message' => 'Sorry, but like, whatever.',
//	'approved_payments_only' => true
//) );
//
//new GW_Inventory( array(
//    'form_id'     => 1330,
//    'field_id'    => 4,
//    'field_group' => array( 12 ),
//    'stock_qty'   => 11,
//    'approved_payments_only' => true
//) );
//
//new GW_Inventory( array(
//	'form_id'     => 1330,
//	'field_id'    => 4,
//	'field_group' => array( 15 ),
//	'stock_qty'   => 11,
//	'approved_payments_only' => true
//) );
//
////new GW_Inventory( array(
////	'form_id'     => 239,
////	'field_id'    => 11.3,
////	'field_group' => 5,
////	'stock_qty'   => 6,
////	'not_enough_stock_message' => __( 'You ordered %1$s of this item but there are %2$s of this item left for the selected date.' ),
////	'out_of_stock_message' => 'Sorry, but like, whatever.',
////) );
//
////foreach( array( 13, 14 ) as $field_id ) {
////
////	$input_id = floatval( $field_id . '.3' );
////
////	new GW_Inventory( array(
////		'form_id'     => 239,
////		'field_id'    => $input_id,
////		'stock_qty'   => 1,
////		'not_enough_stock_message' => __( 'You ordered %1$s of this item but there are %2$s of this item left for the selected date.' ),
////		'out_of_stock_message' => 'Sorry, but like, whatever.',
////	) );
////
////}
//
////new GW_Inventory( array(
////    'form_id'     => 239,
////    'field_id'    => 11.3,
////    'field_group' => 5,
////    'stock_qty'   => function() {
////		$post_id = get_queried_object_id();
////	    return intval( get_post_meta( $post_id, 'stock', true ) );
////    },
////    'not_enough_stock_message' => __( 'You ordered %1$s of this item but there are %2$s of this item left for the selected date.' ),
////	'out_of_stock_message' => 'Sorry, but like, whatever.',
////) );
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
//
////new GW_Inventory( array(
////    'form_id'                  => 363,
////    'field_id'                 => 2.3,
////    'stock_qty'                => 20,
////    'out_of_stock_message'     => 'Sorry, there are no more tickets!',
////    'not_enough_stock_message' => 'You ordered %1$s tickets. There are only %2$s tickets left.',
////    'approved_payments_only'   => false,
////    'hide_form'                => false,
////    'enable_notifications'     => true
////) );
//
////new GW_Inventory( array(
////    'form_id'                  => 363,
////    'field_id'                 => 5.3,
////    'stock_qty'                => 0,
////    'out_of_stock_message'     => 'Sorry, there are no more tickets!',
////    'not_enough_stock_message' => 'You ordered %1$s tickets. There are only %2$s tickets left.',
////    'approved_payments_only'   => false,
////    'hide_form'                => true,
////    'enable_notifications'     => true
////) );
//
////new GW_Inventory( array(
////    'form_id'     => 239,
////    'field_id'    => 11.3,
////    'field_group' => 5,
////    'stock_qty'   => 5,
////    'not_enough_stock_message' => __( 'You ordered %1$s of this item but there are %2$s of this item left for the selected date.' ),
////) );
//
////new GWLimitBySum( array(
////    'form_id'  => 239,
////    'field_id' => 2.3,
////    'limit'    => 20
////) );
//
//
//
//
//
//
//
//
//
//
//
//
//
////new GWLimitBySum( array(
////    'form_id'                => 363,
////    'field_id'               => 2.3,
////    'limit'                  => 20,
////    'limit_message'          => 'Sorry, there are no more tickets!',
////    'validation_message'     => 'You ordered %1$s tickets. There are only %2$s tickets left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////
////new GWLimitBySum( array(
////	'form_id'                => 363,
////	'field_id'               => 5.3,
////	'limit'                  => 20,
////	'limit_message'          => 'Sorry, there are no more tickets!',
////	'validation_message'     => 'You ordered %1$s tickets. There are only %2$s tickets left.',
////	'approved_payments_only' => false,
////	'hide_form'              => false
////) );
////
////new GWLimitBySum( array(
////    'form_id'                => 363,
////    'field_id'               => 8,
////    'limit'                  => 20,
////    'limit_message'          => 'Sorry, there are no more tickets!',
////    'validation_message'     => 'You ordered %1$s tickets. There are only %2$s tickets left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////
////
////
////
////
////
////
////# Configuration
////
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 2.3,
////    'limit'                  => 1,
////    'limit_message'          => '1st Place Prize is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 3.3,
////    'limit'                  => 1,
////    'limit_message'          => '2nd Place Prize is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 4.3,
////    'limit'                  => 1,
////    'limit_message'          => '3rd Place Prize is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 5.3,
////    'limit'                  => 1,
////    'limit_message'          => '4th Place Prize is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 6.3,
////    'limit'                  => 1,
////    'limit_message'          => '5th Place Prize is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 7.3,
////    'limit'                  => 1,
////    'limit_message'          => '6th Place Prize is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 8.3,
////    'limit'                  => 1,
////    'limit_message'          => '7th Place Prize is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 9.3,
////    'limit'                  => 1,
////    'limit_message'          => '8th Place Prize is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 10.3,
////    'limit'                  => 1,
////    'limit_message'          => '9th Place Prize is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////
//////TABLES
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 14.3,
////    'limit'                  => 14,
////    'limit_message'          => 'There are no more standard tables available.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////new GWLimitBySum( array(
////    'form_id'                => 1008,
////    'field_id'               => 15.3,
////    'limit'                  => 1,
////    'limit_message'          => 'The final table is already sponsored.',
////    'validation_message'     => 'You asked to sponsor %1$s tables. There are only %2$s tables left.',
////    'approved_payments_only' => false,
////    'hide_form'              => false
////) );
////
////
////
////
////new GWLimitBySum( array(
////    'form_id'                => 1041,
////    'field_id'               => 2,
////    'limit'                  => 300,
////    'limit_message'          => '<div style="border: 1px solid #9E122C; background-color: #9F122C; color: #FFFFFF!important; padding: 10px;">Sorry. We are sold out. To join the wait list, <a style="color: #FFFFFF!important;" href="https://peachtreebootcamp.com/">click here</a>.</div>',
////    'validation_message'     => 'You ordered %1$s tickets. There are only %2$s tickets left.',
////    'xapproved_payments_only' => true,
////    'hide_form'              => false
////) );
////
////new GWLimitBySum( array(
////    'form_id'                => 1041,
////    'field_id'               => 20,
////    'limit'                  => 300,
////    'limit_message'          => '<div style="border: 1px solid #9E122C; background-color: #9F122C; color: #FFFFFF!important; padding: 10px;">Sorry. We are sold out. To join the wait list, <a style="color: #FFFFFF!important;" href="https://peachtreebootcamp.com/">click here</a>.</div>',
////    'validation_message'     => 'You ordered %1$s tickets. There are only %2$s tickets left.',
////    'xapproved_payments_only' => true,
////    'hide_form'              => false
////) );
//
//
//
//
//
//
//
//
//# Configuration
//
//// Diocesan Convention 2016
//
//new GW_Inventory( array(
//
//	'form_id' => 1441,
//
//	'field_id' => 13.3,
//
//	'stock_qty' => 1,
//
//	'out_of_stock_message' => 'Sorry, there are no more Doubles left!',
//
//	'not_enough_stock_message' => 'You ordered %1$s tickets. There are only %2$s tickets left.',
//
//	'approved_payments_only' => false,
//
//	'hide_form' => false,
//
//	'enable_notifications' => true
//
//) );
//
//// same form, different field
//
//new GW_Inventory( array(
//
//	'form_id' => 1441,
//
//	'field_id' => 14.3,
//
//	'stock_qty' => 11,
//
//	'out_of_stock_message' => 'Sorry, there are no more Quads left!',
//
//	'not_enough_stock_message' => 'You ordered %1$s tickets. There are only %2$s tickets left.',
//
//	'approved_payments_only' => false,
//
//	'hide_form' => false,
//
//	'enable_notifications' => true
//
//) );
//
//// same form, different field
//
//new GW_Inventory( array(
//
//	'form_id' => 1441,
//
//	'field_id' => 26.3,
//
//	'stock_qty' => 2,
//
//	'out_of_stock_message' => 'Sorry, there are no more Accessible Doubles left!',
//
//	'not_enough_stock_message' => 'You ordered %1$s tickets. There are only %2$s tickets left.',
//
//	'approved_payments_only' => false,
//
//	'hide_form' => false,
//
//	'enable_notifications' => true
//
//) );
//
//
////add_action( 'wp', function() {
////	global $post;
////	new GW_Inventory( array(
////		'form_id'     => get_post_meta( $post->ID, 'formId', true ),
////		'field_id'    => get_post_meta( $post->ID, 'fieldId', true ),
////		'stock_qty'   => get_post_meta( $post->ID, 'stockQty', true ),
////		// ...
////	) );
////} );
//
//
//
//
//new GW_Inventory( array(
//	'form_id'     => 1562,
//	'field_id'    => 2.3,
//	'stock_qty'   => 3,
//	'field_group' => array( 32 ),
//	'not_enough_stock_message' => __( 'You ordered %1$s of this item but there are %2$s of this item left for the selected date.' ),
//	'out_of_stock_message'     => 'Out of stock.',
//) );
//
//
///**
// *
// * total sum of all input => value matches          2 => 2016-07-19
// *
// */