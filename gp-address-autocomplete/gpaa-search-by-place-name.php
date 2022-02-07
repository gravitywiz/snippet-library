<?php
/**
 * Gravity Perks // Address Autocomplete // Search by Place Name
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Find addresses by their name. For example, "Mount Trashmore" in Virginia Beach would resolve to
 * an address of "310 Edwin Drive, Virginia Beach, VA 23462".
 *
 * Developer's Note: The JS in this snippet cannot be included via the Custom Javascript plugin due
 * to an order-of-events issue with GPAA calling the options filter before the Custom Javascript plugin
 * has initialized.
 */
add_action( 'gform_preview_header', 'gpaa_search_by_place_name' );
add_action( 'wp_head', 'gpaa_search_by_place_name' );
function gpaa_search_by_place_name() {
	?>
	<script>
		gform.addFilter( 'gpaa_autocomplete_options', function( options ) {
			options.types = [ 'geocode', 'establishment' ];
			return options;
		} );
	</script>
	<?php
}
