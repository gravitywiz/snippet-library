/**
 * Gravity Perks // Advanced Select // Caret Position
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Adds Caret Position to GP Advanced Select fields.
 * 
 * The Caret Position is a built in plugin of Tom Select.
 * @reference https://tom-select.js.org/plugins/caret-position/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
window.gform.addFilter(
    'gpadvs_settings',
    function(settings, gpadvsInstance, selectNamespace) {
        /**
         * Scope to only multiselect fields.
         */
        if (gpadvsInstance.fieldType === 'multiselect') {
            settings.plugins.caret_position = {
                title: 'Caret Position',
            };
        }

        return settings;
    }
);
