/**
 * Gravity Perks // Nested Forms // Add Confirmation To Delete Buttons In The Parent Form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * This snippet adds a confirmation to the Delete buttons in the parent form much like the confirmation when deleting from the Edit modal
 *
 * We recommend installing this snippet with our free Custom Javascript plugin:
 * https://gravitywiz.com/gravity-forms-custom-javascript/
 */
window.gform.addFilter('gpnf_should_delete', function(shouldDelete, items, $trigger, gpnf) {
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
});
