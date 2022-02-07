<?php
/**
 * Gravity Wiz // Gravity Forms // Simple Approval with Save and Continue
 * https://gravitywiz.com/simple-submission-approval-gravity-forms/
 *
 * Make use of Gravity Forms' Save and Continue functionality to send the incomplete form to another user for final
 * approval before submission.
 *
 * Plugin Name:  Gravity Forms — Simple Approval with Save and Continue
 * Plugin URI:   https://gravitywiz.com/simple-submission-approval-gravity-forms/
 * Description:  A simple, single-step approval process for your Gravity Forms submissions.
 * Author:       Gravity Wiz
 * Version:      1.1
 * Author URI:   https://gravitywiz.com/
 */
class GW_Simple_Approval {

	protected static $is_script_output = false;

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'              => false,
			'approve_button_label' => __( 'Approve' ),
			'approval_read_only'   => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.9', '>=' ) ) {
			return;
		}

		// time for hooks
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );
		add_filter( 'gform_save_and_continue_resume_url', array( $this, 'add_approval_query_arg' ), 10, 4 );
		add_filter( 'gform_pre_render', array( $this, 'modify_frontend_form_object_for_approval' ) );

	}

	function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$script = '
            ( function( $ ) {
                var $submitButton       = $( "#gform_submit_button_" + formId ),
                    $saveContinueButton = $( "#gform_save_" + formId + "_link" );
                if( $saveContinueButton.is( "a" ) ) {
                    var label = $saveContinueButton.html();
                    $saveContinueButton.html( "" );
                    var html = $( "<div />" ).append( $saveContinueButton.clone() ).html();
                    $saveContinueButton.replaceWith( html.replace( "<a", "<input type=\"submit\" value=\"" + label + "\"" ).replace( "</a>", "" ) );
                    $saveContinueButton.addClass( "gform_button button" );
                }
                if( $submitButton.is( ":visible" ) ) {
                    $saveContinueButton.hide();
                } else {
                    $saveContinueButton.show();
                }
            } )( jQuery );';

		$slug = 'gw_simple_approval';

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_CONDITIONAL_LOGIC, $script );

	}

	function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return $form_id == $this->_args['form_id'];
	}

	function add_approval_query_arg( $resume_url, $form, $token, $email ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $resume_url;
		}

		// add query arg
		$resume_url = add_query_arg( array(
			'gf_token'    => $token,
			'is_approval' => 1,
		), $resume_url );

		return $resume_url;
	}

	function modify_frontend_form_object_for_approval( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		if ( rgget( 'is_approval' ) ) {
			?>

			<style type="text/css">
				form.gw-approval-mode .hide-in-approval-mode { display: none; }
			</style>

			<?php

			$form['cssClass']       .= 'gw-approval-mode';
			$form['save']['enabled'] = false;
			$form['button']['text']  = $this->_args['approve_button_label'];

			foreach ( $form['fields'] as &$field ) {

				if ( $field->inputName == 'is_approval' ) {
					$field->defaultValue = 1;
				}

				if ( $this->_args['approval_read_only'] ) {
					$field->gwreadonly_enable = true;
				}
			}
		}

		return $form;
	}

}

# Configuration

new GW_Simple_Approval( array(
	'form_id'              => 59,
	'approve_button_label' => 'Approve Prayer Request',
	'approval_read_only'   => true, // requires GP Read Only
) );
