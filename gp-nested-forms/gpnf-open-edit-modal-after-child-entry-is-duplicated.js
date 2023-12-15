/**
 * Gravity Perks // Nested Forms // Open Edit Modal After Child Entry Is Duplicated
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addAction( 'gpnf_post_duplicate_entry', function( entry, data, response ) {
	$( 'tr[data-entryId="{0}"]'.gformFormat( entry.id ) ).find( '.edit a' ).click();
} );
