/**
 * Gravity Perks // Populate Anything // Custom Field Refresh Trigger
 * https://gravitywiz.comhttps://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
// Update "1" and "2" to the field IDs that should trigger a Populate Anything refresh.
$( '#field_GFFORMID_1, #field_GFFORMID_2' ).find( 'input' ).on( 'change', function() {
	// Update "5" to the ID of the field that should be refreshed.
	window.gppaForms[ GFFORMID ].bulkBatchedAjax( [ { field: 5 } ] );
} );
