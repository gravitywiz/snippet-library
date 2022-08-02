/**
 * Gravity Perks // GP Reload Form // Replace a custom element with the form markup.
 * https://gravitywiz.com/documentation/gravity-forms-reload-form/
 *
 * We recommend installing this snippet with our free Custom Javascript plugin:
 * https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addFilter( 'gprf_replacing_elem', function( $replacingElem, self.formId, self ) {
	return jQuery( '#my-custom-element' );
} );
