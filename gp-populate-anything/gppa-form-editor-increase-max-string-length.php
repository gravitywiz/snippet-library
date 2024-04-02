<?php
/**
 * Gravity Perks // Populate Anything // Increase Max String Length of the Form Editor.
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gform_admin_pre_render', function ( $form ) {
	?>
	<script type="text/javascript">
		gform.addFilter('gppa_form_editor_max_string_length', function(form) {
			// Increase the length from default 50 to your choice.
			return 1000;
	});

	</script>
	<?php
	return $form;
} );
