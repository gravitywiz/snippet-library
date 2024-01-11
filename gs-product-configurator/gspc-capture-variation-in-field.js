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
// Update "pa_color" to the variation attribute you would like to capture.
var $variation = $( '#pa_color' );

// Update "6" to the ID of the field on your form that will captured the selected variation attribute.
var $targetField = $( '#input_GFFORMID_6' );

$variation.on( 'change', function() {
	$targetField.val( $variation.val() );
} );

$targetField.val( $variation.val() );
