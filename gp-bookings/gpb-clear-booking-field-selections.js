/**
 * Gravity Perks // Bookings // Clear Booking Field Selections
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Adds a "Clear" button that resets all selections tied to a Booking field —
 * the linked Service field (in manual mode), any linked Resource fields, and
 * the selected Booking time field slot.
 *
 * Instructions
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Add an HTML field to your form with a button that has a
 *    `data-booking-field-id` attribute set to your Booking field's ID.
 *
 *    Example: `<button type="button" data-booking-field-id="4">Clear</button>`
 */
( function () {
	var $form = jQuery( '#gform_' + GFFORMID );
	if ( ! $form.length ) {
		return;
	}

	function findBookingInstance( bookingFieldId ) {
		var instances = window.gpBookings || {};
		for ( var key in instances ) {
			if ( instances[ key ].formId === GFFORMID && instances[ key ].bookingFieldId === parseInt( bookingFieldId, 10 ) ) {
				return instances[ key ];
			}
		}
		return null;
	}

	function clearChoiceField( fieldId ) {
		var $radios = $form.find( 'input[type="radio"][name^="input_' + fieldId + '"]' );
		if ( $radios.length ) {
			var $checked = $radios.filter( ':checked' );
			$radios.prop( 'checked', false );
			( $checked.length ? $checked : $radios.first() ).trigger( 'change' );
			return;
		}
		jQuery( '#input_' + GFFORMID + '_' + fieldId ).val( '' ).trigger( 'change' );
	}

	$form.off( 'click.gpbClear' ).on( 'click.gpbClear', 'button[data-booking-field-id]', function ( event ) {
		event.preventDefault();

		var instance = findBookingInstance( this.getAttribute( 'data-booking-field-id' ) );
		if ( ! instance ) {
			return;
		}

		( instance.resourceFieldIds || [] ).forEach( clearChoiceField );

		if ( instance.serviceFieldId ) {
			clearChoiceField( instance.serviceFieldId );
		}

		var state = instance.store.getState();
		state.setSelectedRange( undefined );
		state.setShowCalendar( true );
	} );
} )();
