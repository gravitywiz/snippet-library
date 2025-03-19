<?php
/**
 * Gravity Perks // Populate Anything // Disable Max String Length for Option Labels
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
