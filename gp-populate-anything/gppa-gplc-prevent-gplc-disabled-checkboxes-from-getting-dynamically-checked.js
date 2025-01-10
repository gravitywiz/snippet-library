/**
 * Gravity Perks // Populate Anything // Prevent Dynamic Checking of any disabled checkbox.
 * https://gravitywiz.comhttps://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
$(document).ready(function() {

	function uncheckDisabledCheckboxes() {
		$('.gplc-pre-disabled:checkbox:disabled').prop('checked', false);
	}

	var observer = new MutationObserver(function(mutationsList) {
		for (var mutation of mutationsList) {
			if (mutation.type === 'childList' && mutation.addedNodes.length) {
				$(mutation.addedNodes).find('.gplc-pre-disabled:checkbox:disabled').prop('checked', false);
			}
		}
	});

	observer.observe(document, { childList: true, subtree: true });
});
