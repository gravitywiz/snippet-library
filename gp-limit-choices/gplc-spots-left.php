<?php

/**
 * GP Limit Choices // Gravity Perks // Display How Many Spots Left
 *
 * Display how many spots are left in the choice label when using the GP Limit Choices perk
 * http://gravitywiz.com/gravity-perks/
 */

add_filter( 'gplc_remove_choices', '__return_false' );

add_filter( 'gplc_pre_render_choice', 'my_add_how_many_left_message', 10, 5 );
function my_add_how_many_left_message( $choice, $exceeded_limit, $field, $form, $count ) {
	$limit         = method_exists( gp_limit_choices(), 'get_choice_limit' ) ? gp_limit_choices()->get_choice_limit( $choice, $field->formId, $field->id ) : rgar( $choice, 'limit' );
	$how_many_left = max( $limit - $count, 0 );

	// translators: placeholder is number of remaining spots left
	$message = sprintf( _n( '(%s spot left)', '(%s spots left)', $how_many_left, 'gp-limit-choices' ), number_format_i18n( $how_many_left ) );

	$choice['text'] = $choice['text'] . " $message";

	return $choice;
}
