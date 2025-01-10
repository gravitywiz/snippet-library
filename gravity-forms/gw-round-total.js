/**
 * Experimental Snippet 🧪
 */
// Round the total field
gform.addFilter( 'gform_product_total', function(total, formId){
	var interval = 1; // Change this to the nearest percision (e.g. 10 rounds to the nearest 10,20,30... etc.)
	var base = Math.round( total / interval );
	return base * interval;
} );
