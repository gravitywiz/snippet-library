/**
 * Gravity Perks // Populate Anything // Refresh Field on Form B when Form A is Submitted
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
jQuery( document ).on( 'gform_confirmation_loaded', function( event, formId ) {
  // Update "123" to the ID of your GPPA-enabled form. Update "4" to the field ID of your GPPA-populated field to be refreshed.
  window.gppaForms[123].bulkBatchedAjax( [ { field: 1 } ] );
} );
