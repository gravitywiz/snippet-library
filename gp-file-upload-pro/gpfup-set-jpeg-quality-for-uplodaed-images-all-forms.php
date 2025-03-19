<?php
/**
 * Gravity Perks // File Upload Pro // Set JPEG Quality for all Uploaded Images (All Forms)
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 */
add_action( 'gform_pre_enqueue_scripts', function( $form ) {
	if ( ! is_callable( 'gp_file_upload_pro' ) || ! gp_file_upload_pro()->should_enqueue_frontend( $form ) ) {
		return;
	}
	?>
	<script>
		window.gform.addFilter( 'gpfup_jpeg_quality', function () {
			// Update "1" to the desired quality. 1 represents 100%.
			return 1;
		} );
	</script>
	<?php
} );
