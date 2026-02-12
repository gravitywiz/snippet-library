/**
 * Gravity Perks // GP Popups // Instant Close on Form Submission
 * https://gravitywiz.com/documentation/gravity-forms-popups/
 *
 * Enable instant popup closing after form submission instead of the default 2-second delay.
 * This snippet provides immediate feedback to users by closing the popup as soon as the form
 * is successfully submitted, eliminating the waiting period.
 *
 * Instruction Video: https://www.loom.com/share/0aaf48acb14346d8b9486dac539057df
 *
 * Instructions:
 *     1. Install our free [Gravity Forms Code Chest](https://gravitywiz.com/gravity-forms-code-chest/) plugin.
 *     2. Copy and paste the snippet into the JavaScript section of Code Chest for the Popup form you wish to apply this snippet to.
 */
var $doc = window.parent ? jQuery(window.parent.document) : jQuery(document);

$doc.on('gp_popup_submitted', function(event) {
	var feedId = event.originalEvent.detail.feedId;

	if (window.parent.GPPopups && feedId) {
		window.parent.GPPopups.hide(feedId);
	}
});
