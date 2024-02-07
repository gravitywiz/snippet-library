/**
 * Gravity Shop // Product Configurator // Capture Variation in Field
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * Capture the selected variation in a form field.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 
 * 2. Follow the inline instructions to update the snippet for your form.
 */
var variationMap = {
	// Update "pa_color" to the variation ID you would like to capture and "1" to the field ID in which the selected variation value should be captured.
	'pa_color' : 1,
	// Repeat the instructions above to capture a different variation or remove this line to only capture a single variation.
	'size': 3,
}

for (let key in variationMap) {
    if (variationMap.hasOwnProperty(key)) {
		mapVariation( key, variationMap[key]);
    }
}

function mapVariation( variation, fieldId ) {
	let $variation = $( '#' + variation );
	let $targetField = $( '#input_GFFORMID_' + fieldId );
	
	if ( ! $variation.length || ! $targetField.length ) {
		return;
	}
	
	$variation.on( 'change', function() {
		$targetField.val( $variation.val() );
	} );

	$targetField.val( $variation.val() );
}
