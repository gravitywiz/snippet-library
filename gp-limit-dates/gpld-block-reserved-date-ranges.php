<?php
/**
 * Gravity Perks // Limit Dates // Block Reserved Date Ranges
 *
 * Instruction Video: https://www.loom.com/share/8eb1f52a08ef4b87974565d6bd14ee6b
 *
 * Block date ranges reserved by previous submissions in linked Date fields.
 *
 * For example, if a user selects Nov 5th as their start date and Nov 10th as their end date, prevent those dates and
 * all dates in between from being selected by another user.
 *
 * Plugin Name:  GP Limit Dates — Block Reserved Date Ranges
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-limit-dates/
 * Description:  Block date ranges reserved by previous submissions in linked Date fields.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GPLD_Block_Reserved_Date_Ranges {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( "gpld_limit_dates_options_{$this->_args['form_id']}_{$this->_args['start_date_field_id']}", array( $this, 'block_reserved_date_ranges' ), 10, 3 );
		add_filter( "gpld_limit_dates_options_{$this->_args['form_id']}_{$this->_args['end_date_field_id']}", array( $this, 'block_reserved_date_ranges' ), 10, 3 );

	}

	function block_reserved_date_ranges( $field_options, $form, $field ) {

		$date = gmdate( 'Y-m-d' );

		// Get all entries where the start date is *after* today - or - where the start date is *before* today and the
		// end date is *after*.
		$query = new GF_Query( $form['id'] );
		$query->where(
			GF_Query_Condition::_and(
				new GF_Query_Condition(
					new GF_Query_Column( 'status' ),
					GF_Query_Condition::EQ,
					new GF_Query_Literal( 'active' )
				),
				GF_Query_Condition::_or(
					new GF_Query_Condition(
						new GF_Query_Column( $this->_args['start_date_field_id'] ),
						GF_Query_Condition::GTE,
						new GF_Query_Literal( $date )
					),
					GF_Query_Condition::_and(
						new GF_Query_Condition(
							new GF_Query_Column( $this->_args['start_date_field_id'] ),
							GF_Query_Condition::LTE,
							new GF_Query_Literal( $date ),
						),
						new GF_Query_Condition(
							new GF_Query_Column( $this->_args['end_date_field_id'] ),
							GF_Query_Condition::GTE,
							new GF_Query_Literal( $date ),
						)
					)
				)
			)
		);

		$entries = $query->get();
		if ( empty( $entries ) ) {
			return $field_options;
		}

		$exceptions = $field_options['exceptions'];

		foreach ( $entries as $entry ) {
			$start_date = new DateTime( $entry[ $this->_args['start_date_field_id'] ] );
			$end_date   = new DateTime( $entry[ $this->_args['end_date_field_id'] ] );
			do {
				$exceptions[] = $start_date->format( 'm/d/Y' );
				$start_date->add( new DateInterval( 'P1D' ) );
			} while ( $start_date <= $end_date );
		}

		$exceptions = array_unique( $exceptions );

		$field_options['exceptionMode'] = 'disable';
		$field_options['exceptions']    = array_values( $exceptions );

		return $field_options;
	}

}

# Configuration

new GPLD_Block_Reserved_Date_Ranges( array(
	'form_id'             => 1039,
	'start_date_field_id' => 1,
	'end_date_field_id'   => 2,
) );
