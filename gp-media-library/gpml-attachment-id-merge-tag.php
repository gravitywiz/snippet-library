<?php
/**
 * Gravity Perks // Media Library // Merge Tag for Media Library IDs
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 *
 * Adds support for fetching a comma-delimited list of attachment IDs after GPML has imported the files into the
 * Media Library. Use the {entry:gpml_ids_FIELDID} merge tag.
 */
add_filter( 'gform_merge_tag_data', function( $data, $text, $form ) {
	foreach ( $form['fields'] as $field ) {
		if ( gp_media_library()->is_applicable_field( $field ) ) {
			$key = gp_media_library()->get_file_ids_meta_key( $field->id );
			if ( isset( $data['entry'][ $key ] ) ) {
				$data['entry'][ $key ] = implode( ',', $data['entry'][ $key ] );
			}
		}
	}
	return $data;
}, 10, 3 );
