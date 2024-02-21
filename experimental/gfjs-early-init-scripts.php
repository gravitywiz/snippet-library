<?php
/**
 * Gravity Forms // Custom Javascript // Load Init Scripts Early
 * https://gravitywiz.com/gravity-forms-code-chest/
 *
 * Some perks (e.g. Copy Cat, Address Autocomplete) allow their initialization options to be filtered but the Custom JS
 * plugin outputs its scripts too late. This changes bumps GF Custom JS scripts to be output first.
 *
 * Developer Note: It's not possible to simply change the priority of the "gform_register_init_scripts" filter as
 * field-based init scripts are registered before the filter is called.
 */
add_action( 'gform_register_init_scripts', function( $form ) {

	$scripts  = rgar( GFFormDisplay::$init_scripts, $form['id'] );
	if ( empty( $scripts ) ) {
		return;
	}

	$filtered = array();
	foreach ( $scripts as $slug => $script ) {
		if ( strpos( $slug, 'gf_custom_js' ) === 0 ) {
			$filtered = array( $slug => $script ) + $filtered;
		} else {
			$filtered[ $slug ] = $script;
		}
	}

	GFFormDisplay::$init_scripts[ $form['id'] ] = $filtered;

}, 100 /* right after GF Custom JS */ );
