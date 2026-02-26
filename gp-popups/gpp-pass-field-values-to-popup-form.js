/**
 * Gravity Perks // Popups // Pass Field Values to Popup Form
 * https://gravitywiz.com/documentation/gravity-forms-popups/
 *
 * Pass field values from a form embedded on the page into a popup form using
 * Gravity Forms dynamic population.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Update `feedId` with your popup feed ID.
 *
 * 3. Update the `fieldMap` array to map page form field IDs to their corresponding
 *    dynamic population parameter names configured on the popup form.
 */
( function() {

	var feedId = 123; // Update '123' to the ID of your Popup Feed.

	/**
	 * Map page form field IDs to popup form dynamic population parameter names.
	 *
	 * For compound fields (Name, Address), use the sub-field ID.
	 * e.g. { fieldId: '1_3', param: 'first_name' }
	 */
	var fieldMap = [
		{ fieldId: '1', param: 'parameter_name' },
		{ fieldId: '3', param: 'another_parameter_name' },
		// Add more mappings as needed.
	];

	function getFieldValue( fieldId ) {
		var field = document.getElementById( 'input_' + GFFORMID + '_' + fieldId );

		if ( field && /^(INPUT|SELECT|TEXTAREA)$/.test( field.tagName ) ) {
			// Multi-select: return comma-separated selected values
			if ( field.multiple && field.selectedOptions ) {
				return Array.prototype.map.call( field.selectedOptions, function( opt ) {
					return opt.value;
				} ).join( ',' );
			}

			return field.value;
		}

		// Radio/checkbox — query checked inputs inside the field container
		var container = document.getElementById( 'field_' + GFFORMID + '_' + fieldId );
		if ( ! container ) {
			return '';
		}

		var checkedInputs = container.querySelectorAll( '.gfield-choice-input:checked' );
		if ( checkedInputs.length ) {
			return Array.prototype.map.call( checkedInputs, function( input ) {
				return input.value;
			} ).join( ',' );
		}

		return '';
	}

	window.gform.addFilter( 'gpp_popup_config', function( config ) {
		if ( config.feedId !== feedId ) {
			return config;
		}

		var url = new URL( config.iframeUrl );

		fieldMap.forEach( function( mapping ) {
			var value = getFieldValue( mapping.fieldId );
			if ( value !== '' ) {
				url.searchParams.set( mapping.param, value );
			}
		} );

		config.iframeUrl = url.toString();

		return config;
	} );

} )();
