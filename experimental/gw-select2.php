<?php
/**
 * Gravity Wiz // Gravvity Forms // Use Select2 for Drop Down & Multi Select Fields
 * https://gravitywiz.com/
 *
 * Add "gw-select2" to to the "Custom CSS Class" setting for any Drop Down or Multi Select field to enable Select2 for that field.
 */
add_action( 'gform_enqueue_scripts', function( $form ) {
	foreach ( $form['fields'] as &$field ) {
		if ( strpos( $field->cssClass, 'gw-select2' ) !== false ) {
			wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js' );
			wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
		}
	}
} );
