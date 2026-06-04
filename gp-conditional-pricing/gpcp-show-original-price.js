/**
 * Gravity Perks // GP Conditional Pricing // Show the original price crossed out next to the adjusted price
 * https://gravitywiz.com/documentation/gravity-forms-conditional-pricing/
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
var gwcpBasePrices = {};

$( document ).off( 'gppa_updated_batch_fields.gwcp' ).on( 'gppa_updated_batch_fields.gwcp', function( event, formId, updatedFieldIds ) {

	var gwcp = GWConditionalPricing.getFormElement( formId ).data( 'gwcp' );
	if ( ! gwcp || ! gwcp._pricingLogic || ! updatedFieldIds || ! updatedFieldIds.length ) {
		return;
	}

	var currency = typeof gform !== 'undefined' && typeof gform.Currency === 'function'
		? new gform.Currency( gf_global['gf_currency_config'] )
		: new Currency( gf_global['gf_currency_config'] );

	for ( var productId in gwcp._pricingLogic ) {
		if ( ! gwcp._pricingLogic.hasOwnProperty( productId ) || updatedFieldIds.indexOf( parseInt( productId, 10 ) ) === -1 && updatedFieldIds.indexOf( productId.toString() ) === -1 ) {
			continue;
		}

		var key   = formId + '_' + productId;
		var $base = $( '#ginput_base_price_{0}_{1}'.gformFormat( formId, productId ) );

		if ( $base.length && $base.val() !== '' ) {
			gwcpBasePrices[ key ] = currency.toMoney( currency.toNumber( $base.val() ), true );
		} else {
			delete gwcpBasePrices[ key ];
		}
	}

} );

gform.addAction( 'gpcp_after_update_pricing', function( triggerFieldId, GWConditionalPricing, productId ) {

	var basePrice = gwcpBasePrices[ GWConditionalPricing._formId + '_' + productId ] || GWConditionalPricing.getBasePrice( productId );
	if ( ! basePrice ) {
		return;
	}

	// Quantity enabled: price is text in span#input_*. Quantity disabled: price is the value of
	// the readonly input#ginput_base_price_*.
	var $input  = $( 'span#input_{0}_{1}'.gformFormat( GWConditionalPricing._formId, productId ) );
	var isInput = false;
	if ( ! $input.length ) {
		$input  = $( 'input#ginput_base_price_{0}_{1}'.gformFormat( GWConditionalPricing._formId, productId ) );
		isInput = true;
	}
	if ( ! $input.length ) {
		return;
	}

	var displayedPrice = isInput ? $input.val() : $input.text();

	var $basePrice = $( '#base_price_{0}_{1}'.gformFormat( GWConditionalPricing._formId, productId ) );

	// Remove base price if there is no discounted price.
	if ( displayedPrice === basePrice ) {
		$basePrice.remove();
	}
	// Otherwise, add base price if it has not been added.
	else {
		if ( ! $basePrice.length ) {
			$basePrice = '<span id="base_price_{0}_{1}" style="text-decoration:line-through;margin-right:0.3rem"></span>'.gformFormat( GWConditionalPricing._formId, productId );
			$input.before( $basePrice );
		}

		if ( $basePrice && typeof $basePrice.text === 'function' ) {
			$basePrice.text( basePrice );
		}
	}

} );
