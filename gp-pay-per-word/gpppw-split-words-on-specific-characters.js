/**
 * Gravity Perks // Pay Per Word // Split Words on Specific Characters (JS)
 * https://gravitywiz.com/documentation/gravity-forms-pay-per-word/
 *
 * This snippet requires the PHP counterpart gpppw-split-words-on-specific-characters.php
 */
gform.addFilter( 'gpppw_word_count', function( wordCount, text, gwppw, ppwField, formId ) {
	// Splits words on periods, underscores, and asterisks.
	var words = text.replace( /[\.\_\*]/g, ' ' ).match( /\S+/g );
	return words == null ? 0 : words.length;
} );
