<?php
/**
 * Gravity Wiz // Gravity Forms // Notes Merge Tag
 * https://gravitywiz.com/
 *
 * Include entry notes in notifications (and other places merge tags are supported after an entry has been created).
 *
 * Instructions
 *
 * 1. Use {notes} to show all notes.
 * 2. Use {notes:last} to show the last submitted note.
 */
add_filter( 'gform_replace_merge_tags', function ( $text, $form, $entry ) {

	if ( strpos( $text, '{notes' ) === false || ! preg_match_all( '/{notes(?::([a-z]+))?}/', $text, $matches, PREG_SET_ORDER ) ) {
		return $text;
	}

	foreach ( $matches as $match ) {

		list( $search, $modifier ) = array_pad( $match, 2, '' );

		$notes = GFAPI::get_notes( array(
			'entry_id' => $entry['id'],
		) );

		if ( $modifier === 'last' ) {
			$notes = array( array_pop( $notes ) );
		}

		$notes_markup = '';

		if ( ! empty( $notes ) ) {
			ob_start();
			GFEntryDetail::notes_grid( $notes, false );
			$notes_markup = ob_get_clean();
		}

		$text = str_replace( $search, $notes_markup, $text );

	}

	return $text;
}, 10, 3 );
