<?php
/**
 * Gravity Perks // Address Autocomplete // Search by Place Name
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Instruction Video: https://www.loom.com/share/8b24df2d7de948b19a64a18d88797442
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
		gform.addFilter( 'gpaa_autocomplete_options', function( options ) {
			options.types = [ 'geocode', 'establishment' ];
			return options;
		} );
	</script>
	<?php
} );
