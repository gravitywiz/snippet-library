/**
 * Gravity Wiz // Gravity Forms // Prioritize Next Button Over Save and Continue When Pressing Enter
 * https://gravitywiz.com/
 *
 * Instructions:
 *    Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
$("#gform_GFFORMID")
	.find(".gform_save_link")
	.attr("type", "button");
