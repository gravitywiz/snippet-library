/**
 * Gravity Perks // Nested Forms // Subscribe to Child Entry Updates
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Experimental Snippet 🧪
 */
// Get your Nested Forms JavaScript instance where "4" is the Nested Form field ID.
var gpnf = window.GPNestedForms_GFFORMID_13;
var entryCount = 0;
var doingItLive = false;

gpnf.viewModel.entries.subscribe( function( entries ) {
	if ( ! doingItLive ) {
		entryCount = entries.length;	
	}
}, null, 'beforeChange' );

gpnf.viewModel.entries.subscribe( function( entries ) {
	// Check if an entry was added.
	if ( ! doingItLive && entries.length > entryCount ) { 
		doingItLive = true;
		gpnf.viewModel.entries.splice( 0, 0, gpnf.viewModel.entries.pop() );
		entryCount = entries.length;
		doingItLive = false;
	}
} );
