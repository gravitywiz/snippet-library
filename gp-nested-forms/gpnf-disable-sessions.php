<?php
/**
 * Gravity Perks // Nested Forms // Disable Session Storage
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/348e9e70999a43eaaf73cf0c820161aa
 *
 */
add_filter( 'gform_pre_render', function( $form ) {
    if ( empty( $_POST ) && class_exists( 'GPNF_Session' ) ) {
        $session = new GPNF_Session( $form['id'] );
        $session->delete_cookie();
    }
    return $form;
} );
