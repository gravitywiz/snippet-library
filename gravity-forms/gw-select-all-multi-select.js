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

let $selectAll = $( '<button type="button" style="margin-top: 1rem;">Select All</button>' );
let $clearAll  = $( '<button type="button" style="margin: 1rem 0 0 0.5rem;">Clear All</button>' );
let $target    = $( '#input_GFFORMID_' + targetFieldId );

$target.parents( '.ginput_container' ).append( $selectAll, $clearAll );

$selectAll.on( 'click', function() {
	selectAllOptions( $target);
} );

$clearAll.on( 'click', function() {
	selectAllOptions( $target, false );
} );

function selectAllOptions( $select, selected ) {
	selected = typeof selected !== 'undefined' ? selected : true;
	$select.find( 'option' ).prop( 'selected', selected );
	window[ 'GPAdvancedSelect_GFFORMID_' + targetFieldId ].sync();
	$select.trigger( 'change' );
}
