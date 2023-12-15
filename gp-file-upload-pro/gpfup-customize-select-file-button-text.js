/**
 * Gravity Perks // File Upload Pro // Customize the “select file” Button Text for a Specific Field
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instructions:
 *   1. Install our free Custom Javascript for Gravity Forms plugin.
 *      Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *   2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */
window.gform.addFilter( 'gpfup_strings', function( strings, formId, fieldId ) {
	// Update '123' to the Form ID and '4' to the File Upload Field ID.
	if ( formId != 123 || fieldId != 4 ) {
		return strings;
	}
		
	strings.select_files = 'Select an image';
	
	return strings;
} );
