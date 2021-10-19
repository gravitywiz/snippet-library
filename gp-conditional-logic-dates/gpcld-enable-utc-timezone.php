<?php
/**
 * Gravity Perks // Conditional Logic Dates // Adjust User's Local Time to UTC
 * https://gravitywiz.com/documentation/gravity-forms-conditional-logic-dates/
 */
// Update "123" to the ID of your form - or - remove to apply to all forms.
add_action( 'gform_pre_render_123', function( $form ) {
	add_action( 'wp_footer', 'gpcld_enable_utc_timezone_script' );
	add_action( 'gform_preview_footer', 'gpcld_enable_utc_timezone_script' );
	function gpcld_enable_utc_timezone_script() {
		?>
		<script>
			gform.addFilter( 'gpcld_enable_utc_timezone', function( enable ) {
				return true;
			} );
		</script>
		<?php
	}
	return $form;
} );
