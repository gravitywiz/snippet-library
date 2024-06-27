<?php
/**
 * Gravity Perks // Populate Anything // Encrypt File Upload Field Links
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Encrypt File Upload Links when populated with GP Populate Anything.
 *
 * Note: This will disable the default behavior of file upload field encryption on Gravity Forms.
 * Instead, it will manually encrypt the file upload links directly on the Gravity Forms Entry.
 */
add_filter( 'gform_secure_file_download_location', '__return_false' );

// Update 729 and 1 to the Form ID and Field ID of the dynamically populated field loading File Upload field links.
add_filter( 'gform_save_field_value_729_1', 'encrypt_links', 10, 4 );
function encrypt_links( $value, $lead, $field, $form ) {
	$upload_root = GFFormsModel::get_upload_url( $form['id'] );
	$upload_root = trailingslashit( $upload_root );

	if ( strpos( $value, $upload_root ) !== false ) {
		$value        = str_replace( $upload_root, '', $value );
		$download_url = site_url( 'index.php' );
		$args         = array(
			'gf-download' => urlencode( $value ),
			'form-id' => $form['id'],
			'field-id' => $field['id'],
			'hash' => GFCommon::generate_download_hash( $form['id'], $field['id'], $value ),
		);
		$download_url = add_query_arg( $args, $download_url );

		return $download_url;
	}
	return $value;
}
