/**
 * Gravity Perks // Multi-Page Navigation // Load all links for Edit Entry
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 *
 * Use the snippet with Gravity Perks Easy Pass Through Edit Entry Snippet
 * https://github.com/gravitywiz/snippet-library/blob/master/gp-easy-passthrough/gpep-edit-entry.php
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */

var $formElem = $( 'form#gform_GFFORMID' );
var $steps    = $formElem.find( '.gf_step' );

// reload all step links
$steps.each( function( index ) {
	var stepNumber = index + 1;
	$(this).html(getPageLinkMarkup(stepNumber, $(this).html())).addClass( 'gpmpn-step-linked' );
} );

function getPageLinkMarkup( stepNumber, content ) {
	return '<a href="#' + stepNumber + '" class="gwmpn-page-link gwmpn-default gpmpn-page-link gpmpn-default">' + content + '</a>';
};
