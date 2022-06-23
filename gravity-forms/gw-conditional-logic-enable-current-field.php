<?php
/**
 * Gravity Wiz // Gravity Forms // Enable Current Field in Conditional Logic
 * https://gravitywiz.com/
 *
 * By default, the current field being edited cannot contain a conditional logic rule based on itself. This makes sense
 * in most contexts except one really helpful scenario — when you want to hide a field if its empty!
 *
 * This snippet will allow you to create conditional logic rules based on the currently selected field in the form
 * editor. This works with any of Gravity Forms methods for dynamic population including our powerful Populate Anything
 * perk.
 *
 * Plugin Name:  Gravity Forms — Enable Current Field in Conditional Logic
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Enable use of the current field when creating conditional logic rules in the form editor.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_action( 'admin_footer', function() {
	if ( wp_script_is( 'gform_form_admin' ) ) :
		?>
		<script>
			gform.addFilter( 'gform_is_conditional_logic_field', function( isSupported, field ) {
				// Only apply our logic in the form editor when the current field is selected and its visibility is not set to "administrative".
				if ( GetSelectedField() && GetSelectedField().id == field.id && field.visibility !== 'administrative' ) {
					var inputType = GetInputType( field );
					isSupported = jQuery.inArray( inputType, GetConditionalLogicFields() ) !== -1;
				}
				return isSupported;
			} );
		</script>
		<?php
	endif;
} );
