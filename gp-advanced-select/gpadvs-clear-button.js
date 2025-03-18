/**
 * Gravity Perks // Advanced Select // Clear Button
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Adds a Clear buton to GP Advanced Select fields.
 * 
 * The Clear Button is a built in plugin of Tom Select.
 * @reference https://tom-select.js.org/plugins/clear-button/
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
         * Scope to only dropdown fields.
         * This can also be changed to 'multiselect' to only target multi-select fields
         * OR the conditional can be removed to apply to all fields.
         */
        if (gpadvsInstance.fieldType === 'dropdown') {
            settings.plugins.clear_button = {
                title: 'Clear options',
            };
        }

        return settings;
    }
);
