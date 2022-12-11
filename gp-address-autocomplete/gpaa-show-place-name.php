<?php
/**
 * Gravity Perks // Address Autocomplete // Search & Show by Place Name
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Find addresses by their name. For example, "Mount Trashmore" in Virginia Beach would resolve to
 * an address of "310 Edwin Drive, Virginia Beach, VA 23462".
 *
 * Developer's Note: The JS in this snippet cannot be included via the Custom Javascript plugin due
 * to an order-of-events issue with GPAA calling the options filter before the Custom Javascript plugin
 * has initialized.
 */
// Update "123" to your form ID or remove "_123" to apply to all forms.
add_action( 'gform_pre_enqueue_scripts_123', function() {
	?>
	<script>
		// Enable search by Place Name
		gform.addFilter( 'gpaa_autocomplete_options', function( options ) {
			options.types = [ 'geocode', 'establishment' ];
			return options;
		} );
		// Display Place Name
		gform.addAction('gpaa_fields_filled', function (place, instance, formId, fieldId) {
			// Update "123" to your form ID, update _1_1 to your field ID. If your field ID is 4, this would be _4_1.
			jQuery('#input_123_1_1').val( place.name );
			return place;
		} );
	</script>
	<?php
} );
