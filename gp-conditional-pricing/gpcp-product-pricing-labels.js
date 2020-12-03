/**
 * Gravity Perks // GP Conditional Pricing // Display Price Labels (JS)
 * http://gravitywiz.com/documentation/gravity-forms-conditional-pricing/
 *
 * This snippet requires the PHP counterpart gpcp-product-pricing-labels.php
 */
( function( $ ) {
	function update_price_lables() {
		$( 'label[data-gpcp-template], option[data-gpcp-template]' ).each( function() {
			var $priceElem = $( this ).is( 'option' ) ? $( this ) : $( this ).siblings( 'input' ),
				price = gformFormatMoney( $priceElem.val().split( '|' )[1] ),
				template = $( this ).attr( 'data-gpcp-template' );
			$( this ).html( template.replace( '{price}', price ) );
		} );
	}
	gform.addAction( 'gpcp_after_update_pricing', update_price_lables);
	update_price_lables();
} )( jQuery );
