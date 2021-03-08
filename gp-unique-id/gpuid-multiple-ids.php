<?php
/**
 * Gravity Perks // GP Unique ID // Generate Multiple Unique IDs per Submission (for Gravity Forms)
 *
 * Generate multiple unique IDs. Does not work with sequential IDs.
 *
 * @version   1.3
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/
 */
class GPUID_Multiple_IDs {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'         => false,
			'target_field_id' => false,
			'source_field_id' => false,
			'count'           => 0,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) || ! function_exists( 'gp_unique_id' ) ) {
			return;
		}

		add_action( 'gform_entry_post_save', array( $this, 'populate_field_value' ), 10, 2 );
		add_action( 'gpui_check_unique_query', array( $this, 'reinforce_check_unqiue_query' ), 10, 4 );

	}

	public function populate_field_value( $entry, $form, $fulfilled = false ) {

		$feed = null;

		if ( rgar( $entry, 'partial_entry_id' ) ) {
			return $entry;
		}

		foreach ( $form['fields'] as $field ) {

			if ( ! $this->is_applicable_field( $field, $form, $entry ) ) {
				continue;
			}

			if ( $feed === null ) {
				$feed = gp_unique_id_field()->get_paypal_standard_feed( $form, $entry );
				/**
				 * Modify the feed that indicates a payment gateway is configured that
				 * accepts delayed payments (i.e. PayPal Standard).
				 *
				 * This filter allows 3rd party payment add-ons to add support for delaying unique ID generation when
				 * one of their feeds is present.
				 *
				 * @since 1.3.1
				 *
				 * @param $feed  array The payment feed.
				 * @param $form  array The current form object.
				 * @param $entry array The current entry object.
				 */
				$feed = gf_apply_filters( array( 'gpui_wait_for_payment_feed', $form['id'], $field->id ), $feed, $form, $entry );
			}

			/**
			 * Indicate whether the unique ID generation should wait for a completed payment.
			 *
			 * Only applies to payment gateways that accept delayed payments (i.e. PayPal Standard).
			 *
			 * @since 1.3.0
			 *
			 * @param $wait_for_payment bool  Whether or not to wait for payment. Defaults to false.
			 * @param $form             array The current form object.
			 * @param $entry            array The current entry object.
			 */
			$wait_for_payment = $feed && gf_apply_filters( array( 'gpui_wait_for_payment', $form['id'], $field->id ), false, $feed, $form, $entry );
			if ( $wait_for_payment && ! $fulfilled ) {
				continue;
			}

			$count        = $this->get_count( $entry );
			$source_field = GFFormsModel::get_field( $form, $this->_args['source_field_id'] );
			$value        = array();

			// add source field ID as first of the X IDs
			$value[] = rgar( $entry, $source_field['id'] );

			for ( $i = 1; $i < $count; $i++ ) {
				$value[] = gp_unique_id()->get_unique( $form['id'], $source_field );
			}

			$entry[ $field['id'] ] = implode( "\n", $value );

			GFAPI::update_entry( $entry );

		}

		return $entry;
	}

	function get_count( $entry ) {

		$count = $this->_args['count'];

		if ( is_int( $count ) ) {
			// do nothing
		} elseif ( is_array( $count ) ) {

			if ( isset( $count['choices'] ) ) {
				$value = call_user_func( 'array_shift', explode( '|', rgar( $entry, $count['field_id'] ) ) );
				$count = rgar( $count['choices'], $value );
			} else {
				$count = rgar( $entry, $count['field_id'] );
			}
		} else {
			$count = 0;
		}

		return $count;
	}

	function is_applicable_field( $field, $form, $entry ) {

		$is_form            = (int) $form['id'] === (int) $this->_args['form_id'];
		$is_target_field_id = (int) $field['id'] === (int) $this->_args['target_field_id'];
		$is_visible         = ! GFFormsModel::is_field_hidden( $form, $field, array(), $entry );

		return $is_form && $is_target_field_id && $is_visible;
	}

	function reinforce_check_unqiue_query( $query, $form_id, $field_id, $unique ) {

		$search  = "AND ld.value = '{$unique}'";
		$replace = "AND ld.value LIKE '%{$unique}%'";

		$query = str_replace( $search, $replace, $query );

		return $query;
	}

}

# Configuration

//new GPUID_Multiple_IDs( array(
//   'form_id'         => 694,
//   'source_field_id' => 35,
//   'target_field_id' => 36,
//   'count'           => 2
//) );

//new GPUID_Multiple_IDs( array(
//	'form_id'         => 753,
//	'source_field_id' => 1,
//	'target_field_id' => 2,
//	'count'           => array(
//		'field_id' => 3,
//	),
//) );

// new GPUID_Multiple_IDs( array(
//     'form_id'         => 694,
//     'target_field_id' => 36,
//     'source_field_id' => 35,
//     'count'           => array(
//         'field_id' => 33,
//         'choices'  => array(
//             'Hole in One Sponsor ($25,000)'  => 12,
//             'Albatross Sponsor ($12,500)'    => 8,
//             'Eagle Sponsor ($6,500)'         => 4,
//             'Birdie Sponsor ($4,500)'        => 2,
//             'Par Sponsor ($2,000)'           => 1,
//             'Digital Media Sponsor ($1,000)' => 0,
//             'Chip-In Sponsor ($500)'         => 0
//         )
//     )
// ) );
