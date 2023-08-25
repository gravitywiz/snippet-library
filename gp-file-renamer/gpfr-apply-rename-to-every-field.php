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
