/**
 * Gravity Perks // Unique ID // Reset Genereated ID when it reaches a number
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * This example shows you how to reset a sequentially generated ID when it reaches a specific number.
 *
 */
add_filter( 'gpui_unique_id', function( $unique, $form_id, $field_id ) {

    // Update "123" to your form ID and "4" to the ID of your Unique ID field.

    if( $form_id == 123 &amp;&amp; $field_id == 4 ) {

        // This is the number that the sequence will be reset to.
        $starting_number = 1;

        // When this number is reached, the unique ID sequence will reset to the starting number.
	    $reset_number = 2;
	    if ( $unique == $reset_number ) {
	        gp_unique_id()->set_sequential_starting_number( $form_id, $field_id, $starting_number - 1 );
	    } 
    }

    return $unique;
}, 10, 3 );
