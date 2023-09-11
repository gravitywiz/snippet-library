/**
 * Gravity Wiz // Gravity Forms // Add "Select All" Button for Multi Select Field
 * https://gravitywiz.com/
 * 
 * Works with [GP Advanced Select](https://gravitywiz.com/documentation/gravity-forms-advanced-select/)!
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 * 
 * 2. Configure per the inline instructions.
 */
// Update "4" to your Multi Select field ID.
let targetFieldId = 4;

let $button = $( '<button type="button" style="margin-top: 1rem;">Select All</button>' );
let $target = $( '#input_GFFORMID_' + targetFieldId );

$target.parents( '.ginput_container' ).append( $button );

$button.on( 'click', function() {
	$target.find( 'option' ).prop( 'selected', true );
	window[ 'GPAdvancedSelect_GFFORMID_' + targetFieldId ].sync();
	$target.trigger( 'change' );
} );
