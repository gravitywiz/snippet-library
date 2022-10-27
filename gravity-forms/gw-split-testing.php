<?php
/**
 * Gravity Wiz // Gravity Forms // Split Testing for Gravity Forms
 *
 * Allows you to test the effectiveness of two or more Gravity Forms by using a single shortcode (or function call) to
 * randomly alternating which form is displayed. Effectiveness can be measured by the "Conversion" column available
 * by default on the Gravity Forms' "Forms" list view.
 *
 * Based on https://gist.github.com/fatmedia/8289103 by @realFATmedia via @mattreport
 *
 * @version   1.0
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/simple-split-testing-gravity-forms/
 * @copyright 2013 Gravity Wiz
 */
class GW_Split_Testing {

	protected static $instance = null;

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {

		add_filter( 'gform_shortcode_split_test', array( $this, 'do_split_test_shortcode' ), 10, 2 );

	}

	public function do_split_test_shortcode( $output, $atts ) {

		// get our "random" form ID from the provided form IDs
		$form_ids = array_map( 'trim', explode( ',', $atts['ids'] ) );

		return $this->get_split_test_form( $form_ids, $atts );
	}

	public function get_split_test_form( $form_ids = array(), $atts = array() ) {

		if ( empty( $form_ids ) ) {
			return;
		}

		if ( rgpost( 'gform_submit' ) && in_array( rgpost( 'gform_submit' ), $form_ids ) ) {
			$form_id = rgpost( 'gform_submit' );
		} else {
			$index   = mt_rand( 0, count( $form_ids ) - 1 );
			$form_id = $form_ids[ $index ];
		}

		// modify attributes to create form-generating shortcode
		$atts['action'] = 'form';
		$atts['id']     = $form_id;

		// generate [gravityform] form shortcode
		$shortcode_bits = array();
		foreach ( $atts as $key => $value ) {

			if ( is_array( $value ) ) {
				$value = implode( ',', $value );
			}

			if ( $value === true ) {
				$value = 'true';
			}

			if ( $value === false ) {
				$value = 'false';
			}

			$shortcode_bits[] = "{$key}=\"$value\"";
		}
		$shortcode = '[gravityform ' . implode( ' ', $shortcode_bits ) . ' /]';

		// get the form markup by processing the generated shortcode
		$form_markup = do_shortcode( $shortcode );

		return $form_markup;
	}

}

function gw_split_testing() {
	return GW_Split_Testing::get_instance();
}

gw_split_testing();
