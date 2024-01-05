/**
 * Gravity Wiz // Gravity Forms // Field Notes
 * https://gravitywiz.com/
 *
 * Add a field setting for capturing notes about the field. This is useful for documenting the purpose of the field or
 * why it was configured a certain way.
 *
 * Plugin Name:  Gravity Forms Field Notes Setting
 * Plugin URI:   https://gravitywiz.com
 * Description:  Add a field setting for capturing notes about the field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_action( 'gform_editor_js', function () {
	?>
	<script type="text/javascript">
		(
			function ( $ ) {

				// Register our Field Notes setting with all field types.
				$( document ).ready( function () {
					for ( fieldType in fieldSettings ) {
						fieldSettings[fieldType] += ', .gw-field-notes-setting';
					}
				} );

				// Populate our Field Notes setting when field is selected.
				$( document ).bind( 'gform_load_field_settings', function ( event, field, form ) {
					$( '#gw-field-notes' ).val( field['gwFieldNotes'] );
				} );

			}
		)( jQuery );
	</script>
	<?php
} );

add_filter( 'gform_field_standard_settings', function ( $position ) {
	// Display our Field Notes setting below the Description setting.
	if ( $position == 50 ) {
		?>
		<li class="gw-field-notes-setting field_setting">
			<label for="gw-field-notes">Field Notes</label>
			<textarea id="gw-field-notes" onkeyup="SetFieldProperty('gwFieldNotes', this.value);"></textarea>
		</li>
		<?php
	}
} );
