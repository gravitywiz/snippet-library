<?php
/**
 * Gravity Perks // File Upload Pro // Deregister Vue as a Dependency
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * File Upload Pro utilizes Vue as the backing framework for its JavaScript
 * on both the frontend and in the form editor. In the event that File Upload Pro’s
 * bundled version of Vue is conflicting with another registered version of Vue,
 * it can be deregistered using the following snippet.
 */
add_filter( 'gpfup_scripts', function ( $scripts ) {
	foreach ( $scripts as &$script ) {
		if ( $script['handle'] !== 'gp-file-upload-pro' ) {
			continue;
		}

		$script['deps'] = array_diff( $script['deps'], array( 'gravityperks-vue-2' ) );
	}

	return $scripts;
} );
