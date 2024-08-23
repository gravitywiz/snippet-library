/**
 * Gravity Perks // GP Conditional Pricing // Fuzzy Comparison for operator "is" (JS)
 * http://gravitywiz.com/documentation/gravity-forms-conditional-pricing/
 *
 * This snippet requires the PHP counterpart gpcp-fuzzy-comparison.php
 */
gform.addFilter('gform_is_value_match', function (isMatch, formId, rule) {
	if ( rule.operator != 'is' ) {
		return isMatch;
	}

	var source = jQuery( '#input_' + formId + '_' + rule.fieldId );
	if ( source ) {
		return fuzzyMatch( source.val(), rule.value );
	}

	return isMatch;
});

function normalizeString( str ) {
	return str.normalize("NFD").replace( /[\u0300-\u036f]/g, "" ).toLowerCase();
}

// Levenshtein Distance function
function levenshtein( a, b ) {
	const an = a.length;
	const bn = b.length;
	if (an === 0) return bn;
	if (bn === 0) return an;
	const matrix = [];
	for (let i = 0; i <= bn; i++) {
		matrix[i] = [i];
	}
	for (let j = 0; j <= an; j++) {
		matrix[0][j] = j;
	}
	for (let i = 1; i <= bn; i++) {
		for (let j = 1; j <= an; j++) {
			if (b.charAt(i - 1) === a.charAt(j - 1)) {
				matrix[i][j] = matrix[i - 1][j - 1];
			} else {
				matrix[i][j] = Math.min(
					matrix[i - 1][j - 1] + 1,
					Math.min(
						matrix[i][j - 1] + 1,
						matrix[i - 1][j] + 1
					)
				);
			}
		}
	}
	return matrix[bn][an];
}

function fuzzyMatch( input, target, threshold = 1 ) {
	const normalizedInput  = normalizeString( input );
	const normalizedTarget = normalizeString( target );
	const distance         = levenshtein( normalizedInput, normalizedTarget );
	return distance <= threshold;
}

