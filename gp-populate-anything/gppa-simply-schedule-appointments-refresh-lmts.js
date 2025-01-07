/**
 * Gravity Perks // GP Populate Anything // Refresh Live Merge Tags pointing to Simply Schedule Appointments field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything
 *
 * ## STOP! ##
 * 
 * This snippet is deprecated and no longer required if you are using the latest version of Simply Schedule Appointments.
 * 
 * ## Original ##
 *
 * By default, if using a Simply Schedule Appointments field and you select an appointment time, any Live Merge Tags
 * referencing the SSA field will not automatically update due to SSA not firing a change event on the hidden input.
 *
 * This snippet works around the issue by utilizing a MutationObserver that watches the hidden input's value attribute.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
var observer = new MutationObserver( function ( mutationList ) {
	mutationList.forEach( function () {
		window.gppaForms[GFFORMID].bulkBatchedAjax( [] );
	} );
} );

observer.observe( document.querySelector( '.ssa_appointment_form_field_appointment_id' ), {
	attributeFilter: ['value'],
	attributeOldValue: true
} );
