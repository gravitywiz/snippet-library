/**
 * Gravity Perks // Populate Anything // Replace Live Merge Tags Individually and Show Spinner
 * https://gravitywiz.comhttps://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addFilter( 'gppa_loading_target_meta', function( targetMeta, $elem, context ) {

	targetMeta[0] = $elem;
	targetMeta[1] = 'gppa-spinner';

	if( $elem.is( 'span' ) && $elem.data( 'gppa-live-merge-tag' ) ) {
		$elem.html( '' );
	}

	return targetMeta;
} );
