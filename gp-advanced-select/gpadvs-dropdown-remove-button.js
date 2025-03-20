/**
 * Gravity Perks // Advanced Select // Dropdown Remove Button
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * Adds a Remove button to selection options in Dropdown and MultiSelect fields.
 * 
 * The Remove Button is a built in plugin of Tom Select.
 * @reference https://tom-select.js.org/plugins/remove-button/ 
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
window.gform.addFilter(
    'gpadvs_settings',
    function(settings, gpadvsInstance, selectNamespace) {
        settings.plugins.remove_button = {
            title: window.GPADVS.strings?.remove_this_item
                ? window.GPADVS.strings.remove_this_item
                : 'Remove this item',
        }

        return settings;
    }
);
