/**
 * Gravity Wiz // Populate Anything // Firefox Select Fixer
 * https://gravitywiz.com/
 *
 * Experimental Snippet 🧪
 *
 * Firefox will auto-fill fields (including select fields) with the selected option upon
 * refresh. This can be problematic with Populate Anything if fields or Live Merge Tags rely
 * upon the value of the select as Firefox does not trigger any events. To work around this, we
 * compare the values between the select field and the option with the selected attribute. If they
 * differ, we trigger a forceReload event which is an event Populate Anything listens for.
 *
 * Installation:
 *    1. Install and Activate https://gravitywiz.com/gravity-forms-code-chest/
 *    2. Navigate to Form Settings > Custom JavaScript and add this snippet.
 */
jQuery('#gform_GFFORMID').find('select').each(function() {
	var $el = $(this);
	var $selOption = $el.find('option[selected="selected"]');

	if ($selOption.val() !== $el.val()) {
		$el.trigger('forceReload');
	}
});
