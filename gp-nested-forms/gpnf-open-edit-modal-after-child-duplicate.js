/**
 * Gravity Perks // Nested Forms // Open Edit Modal After Child Entry Is Duplicated
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * We recommend installing this snippet with our free Custom Javascript plugin:
 * https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addAction( 'gpnf_post_duplicate_entry', function( entry, data, response ) {
	jQuery( 'tr[data-entryId="{0}"]'.format( entry.id ) ).find( '.edit a' ).click();
});
