/**
 * Gravity Perks // Populate Anything // Listen for Populate Anything Updates on a Specific Field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
$( document ).on( 'gppa_updated_batch_fields', function( e, formId, updatedFieldIDs ) {
	var targetFieldId = 3;
	if ( parseInt( formId ) === GFFORMID && $.inArray( String( targetFieldId ), updatedFieldIDs ) !== -1 ) {
		console.log( 'Target field updated!' );
	}
} );
