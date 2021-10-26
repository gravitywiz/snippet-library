<?php
/**
 * Gravity Wiz // Gravity Forms // Check If Form Will Be Loaded on Page
 * https://gravitywiz.com/
 *
 * This snippet will allow you to check if a form will be loaded on the current page and do something if it will be.
 * Note: this is a simple version that will only work on singular views where the [gravityforms] shortcode is used in
 * the post content.
 *
 * @todo:
 *  + Update to parse all posts on a given page. See: GFFormDisplay::enqueue_scripts().
 */
add_filter( 'wp', function() {

	if ( ! class_exists( 'GFCommon' ) || ! is_singular() ) {
		return;
	}

	require_once( GFCommon::get_base_path() . '/form_display.php' );
	GFFormDisplay::parse_forms( get_queried_object()->post_content, $forms, $blocks );

	foreach ( $forms as $form ) {
		// Update "123" to your target form ID.
		if ( $form['id'] == 123 ) {
			// Form 123 will be loaded. Do what you need to do here.
		}
	}

} );
