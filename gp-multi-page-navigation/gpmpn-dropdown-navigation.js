/**
 * Gravity Perks // Multi-page Navigation // Dropdown Navigation
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 *
 * Instruction Video: https://www.loom.com/share/38fcc941036842d6a81a599fd8de39d7
 *
 * This snippet is designed to be used with the Gravity Forms Code Chest plugin.
 * https://gravitywiz.com/gravity-forms-code-chest/
 */
const pageNameTemplate = 'Step {pageNumber}: {pageName}';
function updateNavigationState(  ) {
	// hide if visible
	$( '#gf_page_steps_GFFORMID' ).attr( 'hidden', 'true' );

	var $select = $( '#page_steps_dropdown' );

	// Check if the select element already exists
	if ( $select.length === 0 ) {
		// Create a new select element if it does not exist
		$select = $( '<select id="page_steps_dropdown"></select>' );
		$( '#gf_page_steps_GFFORMID' ).after( $select );

		$select.on( 'change', function() {
			var selectedValue = $( this ).val(  );
			if ( selectedValue ) {
				var $matchingLink = $( '#gf_page_steps_GFFORMID .gf_step a[href="' + selectedValue + '"]' );
				if ( $matchingLink.length > 0 ) {
					$matchingLink[0].click();
				}
			}
		} );
	} else {
		$select.empty();
	}

	$( '#gf_page_steps_GFFORMID .gf_step' ).each( function(  ) {
		var $div       = $( this );
		var stepNumber = $div.find('.gf_step_number').text();
		var pageName   = $div.find('.gf_step_label').text().trim();
		var optionText = pageNameTemplate
            .replace('{pageNumber}', stepNumber)
            .replace('{pageName}', pageName);

		var $a      = $div.find('a');
		var $option = $('<option></option>').text( optionText );

		if ( $a.length > 0 ) {
			// found a tag, add the link to the dropdown option
			var href = $a.attr( 'href' );
			$option.attr( 'value', href );
		} else if ( $div.hasClass( 'gpmpn-step-current' ) ) {
			// current step should remain selected, but no link needed
			$option.attr( 'selected', 'selected' );
		} else {
			// page not yet traversed, should be disabled
			$option.attr( 'disabled', 'disabled' );
		}

		$select.append( $option );
	} );
}

// Initialize the navigation state on page load
setTimeout( updateNavigationState );

/* CSS rule to add:
#gf_page_steps_GFFORMID {
    display: none;
}
*/
