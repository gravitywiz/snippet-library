<?php
/**
 * Gravity Forms // Populate Anything // Force Dynamic Population When Editing Entry.
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/6a7f28fc0cde406798d5bad4b386a70a
 *
 * Use this snippet to force fields to be dynamically repopulated via Populate Anything when they are
 * edited via the Gravity Forms edit entry screen.
 */
add_action( 'gform_after_update_entry', function ( $form, $entry_id ) {
	$gppa_lmt = GP_Populate_Anything_Live_Merge_Tags::get_instance();
	$entry    = GFAPI::get_entry( $entry_id );
	foreach ( $form['fields'] as $field ) {
		// For any field having Live Merge Tags.
		if ( $gppa_lmt->has_live_merge_tag( $field->defaultValue ) ) {
			$gppa_lmt->populate_lmt_whitelist( $form );
			remove_all_filters('gform_pre_replace_merge_tags');

			// Process the Live Merge Tags.
			$merge_tag = preg_replace( '/@(?=\{)/', '', $field->defaultValue );
			$value     = GFCommon::replace_variables( $merge_tag, $form, $entry );

			// Store updated value on the entry.
			GFFormsModel::update_entry_field_value( $form, $entry, $field, '', $field->id, $value );
		}
	}
}, 15, 2 );
