/**
 * Gravity Perks // eCommerce Fields // Exclude Products on Hidden Pages
 * https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * Gravity Forms does not exclude products on Hidden pages from frontend totals. As a byproduct,
 * they are not excluded from eCommerce Fields' subtotals either. This snippet addresses that.
 * 
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
window['GFPageConditionalLogic_GFFORMID'] = null;

gform.addAction( 'gform_frontend_pages_evaluated', function( pages, formId, self ) {
	if ( formId == GFFORMID ) {
		window['GFPageConditionalLogic_GFFORMID'] = self;
		gformCalculateTotalPrice( formId );	
	}
} );

window.gformIsHidden = function( $elem ) {
	
	if ( ! $elem.length ) {
		return false;
	}
	
	let $field        = $elem.parents( '.gfield' );
	let isFieldHidden = $field.not( '.gfield_hidden_product' ).css( 'display' ) === 'none';
	if ( isFieldHidden ) {
		return true;
	}
	
	var pageCL = window['GFPageConditionalLogic_GFFORMID'];
	if ( ! pageCL ) {
		return false;
	}
	
	let pageIndex = $field.parents( '.gform_page' ).index();
	if ( pageIndex === 0 ) {
		return false;
	}
	
	let page         = pageCL.options.pages[ pageIndex - 1 ];
	let isPageHidden = ! pageCL.isPageVisible( page );
	
	return isPageHidden;
}
