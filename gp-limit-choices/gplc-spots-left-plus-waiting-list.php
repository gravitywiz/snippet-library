<?php
/**
 * Gravity Perks // GP Limit Choices // Spots Left + Waiting List Message
 *
 * Allows you to display the number of spots left in the label of each choice. If there are no spots left, will display a waiting list message.
 *
 * @link http://gravitywiz.com/documentation/gp-limit-choices/
 */

add_filter( 'gplc_remove_choices', '__return_false' );
add_filter( 'gplc_disable_choices', '__return_false' );

add_filter( 'gplc_pre_render_choice', 'my_add_how_many_left_message', 10, 4 );
function my_add_how_many_left_message( $choice, $exceeded_limit, $field, $form ) {

    $choice_counts = GWLimitChoices::get_choice_counts( $form['id'], $field );
    $count = intval( rgar( $choice_counts, $choice['value'] ) );
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
