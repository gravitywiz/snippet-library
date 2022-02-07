<?php
/**
 * Gravity Perks // Limit Choices // Daily Limit
 * https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 *
 * By default, GP Limit Choices limits choices forever. This snippet will allow you to make that limit apply only to the current day.
 *
 * Plugin Name:  GP Limit Choices — Daily Limit
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 * Description:  Set the limit to apply only to the current day.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
class GWLimitChoicesDailyLimit {

	function __construct( $args ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'   => false,
			'field_ids' => array(),
		) );

		add_filter( "gwlc_choice_counts_query_{$this->_args['form_id']}", array( $this, 'modify_choice_counts_query' ), 10, 2 );

	}

	function modify_choice_counts_query( $query, $field ) {

		$form = GFAPI::get_form( $field['formId'] );

		foreach ( $form['fields'] as $field ) {

			if ( ! in_array( $field['id'], $this->_args['field_ids'] ) ) {
				continue;
			}

			$time_period_sql = 'DATE( date_created ) = DATE( utc_timestamp() )';
			$query['where'] .= ' AND ' . $time_period_sql;

		}

		return $query;
	}

}

new GWLimitChoicesDailyLimit( array(
	'form_id'   => 436,
	'field_ids' => array( 4 ),
) );
