<?php
/**
 * Gravity Wiz // Gravity Connect // Disable Async Feed Processing for all Gravity Connect Plugins
 *
 * By default, Gravity Connect feeds run asynchronously. This snippet will disable that, so they run in the same request as the form submission.
 *
 * This is useful for debugging whether there is a wider issue with asynchronous feeds on a site.
 *
 * Installation:
 * 
 * 1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 * 2. Profit.  
 */
add_filter( 'gform_is_feed_asynchronous', function( $is_async, $feed, $entry, $form ) {
	if ( ! class_exists( 'GFFeedAddOn' ) ) {
		return $is_async;
	}

	$addon = GFFeedAddOn::get_addon_by_slug( $feed['addon_slug'] );

	if ( ! $addon ) {
		return $is_async;
	}

	if ( $addon instanceof \GC_Plugin ) {
		return false;
	}

	return $is_async;
}, 50, 4 );
