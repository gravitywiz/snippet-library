<?php
/**
 * Gravity Perks // Populate Anything // Increase Max String Length of the Form Editor.
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gform_admin_pre_render', function ( $form ) {
	?>
	<script type="text/javascript">
		gform.addFilter('gppa_form_editor_max_string_length', function(form) {
			// Skip max string length check by returning false.
			return false;
	});

	</script>
	<?php
	return $form;
} );
