/**
 * Gravity Perks // Pay Per Word // Surprise, Pay Per Character! (JS)
 * https://gravitywiz.com/documentation/gravity-forms-pay-per-word/
 *
 * This snippet requires the PHP counterpart gpppw-pay-per-character.php
 */
gform.addFilter( 'gpppw_word_count', function( wordCount, text, gwppw, ppwField, formId ) {
	// Pay per character instead of words.
	var words = text.split( '' );
	return words == null ? 0 : words.length;
} );
