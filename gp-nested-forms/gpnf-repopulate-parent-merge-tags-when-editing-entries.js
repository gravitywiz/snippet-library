/**
 * Gravity Perks // Nested Forms // Repopulate Parent Merge Tags When Editing Entries
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 *
 * Use this snippet to repopulate {Parent} merge tag when editing entries, replacing any previously saved values.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
window.gform.addFilter( 'gpnf_replace_parent_merge_tag_on_edit', function () {
	 return true;
} );
