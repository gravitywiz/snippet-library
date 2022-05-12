<?php
/**
 * Gravity Wiz // Gravity Forms // Disable HTML5 Validation
 * https://gravitywiz.com/disable-html5-validation-on-gravity-forms/
 *
 * An easy way to disable HTML5 validation on your Gravity Forms.
 *
 * Plugin Name:  Gravity Forms Disable HTML5 Validation
 * Plugin URI:   https://gravitywiz.com/disable-html5-validation-on-gravity-forms/
 * Description:  Disable HTML5 validation on your Gravity Forms
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com/
 */
add_filter( 'gform_form_tag', 'add_no_validate_attribute_to_form_tag' );
function add_no_validate_attribute_to_form_tag( $form_tag ) {
    return str_replace( '>', ' novalidate="novalidate">', $form_tag );
}
