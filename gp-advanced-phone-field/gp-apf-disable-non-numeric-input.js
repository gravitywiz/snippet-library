/**
 * Gravity Perks // Advanced Phone Field // Disable Phone Validation
 * https://gravitywiz.com/documentation/gravity-forms-advanced-phone-field/
 *
 * Plugin Name:  GP Advanced Phone Field — Disable Non Numeric Input
 * Description:  Disable non numeric phone number input.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 * 
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */

// Replace 1 with the field id.
$( '#input_GFFORMID_1_raw' ).bind( 'keyup paste', function() {
	this.value = this.value.replace( /[^0-9]/g, '' );
});
