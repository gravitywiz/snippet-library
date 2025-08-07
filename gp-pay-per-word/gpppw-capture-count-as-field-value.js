/**
 * Gravity Perks // Pay Per Word // Capture Word Count as Field Value
 * https://gravitywiz.com/documentation/gravity-forms-pay-per-word/
 *
 * Use ths snippet alongside our [GF Custom JavaScript][1] plugin to capture the number of words
 * in another field. This is useful if you'd like to implement conditional logic based on the
 * number of words.
 *
 * [1]: https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter( 'gpppw_word_count', function( wordCount ) {
	// Update "2" to the ID of the field which should be populated with the word count.
	var $field = $( '#input_GFFORMID_2' );

	// Only update if value is actually different
		if ( $field.val() != wordCount ) {
		$field.val( wordCount).change();
	}

	return wordCount;
}, 11 );
