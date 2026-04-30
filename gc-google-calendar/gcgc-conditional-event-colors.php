<?php
/**
 * Gravity Connect // Google Calendar // Conditional Event Colors
 * https://gravitywiz.com/documentation/gravity-connect-google-calendar/
 *
 * Set Google Calendar event colors conditionally based on form field values.
 *
 * Instructions
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Configure your rules at the bottom of this file: set `form_id` (and optionally `feed_id`),
 *    then add a `conditionals` entry for each color, using the color IDs below. Rules are
 *    evaluated top-to-bottom and the first match wins.
 *
 * Google Calendar Color IDs:
 *  1  = Lavender   (blue-purple)  #a4bdfc
 *  2  = Sage       (muted green)  #7ae7bf
 *  3  = Grape      (purple)       #dbadff
 *  4  = Flamingo   (pink)         #ff887c
 *  5  = Banana     (yellow)       #fbd75b
 *  6  = Tangerine  (orange)       #ffb878
 *  7  = Peacock    (teal)         #46d6db
 *  8  = Graphite   (gray)         #e1e1e1
 *  9  = Blueberry  (blue)         #5484ed
 *  10 = Basil      (green)        #51b749
 *  11 = Tomato     (red)          #dc2127
 */
class GCGC_Conditional_Event_Colors {

	private $_args = array();

	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args( $args, array(
			'form_id'      => false,
			'feed_id'      => false,
			'conditionals' => array(),
		) );

		add_action( 'plugins_loaded', array( $this, 'init' ), 15 );
	}

	public function init() {
		if ( ! function_exists( 'gc_google_calendar' ) ) {
			return;
		}

		add_filter( 'gcgc_create_calendar_event_params', array( $this, 'apply_color' ), 10, 4 );
		add_filter( 'gcgc_update_calendar_event_params', array( $this, 'apply_color' ), 10, 4 );
	}

	public function apply_color( $params, $form, $feed, $entry ) {
		if ( ! $this->is_applicable_form( $form ) || ! $this->is_applicable_feed( $feed ) ) {
			return $params;
		}

		foreach ( $this->_args['conditionals'] as $conditional ) {
			if ( GFCommon::evaluate_conditional_logic( $conditional['conditionalLogic'], $form, $entry ) ) {
				$params['colorId'] = (string) $conditional['colorId'];
				break;
			}
		}

		return $params;
	}

	public function is_applicable_form( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

	public function is_applicable_feed( $feed ) {
		$feed_id = isset( $feed['id'] ) ? $feed['id'] : $feed;

		return empty( $this->_args['feed_id'] ) || $feed_id == $this->_args['feed_id'];
	}
}

# Configuration

new GCGC_Conditional_Event_Colors( array(
	'form_id'      => 123,
	'feed_id'      => false, // Optional: restrict to a specific feed ID
	'conditionals' => array(
		array(
			'colorId'          => '11', // Tomato (red)
			'conditionalLogic' => array(
				'logicType'  => 'any',
				'actionType' => 'show',
				'rules'      => array(
					array(
						'fieldId'  => '3',
						'operator' => 'is',
						'value'    => 'urgent',
					),
				),
			),
		),
		array(
			'colorId'          => '5', // Banana (yellow)
			'conditionalLogic' => array(
				'logicType'  => 'any',
				'actionType' => 'show',
				'rules'      => array(
					array(
						'fieldId'  => '3',
						'operator' => 'is',
						'value'    => 'pending',
					),
				),
			),
		),
	),
) );
