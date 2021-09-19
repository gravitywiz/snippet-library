/**
 * Gravity Perks // Nested Forms // Subscribe to Child Entry Updates
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
// Get your Nested Forms JavaScript instance where "123" is the form ID and "4" is the Nested Form field ID.
var gpnf = window.GPNestedForms_123_4;

gpnf.viewModel.entries.subscribe( function( entries ) {
  // Do whatever you want any time the child entries for your Nested Form field are updated.
  console.log( entries );
} );
