/**
 * Gravity Perks // GP Reload Form // Reload Form in a Custom Element
 * https://gravitywiz.com/documentation/gravity-forms-reload-form/
 *
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addFilter( 'gprf_replacing_elem', function( $replacingElem, self.formId, self ) {
	return $( '#my-custom-element' );
} );
