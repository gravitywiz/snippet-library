<?php
/**
 * Gravity Perks // QR Code // Enable Scanner Setting for Paragraph Text fields.
 * https://gravitywiz.com/documentation/gravity-forms-qr-code/
 */
add_action( 'gform_editor_js', function() {
	?>
	<script>
		window.gform.addFilter( 'gpqr_is_supported_field', function( isSupported, field ) {
			if ( GetInputType( field ) === 'textarea' ) {
				isSupported = true;
			}
			return isSupported;
		} );
	</script>
	<?php
} );
