<?php
/**
 * Gravity Perks // GP Read Only // Disable Read Only for Admins
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 *
 * Disable the Read Only property on fields for Admin users. 
 * This is useful when wanting to allow administrative users to edit fields that non-admins should not be able to edit.
 *
 * Plugin Name:  GP Read Only — Disable Read Only for Admins
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-read-only/
 * Description:  This snippet allows you to disbale the Read Only property on fields for Admin users.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_pre_render', function( $form ) {
    foreach( $form['fields'] as $field ) {
        if( gw_has_css_class( $field, 'gp-read-only-except-admin' ) && current_user_can( 'administrator' ) ) {
            $field->gwreadonly_enable = false;
        }
    }
    return $form;
} );
