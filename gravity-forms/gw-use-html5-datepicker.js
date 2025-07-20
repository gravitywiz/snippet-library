/**
 * Gravity Wiz // Gravity Forms // Use HTML5 Datepicker
 *
 * Experimental Snippet 🧪
 *
 * Some users, especially on mobile, may prefer their browser's native datepicker. This snippet will
 * convert date fields to use the native datepicker if the visitor is on a mobile device. Optionally, you can
 * have the date fields converted for all users (including desktop).
 *
 * Instructions:
 * 
 * 1. Install our free Custom JavaScript for Gravity Forms plugin.
 *     Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */

var onlyConvertOnMobile = true; // Change to false if you wish for all devices to use HTML5 datepicker

var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

if ( isMobile || onlyConvertOnMobile === false ) {
	jQuery.fn.datepicker = function() {};

	jQuery( '#gform_fields_GFFORMID .ginput_container_date input' )
		.attr( 'type', 'date' );
}
