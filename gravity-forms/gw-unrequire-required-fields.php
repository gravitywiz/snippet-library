<?php
/**
 * Gravity Wiz // Gravity Forms Unrequire Required Fields for Testing
 *
 * When bugs pop up on your forms, it can be really annoying to have to fill out all the required fields for every test
 * submission. This snippet saves you that hassle by unrequiring all required fields so you don't have to fill them out.
 *
 * @version 1.0
 * @author David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link http://gravitywiz.com/speed-up-gravity-forms-testing-unrequire-required-fields/
 * @copyright 2013 Gravity Wiz
 */

class GWUnrequire {

 var $_args = null;

 public function __construct( $args = array() ) {

 $this->_args = wp_parse_args( $args, array(
 'admins_only' => true,
 'require_query_param' => true
 ) );

 add_filter( 'gform_pre_validation', array( $this, 'unrequire_fields' ) );

 }

 function unrequire_fields( $form ) {

 if( $this->_args['admins_only'] && ! current_user_can( 'activate_plugins' ) )
 return $form;

 if( $this->_args['require_query_param'] && ! isset( $_GET['gwunrequire'] ) )
 return $form;

 foreach( $form['fields'] as &$field ) {
 $field['isRequired'] = false;
 }

 return $form;
 }

}

# Basic Usage
# requires that the user be logged in as an administrator and that a 'gwunrequire' parameter be added to the query string
# http://youurl.com/your-form-page/?gwunrequire=1

new GWUnrequire();
