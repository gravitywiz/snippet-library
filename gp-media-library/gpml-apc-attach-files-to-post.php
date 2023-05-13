<?php
/**
 * Gravity Wiz // Gravity Forms // Attach Files uploaded via GPML to Posts created with GFAPC + ACF
 *
 * Attach Files uploaded via GPML to Posts created with GFAPC + ACF
 *
 * Instructions:
 *  1. Install per https://gravitywiz.com/how-do-i-install-a-snippet/
 */
add_action( 'gform_advancedpostcreation_post_after_creation', function( $post_id, $feed, $entry, $form ) {
	foreach ( $feed['meta']['postMetaFields'] as $post_meta_field ) {
		$field_id         = rgar( $post_meta_field, 'value' );
		$field            = GFAPI::get_field( $form, $field_id );
		$is_media_library = rgar( $field, 'uploadMediaLibrary' );

		if ( $is_media_library ) {
			$file_ids = gp_media_library()->get_file_ids( $entry['id'], $field->id );
			foreach ( $file_ids as $file_id ) {
				wp_update_post( array(
					'ID'          => $file_id,
					'post_parent' => $post_id,
				) );
			}
		}
	}
}, 10, 4 );
