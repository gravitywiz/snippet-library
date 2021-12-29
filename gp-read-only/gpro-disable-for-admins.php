<?php
/**
 * Gravity Perks // GP Read Only // Disable Read Only for Admins
 * https://gravitywiz.com/documentation/gravity-forms-read-only/
 *
 * Instruction Video: https://www.loom.com/share/24aaeb27fce44dc98355b78168d6fe5d
 *
 * Disable the Read Only property on fields for Admin users.
 * This is useful when wanting to allow administrative users to edit fields that non-admins should not be able to edit.
 *
 * Usage:
 *
 * 1. Install this code as a plugin or as a snippet.
 * 2. Add the `gp-read-only-except-admin` CSS Class Name to field's Custom CSS Class setting.
 *
 * Plugin Name:  GP Read Only — Disable Read Only for Admins
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-read-only/
 * Description:  This snippet allows you to disbale the Read Only property on fields for Admin users.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_pre_render', function( $form ) {
    foreach( $form['fields'] as $field ) {
        if( strpos( $field->cssClass, "gp-read-only-except-admin" ) !== false && current_user_can( 'administrator' ) ) {
            $field->gwreadonly_enable = false;
        }
    }
    return $form;
} );
