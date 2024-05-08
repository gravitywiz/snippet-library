/**
 * Gravity Perks // Nested Forms // Delete Child Entries When Parent Value Changes
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Overview Video:
 * https://www.loom.com/share/304641c3136049d0a8d1cf58c7041df5
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Configure the snippet for your form/fields based on the inline instructions.
 */
// Update "4" to the ID of the field on the parent that when changed should delete the child entries.
$( '#input_GFFORMID_4' ).on( 'change', function() {
	// Update "5" to the ID of the Nested Form field on the parent form.
	var gpnfA = window.GPNestedForms_GFFORMID_5;
	gpnfA.viewModel.entries().forEach( function( item ) {
		var $deleteButton = $( 'tr[data-entryid=' + item.id + ']' ).find( '.delete-button' );
		gpnfA.deleteEntry( item, $deleteButton );
	} );
} );
