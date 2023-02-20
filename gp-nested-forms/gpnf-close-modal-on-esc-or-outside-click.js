/**
 * Gravity Perks // Nested Forms // Close Nested Form modal when ESC key is pressed or when clicked outside modal
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
// Replace 1 with Nested Form Field ID
var gpnf = window['GPNestedForms_GFFORMID_1'];

// Close the modal when Escape key is pressed
document.addEventListener( 'keydown', function( event ) {
    if ( event.key === 'Escape' ) {
        gpnf.modal.close();
    }
});

// Close the modal when clicking outside the modal box
$( document ).on( 'click', function (event ) {
    if ( $( event.target ).hasClass( 'tingle-modal--noOverlayClose' ) ) {
        gpnf.modal.close();
    }
});
