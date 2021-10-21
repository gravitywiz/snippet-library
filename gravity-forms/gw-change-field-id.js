/**
 * Gravity Wiz // Gravity Forms // Change Field ID via Browser Console
 * https://gravitywiz.com/changing-your-gravity-forms-field-ids/
 *
 * Provides a simple function for changing the ID of a Gravity Forms field via the browser console from the Form Editor page.
 * 
 * Instructions:
 *
 * 1. Open the desired form in the Form Editor.
 * 
 * 2. Open your Browser Console
 * 
 * 		Chrome:  https://developer.chrome.com/devtools/docs/console
 * 		Firefox: https://developer.mozilla.org/en-US/docs/Tools/Browser_Console
 * 		IE:      https://msdn.microsoft.com/en-us/library/gg589530(v=vs.85).aspx
 *
 * 3. Copy and paste this snippet into the console. Now the gwChangeFieldId() function is available for use on this page.
 * 
 * 4. Provide the current field ID and the new field ID to the gwChangeFieldId() function. 
 *
 * 		Example: Current Field ID is 4, New Field ID is 12
 *
 * 		gwChangeFieldId( 4, 12 );
 *
 * 5. Click the "Update Form" button to save your changes.
 *
 * Video Instruction: https://www.screencast.com/t/STm1eLZEsR9q
 */
gwChangeFieldId = function( currentId, newId ) {

	for( var i = 0; i < form.fields.length; i++ ) {
		if( form.fields[i].id == currentId ) {
			form.fields[i].id = newId;
			jQuery( '#field_' + currentId ).attr( 'id', 'field_' + newId );
			if( form.fields[i].inputs ) {
				for( var j = 0; j < form.fields[i].inputs.length; j++ ) {
					form.fields[i].inputs[j].id = form.fields[i].inputs[j].id.replace( currentId + '.', newId + '.' );
				}
			}
			return 'Success!';
		}
	}

	return 'Failed.'
}
