/**
 * Gravity Wiz // Gravity Forms // Show Two Decimals for Calculation Results
 * https://gravitywiz.com/
 *
 * Always show two decimals in your calculation results (e.g. `1.00`, `1.50`). 
 *
 * Most often if you need to show two decimals it's because you're working with currency; however, this
 * isn't always the case. In these scenarios, this snippet can help.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 
 * 2. Follow the inline comments to configure for your form and field.
 */
gform.addFilter( 'gform_calculation_format_result', function ( formattedResult, result, formulaField, formId, calcObj ) {
	// Change "4" to your field ID.
	if ( formId == GFFORMID && formulaField.field_id == 4 ) { 
		var currency = new Currency( gf_global.gf_currency_config );
    	formattedResult = currency.numberFormat( result, 2, currency.currency.decimal_separator, currency.currency.thousand_separator );
  	}
	return formattedResult;
} );
