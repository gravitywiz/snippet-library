<?php
/**
 * Gravity Perks // Popups // Pass Hash Parameters to Popup Form
 * https://gravitywiz.com/documentation/gravity-forms-popups/
 *
 * Open a Hash-triggered popup from a link that has query parameters after the hash, and pass
 * those parameters to the popup form using Gravity Forms dynamic population.
 *
 * For example, `https://example.com/page/#my-popup?ref=abc&uid=123` opens the popup whose Hash
 * is `my-popup` and populates the popup form fields whose dynamic population parameter names are
 * `ref` and `uid`. `#my-popup/?ref=abc` works too.
 *
 * Parameters are only passed to popups using the default "Iframe" Rendering Method. Popups using
 * the "Inline" Rendering Method still open, but to populate them, put the parameters before the
 * hash instead: `https://example.com/page/?ref=abc&uid=123#my-popup`.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Enable "Allow field to be populated dynamically" on each popup form field and set its
 *    parameter name to match a parameter in your link.
 */
add_action( 'wp_footer', function() {

	if ( ! wp_script_is( 'gp_popups_frontend', 'enqueued' ) ) {
		return;
	}

	$script = <<<'JS'
( function() {

	if ( ! Array.isArray( window.gpPopupsConfig ) ) {
		return;
	}

	function normalizeHash( hash ) {
		return String( hash || '' ).replace( /^#/, '' ).toLowerCase();
	}

	var popupHashes = window.gpPopupsConfig
		.filter( function( config ) {
			return config.trigger && config.trigger.type === 'hash' && config.trigger.hashValue;
		} )
		.map( function( config ) {
			return normalizeHash( config.trigger.hashValue );
		} );

	var paramsByHash = {};

	// Store the parameters from "#my-popup?ref=abc" and rewrite the URL to "#my-popup" so the Hash trigger matches it.
	function extractHashParams() {
		var match = window.location.hash.match( /^#([^?]*?)\/?\?(.*)$/ );
		if ( ! match || popupHashes.indexOf( normalizeHash( match[1] ) ) === -1 ) {
			return;
		}

		paramsByHash[ normalizeHash( match[1] ) ] = new URLSearchParams( match[2] );

		window.history.replaceState( window.history.state, '', window.location.pathname + window.location.search + '#' + match[1] );
	}

	// This runs before GP Popups sets up its triggers, and its listener is added before theirs.
	extractHashParams();
	window.addEventListener( 'hashchange', extractHashParams );

	window.gform.addFilter( 'gpp_popup_config', function( config ) {
		var params = config.trigger && paramsByHash[ normalizeHash( config.trigger.hashValue ) ];
		if ( ! params ) {
			return config;
		}

		var url = new URL( config.iframeUrl, window.location.href );

		params.forEach( function( value, name ) {
			// Don't let a link change which popup the iframe loads.
			if ( name !== 'gp_popups_render' && name !== 'nonce' ) {
				url.searchParams.set( name, value );
			}
		} );

		config.iframeUrl = url.toString();

		return config;
	} );

} )();
JS;

	wp_add_inline_script( 'gp_popups_frontend', $script, 'before' );

}, 19 );
