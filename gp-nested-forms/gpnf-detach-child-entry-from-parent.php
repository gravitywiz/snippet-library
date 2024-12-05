<?php
/**
 * Gravity Perks // Nested Forms // Metabox on Entry Details to detach child entry from parent.
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Instruction Video: https://www.loom.com/share/84a6fc86c75449baa544cd8080c3ed15
 */
add_filter( 'gform_entry_detail_meta_boxes', function ( $meta_boxes, $entry, $form ) {
	if ( class_exists( 'GP_Nested_Forms' ) ) {
		// Only to be displayed for Nested Entries.
		$parent_entry_id = rgar( $entry, 'gpnf_entry_parent' );
		if ( ! $parent_entry_id ) {
			return $meta_boxes;
		}

		// Either the metabox shows when Nested entry is still attached, or the metabox is skipped when parent link is detached.
		$action = 'gpnf_detach_parent_entry';
		if ( rgpost( 'action' ) == $action ) {
			$entry_id = $entry['id'];
			gform_delete_meta( $entry_id, 'gpnf_entry_parent' );
			gform_delete_meta( $entry_id, 'gpnf_entry_parent_form' );
		} else {
			$meta_boxes['gpnf_metabox'] = array(
				'title'    => 'Nested Forms',
				'callback' => 'detach_parent_entry_from_child',
				'context'  => 'side',
			);
		}
	}

	return $meta_boxes;
}, 10, 3 );

// Callback method
function detach_parent_entry_from_child( $args ) {
	$action = 'gpnf_detach_parent_entry';
	$html   = sprintf( '<input type="submit" value="%s" class="button" onclick="jQuery(\'#action\').val(\'%s\');" />', 'Detach from Parent', $action );

	echo $html;
}
