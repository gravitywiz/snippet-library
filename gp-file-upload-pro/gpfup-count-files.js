/**
* Experimental Snippet 🧪
*/
jQuery(function() {
  // Update "123" to your form ID and "4" to your File Upload field ID.
	if (typeof window['GPFUP_123_4'] === 'undefined') {
		return;
	}
	
  // Update "123" to your form ID and "4" to your File Upload field ID.
	var store = window.GPFUP_123_4.$store;
	
	store.subscribe(function(mutation, state) {
		if (mutation.type !== 'SET_FILES') {
			return;
		}
		// Update "5" to your Single Product field ID - or - update `#input_123_5_1` to your desired input's HTML ID.
		jQuery( '#input_123_5_1' ).val( state.files.length ).change();
	});
});
