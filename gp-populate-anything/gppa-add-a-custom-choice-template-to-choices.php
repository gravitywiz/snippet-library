/**
 * Gravity Perks // Populate Anything // Add A Custom Choice Template To Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_action( 'admin_print_footer_scripts', function () {
	if ( ! is_callable( 'GFCommon::is_form_editor' ) || ! GFCommon::is_form_editor() ) {
		return;
	}
	?>
	<script>
	window.gform.addFilter('gppa_template_rows', function (templateRows, field, populate) {
		if (populate !== 'choices') return templateRows;
			templateRows.push({
			id: 'image',
			label: 'Image',
		})
	return templateRows;
	});
	</script>
	<?php
} );
