<?php
/**
 * Gravity Perks // Multi-page Navigation // Enable Page Query Parameter for Specific Form
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 *
 * Typically, the `gpmpn_page` query parameter, which allows you to set the default starting page for a form, is only
 * enabled if you are using the `page` attribute on the [gravityforms] shortcode. This snippet will enable it by default
 * for a specific form. This is useful if you are displaying the form via a block.
 */
// Update "123" to your form ID.
add_filter( 'gpmpn_default_page_123', function() {
	return rgget( 'gpmpn_page' );
} );
