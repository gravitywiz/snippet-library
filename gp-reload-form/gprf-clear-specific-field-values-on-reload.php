<?php
/**
 * Gravity Perks // Reload From // Clear Specific Field Values on Reload
 * https://gravitywiz.com/documentation/gravity-forms-reload-form/
 *
 * When reloading a form, you may want to preserve the submitted values in some values and remove them in others. This
 * snippet provides a basic mechanism for achieving this. Be sure to enable the "Preserve values from previous submission"
 * form setting in the "Reload Form" section.
 */
// Update "123" to your form ID.
add_filter( 'gprf_disable_dynamic_reload_123', function( $return ) {

	$form_id         = 123;
	$fields_to_clear = array( 2, 3, 4 ); // array of field IDs to clear

	add_filter( 'gform_pre_render_' . $form_id, function( $form ) use ( $fields_to_clear ) {

		foreach ( $fields_to_clear as $field_id ) {
			$field = GFAPI::get_field( $form, $field_id );

			if ( ! $field ) {
				continue; // skip if field not found
			}

			if ( $field->type === 'checkbox' ) {
				foreach ( $_POST as $key => $value ) {
					if ( strpos( $key, "input_{$field_id}" ) === 0 ) {
						$_POST[ $key ] = '';
					}
				}
			} elseif ( $field->type === 'fileupload' ) {
				if ( isset( $_POST['gform_uploaded_files'] ) ) {
					$uploaded_files = json_decode( stripslashes( $_POST['gform_uploaded_files'] ), true );
					if ( isset( $uploaded_files[ "input_{$field_id}" ] ) ) {
						unset( $uploaded_files[ "input_{$field_id}" ] );
						$_POST['gform_uploaded_files'] = wp_json_encode( $uploaded_files );
					}
				}
				GFFormsModel::$uploaded_files[ $form['id'] ][ "input_{$field_id}" ] = array();
			} else {
				$_POST[ "input_{$field_id}" ] = '';
			}
		}

		return $form;
	} );

	return $return;
} );
