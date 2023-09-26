<?php
/**
 * Gravity Perks // File Renamer // Apply Rename to Every Field
 * https://gravitywiz.com/documentation/gravity-forms-file-renamer/
 *
 * This applies GPFR to every single field, no matter if there is a "template value" set for the field or not.
 *
 * This is useful when combined with "gpfr_filename_template" filter to automatically apply the filtered template
 * to all fields.
 *
 * Requires GP File Renamer v1.0.6+
 */
add_filter( 'gpfr_is_applicable_field', function ( $should_apply_gpfr, $form, $field ) {
	return true;
}, 10, 3 );


/**
 * It is recommended to also register a filter callback for `gpfr_filename_template` to ensure that
 * any file upload fields without a template set in the form settings will still get a valid template.
 *
 * Without inlcuding this, any file upload fields without a template set will automatically assume that the
 * intended file name is an empty string which will likely cause unintended behavior.
 */
add_filter( 'gpfr_filename_template', function( $template, $file, $form, $field, $entry ) {
	// if no template is set for this field, default to storing each file in a directory name by the entry id.
	if ( empty( $template ) ) {
		return '{entry_id}/{filename}';
	}

	return $template;
}, 10, 5 );
