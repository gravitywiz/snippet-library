/**
 * Gravity Perks // Advanced Select // Auto Select on Match
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Automatically select the matching option based on the search value.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter( 'gpadvs_settings', function( settings, gpadvs, selectNamespace ) {
  if ( gpadvs.formId == GFFORMID ) {
    settings.onType = function( str ) {
      const tomSelect = window[ selectNamespace ];
      const matchedKey = Object.keys( tomSelect.options ).filter( function( key ) {
        return -1 !== tomSelect.options[ key ].text.toLowerCase().indexOf( str.toLowerCase() );
      } );

      if ( 1 === matchedKey.length ) {
        tomSelect.setValue( tomSelect.options[ matchedKey[0] ].id, false );
        // Especially after an option was previously selected, if the first
        // character typed next to resume search matches just one option,
        // the dropdown would remain open; blur closes it immediately.
        tomSelect.blur();
      }
    };
  }

	return settings;
} );
