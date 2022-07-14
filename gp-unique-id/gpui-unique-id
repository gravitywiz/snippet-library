<?php
/**
 * Gravity Perks // Unique ID // Prepend Unique IDs with a dynamic string
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * This example shows you how to prepend all generated IDs with the output of a custom function (my_custom_function) 
 * before the value is stored in the database.
 */
add_filter( 'gpui_unique_id', function( $unique, $form_id, $field_id ) {

    // Prepend UIDs with a dynamically generated string

    $my_pre = my_custom_function();

    return $my_pre . $unique;

}, 10, 3 );
