/**
 * Gravity Perks // Advanced Select // Search From Start of Label
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * By default, Advanced Select will return any item whose label contains the search query. This
 * snippet will change the search algorithm to only return items whose label starts with the
 * search query.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
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
			if ( item.text.toLowerCase().startsWith( search ) ) {
				// High score for items starting with search query
				return 1;
			}
			// Zero score for items not starting with search query
			return 0;
		};
	};
	return settings;
} );
