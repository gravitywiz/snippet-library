/**
 * Gravity Perks // Nested Forms // Subscribe to Child Entry Updates
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * If you're having issues getting this to work, try using our [Gravity Forms Custom Javascript](https://gravitywiz.com/gravity-forms-code-chest/)
 * plugin. It handles ensuring the Nested Forms has fully initialized so you don't have to.
 */
// Get your Nested Forms JavaScript instance where "123" is the form ID and "4" is the Nested Form field ID.
var gpnf = window.GPNestedForms_123_4;

gpnf.viewModel.entries.subscribe( function( entries ) {
  // Do whatever you want any time the child entries for your Nested Form field are updated.
  console.log( entries );
} );
