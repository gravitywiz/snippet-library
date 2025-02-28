/**
 * Gravity Perks // Advanced Select // Clear Button
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Adds a Clearn button to selection options in Dropdown fields. By default,
 * the remove button is only added to Multi-Select fields.
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
        settings.plugins.clear_button = {
            title: 'Clear options',
        };

        return settings;
    }
);
