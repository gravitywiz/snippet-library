<?php
/**
 * Gravity Perks // Populate Anything // Add A Custom Choice Template To Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gppa_input_choice', function( $choice, $field, $object, $objects ) {
    $choice['image'] = gp_populate_anything()->process_template( $field, 'image', $object, 'choices', $objects );
    return $choice;
}, 10, 4 );
