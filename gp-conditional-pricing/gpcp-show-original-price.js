/**
 * Gravity Perks // GP Conditional Pricing // Show the original price crossed out next to the adjusted price
 * http://gravitywiz.com/documentation/gravity-forms-conditional-pricing/
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
gform.addAction( 'gpcp_after_update_pricing', function( triggerFieldId, GWConditionalPricing, productId ) {

	var basePrice = GWConditionalPricing.getBasePrice( productId );
	if ( ! basePrice ) {
		return;
	}

	var $input = $( '#field_{0}_{1}'.gformFormat( GWConditionalPricing._formId, productId ) );
	if ( ! $input.length ) {
		return;
	}

	var $basePrice = $( '#base_price_{0}_{1}'.gformFormat( GWConditionalPricing._formId, productId ) );

	// Remove base price if there is no discounted price.
	if ( $input.text() === basePrice ) {
		$basePrice.remove();
	}
	// Otherwise, add base price if it has not been added.
	else if ( ! $basePrice.length ) {
		$basePrice = '<span id="base_price_{0}_{1}" style="text-decoration:line-through;margin-right:0.3rem">{2}</span>'.gformFormat( GWConditionalPricing._formId, productId, basePrice );
		$input.before( $basePrice );
	}

} );
