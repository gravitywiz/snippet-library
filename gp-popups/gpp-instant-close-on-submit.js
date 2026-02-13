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
 * 
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Configure the snippet based on inline instructions.
 *
 * Plugin Name:  GP Popups — Instant Close on Form Submission
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-popups/
 * Description:  Enable instant popup closing after form submission.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
var $doc = window.parent ? jQuery(window.parent.document) : jQuery(document);

$doc.on('gp_popup_submitted', function(event) {
	var feedId = event.originalEvent.detail.feedId;

	if (window.parent.GPPopups && feedId) {
		window.parent.GPPopups.hide(feedId);
	}
});
