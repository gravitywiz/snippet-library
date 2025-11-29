<?php
/**
 * Gravity Wiz // Gravity Forms // Office Closings
 *
 * Display a short message above a form when your office is closed.
 *
 * @version  0.2
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/
 */
class GW_Office_Closings {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_get_form_filter', array( $this, 'handle_closing_messages' ), 10, 2 );

	}

	public function handle_closing_messages( $html, $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $html;
		}

		$timestamp = current_time( 'timestamp' );
		// Display the message a weekday early so customers submitting immediately before a closing are aware of potential delays.
		$timestamp = strtotime( '+1 weekday', $timestamp );
		$messages  = array();

		foreach ( $this->_args['schedule'] as $schedule ) {
			$start_date = $this->get_date_timestamp( $schedule['start_date'] );
			$end_date   = $this->get_date_timestamp( $schedule['end_date'], false );
			if ( $timestamp >= $start_date && $timestamp <= $end_date ) {
				$messages[] = $this->replace_date_merge_tags( $schedule['message'], $schedule );
			}
		}

		if ( ! empty( $messages ) ) {
			$html = $this->format_messages( $messages ) . $html;
		}

		return $html;
	}

	public function get_date_timestamp( $date, $midnight = true ) {
		$date    = explode( '-', $date );
		$date[0] = str_replace( '*', date( 'Y' ), $date[0] );
		$date[1] = str_replace( '*', date( 'm' ), $date[1] );
		$date[2] = str_replace( '*', date( 'd' ), $date[2] );

		$timestamp = strtotime( implode( '-', $date ) );
		if ( ! $midnight ) {
			$timestamp = strtotime( '11:59pm', $timestamp );
		}

		return $timestamp;
	}

	public function format_messages( $messages ) {
		return sprintf( '<div class="gwoc-messages callout notice" data-label="Support Announcement">%s</div>', implode( '</p><p>', $messages ) );
	}

	public function replace_date_merge_tags( $message, $schedule ) {

		// Parse merge tags (AI-generated).
		preg_match_all('/\{([a-z_]+):([^\}]+)\}/i', $message, $matches, PREG_SET_ORDER);

		foreach ( $matches as $match ) {

			$tag = $match[1];
			$format = $match[2];

			if ( $tag === 'next_date' ) {
				$date = date( 'Y-m-d', strtotime( 'next weekday', $this->get_date_timestamp( $schedule['end_date'] ) ) );
			} else {
				$date = rgar( $schedule, $tag );
			}

			if ( ! $date ) {
				continue;
			}

			$message = str_replace( $match[0], date( $format, $this->get_date_timestamp( $date ) ), $message );
		}

		return $message;
	}

	public function is_applicable_form( $form ) {
		$form_id = $form['id'] ?? $form;
		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

}

# Configuration

new GW_Office_Closings( array(
	'form_id'  => 7,
	'schedule' => array(
		array(
			'start_date' => '2024-12-31',
			'end_date'   => '2025-01-01',
			'message'    => '<p><strong>The Gravity Wiz <strike>office</strike> tower will be closed {start_date:l, F jS} through {end_date:l, F jS} in honor of New Year\'s.</strong></p><p>May the best year of your past be the worst year of your future. We look forward to providing you magical support again on {next_date:l, F jS}.</p>',
		),
		array(
			'start_date' => '*-05-26',
			'end_date'   => '*-05-26',
			'message'    => '<p><strong>The Gravity Wiz <strike>office</strike> tower will be closed {start_date:l, F jS} in honor of Memorial Day.</strong></p><p>We\'ll be casting a 21-spell salute in appreciation of our veterans! We look forward to providing you magical support again on {next_date:l, F jS}.</p>',
		),
		array(
			'start_date' => '*-07-04',
			'end_date'   => '*-07-04',
			'message'    => '<p><strong>The Gravity Wiz <strike>office</strike> tower will be closed {start_date:l, F jS} in observance of Independence Day.</strong></p><p>Today we remember that all wizards (and people) are created equal. We look forward to providing you magical support again on {next_date:l, F jS}.</p>',
		),
		array(
			'start_date' => '*-09-01',
			'end_date'   => '*-09-01',
			'message'    => '<p><strong>The Gravity Wiz <strike>office</strike> tower will be closed {start_date:l, F jS} in honor of Labor Day.</strong></p><p>Work hard, play hard, live well. We look forward to providing you magical support again on {next_date:l, F jS}.</p>',
		),
		array(
			'start_date' => '*-11-28',
			'end_date'   => '*-11-29',
			'message'    => '<p><strong>The Gravity Wiz <strike>office</strike> tower will be closed {start_date:l, F jS} through {end_date:l, F jS} in honor of Thanksgivings.</strong></p><p>We couldn\'t conjure up better customers if we tried and we\'re incredibly grateful for you. Thank you! We look forward to providing you magical support again on {next_date:l, F jS}.</p>',
		),
		array(
			'start_date' => '*-12-24',
			'end_date'   => '*-12-25',
			'message'    => '<p><strong>The Gravity Wiz <strike>office</strike> tower will be closed {start_date:l, F jS} through {end_date:l, F jS} in honor of Christmas Eve and Christmas Day.</strong></p><p>We hope the holidays cast a merry spell of peace and love upon you and your family. We look forward to providing you magical support again on {next_date:l, F jS}.</p>',
		),
		array(
			'start_date' => '2025-12-31',
			'end_date'   => '2026-01-01',
			'message'    => '<p><strong>The Gravity Wiz <strike>office</strike> tower will be closed {start_date:l, F jS} through {end_date:l, F jS} in honor of New Year\'s.</strong></p><p>May the best year of your past be the worst year of your future. We look forward to providing you magical support again on {next_date:l, F jS}.</p>',
		),
	),
) );
