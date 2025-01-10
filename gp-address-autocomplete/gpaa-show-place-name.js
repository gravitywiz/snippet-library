/**
 * Gravity Perks // Address Autocomplete // Search & Show by Place Name
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Find addresses by their name. For example, "Mount Trashmore" in Virginia Beach would resolve to
 * an address of "310 Edwin Drive, Virginia Beach, VA 23462".
 *
 * Instructions:
 *     1. Install this snippet with our free Code Chest plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-code-chest/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */

// Enable search by Place Name
gform.addFilter( 'gpaa_autocomplete_options', function( options ) {
	options.types = [ 'geocode', 'establishment' ];
	options.fields.push( 'name' );
	return options;
} );

// Display Place Name
gform.addAction( 'gpaa_fields_filled', function ( place, instance, formId, fieldId ) {
	jQuery( '#input_{0}_{1}_1'.gformFormat( formId, fieldId ) ).val( place.name ).trigger('change');
} );
