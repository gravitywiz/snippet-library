/**
 * Gravity Perks // Nested Forms // Count the Number of "Answered" Child Fields
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Experimental Snippet 🧪
 *
 * This is designed for use with our [GF Custom Javascript](https://gravitywiz.com/gravity-forms-code-chest/) plugin.
 */
// Update "1" to your Nested Form field ID.
var gpnf = window.GPNestedForms_GFFORMID_1;

// The child field IDs which should be counted if they are answered. Add additional field Ids separated by a comma.
var sourceFieldIds = [ 4, 5 ];

// The target field ID into which the count of "answered" fields should be populated.
var targetFieldId = 6;

var $target = $( '#input_GFFORMID_' + targetFieldId );

function gwUpdateEntryValueCounts( entries ) {
	var count = 0;
	$.each( entries, function( index, entry ) {
		for ( var fieldId in entry ) {
			if ( ! entry.hasOwnProperty( fieldId ) || $.inArray( parseInt( fieldId ), sourceFieldIds ) === -1 ) {
				continue;
			}
			if ( entry[ fieldId ].value ) {
				count++;
			}
		}
	} );

	if ( $target.val() != count ) {
		$target.val( count ).change();
	}

}

gpnf.viewModel.entries.subscribe( function( entries ) {
	gwUpdateEntryValueCounts( entries );
} );

gwUpdateEntryValueCounts( gpnf.viewModel.entries() );
