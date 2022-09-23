/**
 * Gravity Wiz // Gravity Forms // Disable Selected Dropdown Choice in Other Dropdown Fields
 * https://gravitywiz.com/
 *
 * Disable a selected dropdown choice in other dropdown fields that have the same set of choices.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * 2. Add the `gfield_ddselect` designator to all the dropdown fields' CSS Class Name setting.
 */
jQuery(".gfield_ddselect").change(function () {
	var select_num = jQuery(".gfield_ddselect").length;
	var ddvalue = [];
	var selected = jQuery("option:selected", jQuery(this)).val();
	jQuery(".gfield_ddselect").each(function (i) {
		ddvalue[i] = jQuery("option:selected", jQuery(this)).val();
		i += 1;
	});
	var thisID = jQuery(this).attr("id");
	jQuery(".gfield_ddselect").each(function (a) {
		var parent_el = jQuery(this);
		jQuery("option", jQuery(this)).each(function (b) {
			jQuery(this).prop("disabled", false);
			if (b != 0) {
				for (c = 0; c < select_num; c++) {
					if (a != c) {
						if (ddvalue[c] != '') {
							jQuery("option[value='" + ddvalue[c] + "']", parent_el).attr("disabled", true);
						}
					}
				}
			}
			b += 1;
		});
		a += 1;
	});
});
