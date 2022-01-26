<?php
/**
 * Gravity Perks // Nested Forms // Disable Session Storage
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
add_filter( 'gform_pre_render', function( $form ) {
    if ( empty( $_POST ) && class_exists( 'GPNF_Session' ) ) {
        $session = new GPNF_Session( $form['id'] );
        $session->delete_cookie();
    }
    return $form;
} );
