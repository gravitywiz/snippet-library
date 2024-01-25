/**
 * Gravity Perks // Populate Anything // Replace Field with Spinner Instead of Pulsing
 * https://gravitywiz.comhttps://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Instruction Video: https://www.loom.com/share/3ed196b95f1544b7aa250101f1b1a783
 * 
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
gform.addFilter('gppa_loading_field_target_meta', function (targetMeta, $elem, context) {
	/* Only modify behavior in loading context */
	if (context !== 'loading') {
		return targetMeta;
	}

	var spinner = document.createElement('div');

	targetMeta[0].find('input, select, textarea').hide();

	targetMeta[0].prepend(spinner);
	targetMeta[0] = jQuery(spinner);
	targetMeta[1] = 'gppa-spinner';

	return targetMeta;
});
