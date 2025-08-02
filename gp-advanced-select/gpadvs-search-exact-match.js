/**
 * Gravity Perks // Advanced Select // Search For Exact Match
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * By default, Advanced Select will return any item whose label contains the search query. This
 * snippet will change the search algorithm to only return items whose label matches the
 * search query exactly.
 *
 * Instruction Video: https://www.loom.com/share/4266734e5ab14870ba6b8bba28d01f68
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
gform.addFilter( 'gpadvs_settings', function( settings, gpadvs ) {
	settings.score = function(search) {
		if ( ! search ) {
			return function() {
				// Item has no search query, return all items
				return 1;
			};
		}
		search = search.toLowerCase();
		return function(item) {
			if ( item.text.toLowerCase() === search ) {
				// High score for items matching search query exactly
				return 1;
			}
			// Zero score for items not matching search query
			return 0;
		};
	};
	return settings;
} );
