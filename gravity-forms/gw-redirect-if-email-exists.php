<?php
/**
 * Gravity Wiz // Gravity Forms // Redirect if Email Exists
 * https://gravitywiz.com/
 * 
 * Redirect to a specified URL if the the submitted email address matches an existing user.
 *
 * Note: Does not work with AJAX-enabled forms.
 *
 * Plugin Name: Gravity Forms - Redirect if Email Exists
 * Plugin URI: https://gravitywiz.com/
 * Description: Redirect to a specified URL if the the submitted email address matches an existing user.
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: http://gravitywiz.com
 */
class GW_Redirect_Email_Exists {

    public function __construct( $args = array() ) {

        // set our default arguments, parse against the provided arguments, and store for use throughout the class
        $this->_args = wp_parse_args( $args, array(
		'form_id' => false,
	    	'email_field_id' => false,
	    	'redirect_url' => false,
        ) );

        // do version check in the init to make sure if GF is going to be loaded, it is already loaded
        add_action( 'init', array( $this, 'init' ) );

    }

    public function init() {

        // make sure we're running the required minimum version of Gravity Forms
        if( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
            return;
        }

        add_filter( 'gform_validation', array( $this, 'maybe_redirect' ) );

    }

    public function maybe_redirect( $result ) {
    	if( $this->is_applicable_form( $result['form'] ) ) {
		    $field = GFFormsModel::get_field( $result['form'], $this->_args['email_field_id'] );
		    $value = rgpost( 'input_' . str_replace( '.', '_', $field->id ) );
		    if( get_user_by( 'email', $value ) ) {
		    	wp_redirect( $this->_args['redirect_url'] );
		    	exit;
		    }
	    }
	    return $result;
    }

    public function is_applicable_form( $form ) {

        $form_id = isset( $form['id'] ) ? $form['id'] : $form;

        return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
    }

}

# Configuration

new GW_Redirect_Email_Exists( array(
	'form_id'        => 123,
	'email_field_id' => 4,
	'redirect_url'   => 'http://google.com'
) );
