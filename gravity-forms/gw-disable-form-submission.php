<?php
/**
 * Gravity Wiz // Gravity Forms // Disable Form Submission
 * https://gravitywiz.com
 *
 * Disables form submission entirely for specified forms. Useful for forms that act as calculators
 * (e.g. with GP Advanced Calculations) or forms that contain GC OpenAI Stream/Image fields.
 *
 * Instructions:
 *
 * 1. Install this code as a plugin or as a snippet (https://gravitywiz.com/documentation/how-do-i-install-a-snippet/)
 * 2. Specify the form IDs you would like to disable form submission at the bottom of this snippet.
 *
 * Plugin Name:  Gravity Forms - Disable Form Submission
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Disables form submission entirely for specified forms.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GW_Disable_Form_Submission {

	private $_args = array();

	/**
	 * @param $args array{
	 *     form_ids: int[]
	 * } The arguments to initialize GW_Disable_Form_Submission.
	 */
	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args( $args, array(
			'form_ids' => array(),
		) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gform_validation', array( $this, 'disable_form_submission' ) );
		add_filter( 'gform_submit_button', array( $this, 'remove_submit_button' ), 10, 2 );
	}

	public function disable_form_submission( $validation_result ) {
		if ( ! $this->is_applicable_form( $validation_result['form'] ) ) {
			return $validation_result;
		}

		$validation_result['is_valid'] = false;

		return $validation_result;
	}

	public function remove_submit_button( $button, $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $button;
		}

		return '';
	}

	public function is_applicable_form( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return in_array( $form_id, $this->_args['form_ids'], false );
	}
}

# Configuration

new GW_Disable_Form_Submission(array(
	// replace with form IDs you would like to disable form submission for
	'form_ids' => array(
		6,
	),
));
