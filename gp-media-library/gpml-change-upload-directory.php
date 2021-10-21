<?php
/**
 * Gravity Perks // Media Library // Change Upload Directory
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 *
 * Customize the directory to which GP Media Library will import files for a specific form or field.
 */
// Using "gpml_media_data" filter to ensure we only change upload directory for GPML-enabled uploads.
// Update "123" to your form ID.
add_filter( 'gpml_media_data_123', function( $return ) {
	$modify_upload_dir = function( $upload_dir ) {
		// Update the path and URL as desired.
		$upload_dir['path'] = WP_CONTENT_DIR . '/uploads/my-custom-folder';
		$upload_dir['url']  = WP_CONTENT_URL . '/uploads/my-custom-folder';
		return $upload_dir;
	};
	add_filter( 'upload_dir', $modify_upload_dir );
	// The "wp_handle_upload" filter is triggered after the file has been imported into the Media Library. Remove our
	// upload directory modifier function.
	add_filter( 'wp_handle_upload', function( $return ) use ( $modify_upload_dir ) {
		remove_filter( 'upload_dir', $modify_upload_dir );
		return $return;
	} );
	return $return;
} );
