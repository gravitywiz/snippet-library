/**
 * Gravity Perks // GP File Upload Pro // Prevent cropper from closing Beaver Builder modal that a form is embedded in
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instructions:
 *     1. Install our free Custom JavaScript for Gravity Forms plugin.
 *         Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 *     3. Update the form ID/field ID (e.g. GPFUP_24_1) and Beaver Builder modal ID (e.g. pp_modal_fl6amr4d57jn) accordingly.
 */
gform.addAction( 'gpfup_uploader_ready', function ( gpfup ) {
	var store = window.GPFUP_24_1.$store;

	if ( !store ) {
		return;
	}

	store.subscribe( function ( mutation, state ) {
		switch ( mutation.type ) {
			case 'OPEN_EDITOR':
				pp_modal_fl6amr4d57jn.settings.click_exit = false;
				break;

			case 'REPLACE_FILE':
				pp_modal_fl6amr4d57jn.settings.click_exit = true;
				break;
		}
	} );
} );
