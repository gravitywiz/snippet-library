/**
 * Gravity Perks // Nested Forms // Refresh Markup in Nested Form B After Submission in Nested Form A
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 * 
 * Refresh the markup in Nested Form B after a child entry has been submitted in Nested Form field A. 
 * This is useful when combined with Populate Anything to allow entries submitted in one Nested Form 
 * field to be populated as choices in another.
 */
// Update "1" to your first Nested Form field ID and "2" to your second Nested Form field ID.
var gpnfA = window.GPNestedForms_GFFORMID_1;
var gpnfB = window.GPNestedForms_GFFORMID_2;

gpnfA.viewModel.entries.subscribe( function( entries ) {
	gpnfB.refreshMarkup();
} );
