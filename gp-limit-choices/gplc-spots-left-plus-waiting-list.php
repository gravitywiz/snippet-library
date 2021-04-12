<?php
/**
 * Gravity Perks // GP Limit Choices // Spots Left + Waiting List Message
 *
 * Display the number of spots left in the label of each choice. If there are no spots left, it will display a waiting list message.
 * https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 *
 * Plugin Name:  GP Limit Choices — Spots Left + Waiting List Message
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 * Description:  Display the number of spots left in the label of each choice. If there are no spots left, it will display a waiting list message.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gplc_remove_choices', '__return_false' );
add_filter( 'gplc_disable_choices', '__return_false' );

add_filter( 'gplc_pre_render_choice', 'my_add_how_many_left_message', 10, 5 );
function my_add_how_many_left_message( $choice, $exceeded_limit, $field, $form, $count ) {
    $limit = rgar( $choice, 'limit' );
    $how_many_left = max( $limit - $count, 0 );

    if( $how_many_left <= 0 ) {
        $message = '(waiting list)';
    } else {
        $message = "($how_many_left spots left)";
    }
    
    $choice['text'] = $choice['text'] . " $message";
    return $choice;
}
