<?php
/**
 * Gravity Perks // Entry Blocks // Apply Default Value to Filter on Page Load
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Filter entries on Page Load with a provided default value
 */
use function GP_Entry_Blocks\get_current_url;
add_action( 'gpeb_before_render_block', function( $block ) {
	if ( ! isset( $_GET['filters'] ) ) {
		wp_safe_redirect( add_query_arg( array(
			// REPLACE "3" with the Field ID of your Field.
			// REPLACE "date( 'Y-m-d' )" with your default value.
			// This example target Date Field ID 3 to work with the current date as default value.
			'filters'         => array( 3 => date( 'Y-m-d' ) ),
			'filters_form_id' => rgar( $block->context, 'gp-entry-blocks/formId' ),
		), get_current_url() ) );
	}
}, 10 );
