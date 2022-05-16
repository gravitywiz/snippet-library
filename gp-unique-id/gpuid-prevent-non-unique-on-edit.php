<?php
/**
 * Gravity Perks // Unique ID // Prevent Non-Unique Values on Edit
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 *
 * Screenshot: https://gwiz.io/3wjwtYD
 *
 * Plugin Name: GP Unique ID – Prevent Non-Unique Values on Edit
 * Plugin URI:  
 * Description: Redirect to a specified URL when the limit is reached (rather than displaying a limit message).
 * Author:      Gravity Wiz
 * Version:     0.1
 * Author URI:  https://gravitywiz.com
 */
add_action( 'gform_pre_entry_detail', function( $form, $entry ) {

	foreach ( $form['fields'] as $field ) {
		if ( $field->get_input_type() === 'uid' ) {
			$key   = "input_{$field->id}";
			$value = rgpost( $key );
			if ( $value && ! gp_unique_id()->check_unique( $value, $form['id'], $field->id ) ) {
				$_POST[ $key ] = $entry[ $field->id ];
				?>
				<script>
					setTimeout( function() {
						jQuery( '#entry_form' ).before( '<div class="alert warning"><p><?php echo $field->get_field_label( false, $value ); ?> was not updated. The provided value was not unique.</p></div> ' );
					} );
				</script>
				<?php
			}
		}
	}

}, 10, 2 );
