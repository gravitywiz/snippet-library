/**
 * Gravity Wiz // Gravity Forms // Prevent Non-numeric Characters in Input
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 *
 * Based on solution provided here: https://stackoverflow.com/a/15729184/227711
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 * 2. Configure snippet for your form based on inline instructions.
 */
// Update "1" to your field ID.
document.getElementById( 'input_GFFORMID_1' ).onkeypress = function( e ) {
    e = e || window.event;
    var charCode = ( typeof e.which == 'undefined' ) ? e.keyCode : e.which;
    var charStr = String.fromCharCode( charCode );
    if ( ! /\d/.test( charStr ) ) {
        return false;
    }
};
