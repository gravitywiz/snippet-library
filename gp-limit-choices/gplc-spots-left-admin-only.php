<?php
/**
 * Gravity Perks // Limit Choices // Display How Many Spots Left to Admins Only
 * https://gravitywiz.com/documentation/gravity-forms-limit-choices/
 *
 * (Admin-only) Display how many spots are left in the choice label when using the GP Limit Choices Perk.
 */
add_filter( 'gplc_pre_render_choice', 'my_add_how_many_left_message', 10, 4 );
function my_add_how_many_left_message( $choice, $exceeded_limit, $field, $form ) {

	if ( ! current_user_can( 'administrator' ) ) {
		return $choice;
	}

	$limit         = method_exists( gp_limit_choices(), 'get_choice_limit' ) ? gp_limit_choices()->get_choice_limit( $choice, $field->formId, $field->id ) : rgar( $choice, 'limit' );
	$how_many_left = max( $limit - $count, 0 );

	$message = "($how_many_left spots left)";

	$choice['text'] = $choice['text'] . " $message";

	return $choice;
}
