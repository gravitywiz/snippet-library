<?php
/**
 * Gravity Perks // Unique ID // Use Unique ID Field in Conditional Logic
 * https://gravitywiz.com/documentation/gravity-forms-unique-id/
 */
add_action( 'admin_footer', function() {
	if ( wp_script_is( 'gform_form_admin' ) && GFForms::get_page() !== 'form_editor' ) :
		?>
		<script>
			gform.addFilter( 'gform_is_conditional_logic_field', function( isConditionalLogicField, field ) {
				return isConditionalLogicField || field.type === 'uid';
			} );
		</script>
	<?php
	endif;
} );
