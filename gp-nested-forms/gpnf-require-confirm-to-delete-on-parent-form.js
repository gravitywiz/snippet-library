/**
 * Gravity Perks // Nested Forms // Require Confirmation to Delete Child Entry on Parent Form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Require users to confirm delection of a child entry on the parent form. This works the same was as the
 * confirm-to-delete requirement in the child form.
 *
 * Instructions:
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
window.gform.addFilter( 'gpnf_should_delete', function( shouldDelete, items, $trigger, gpnf ) {
	if ( ! $trigger.is('.gpnf-row-actions .delete-button') ) {
		return shouldDelete;
	}
	if ( ! $trigger.data( 'isConfirming' ) ) {
		$trigger
			.data( 'isConfirming', true )
			.text( gpnf.modalArgs.labels.confirmAction );
		setTimeout( function() {
			$trigger
				.data( 'isConfirming', false )
				.text( gpnf.modalArgs.labels.delete );
		}, 3000 );

		return false;
	}
	return shouldDelete;
} );
