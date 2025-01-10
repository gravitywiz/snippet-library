/**
 * Gravity Wiz // Gravity Forms // Current Time Button
 * https://gravitywiz.com/
 *
 * Append an "Insert Current Time" button to Time fields that populates the current time when clicked.
 *
 * Instruction Video: 
 * 
 * https://www.loom.com/share/2cf02ee8447f4568bd78e2e566af07d8
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 * 
 * 2. Add "gw-current-time-button" to any Time field's "Custom CSS Class" setting.
 */
$('.gw-current-time-button').each(function() {
    var $fieldset = $(this);
    var $button = $('<button type="button" class="gform-theme-button--secondary" style="margin-top: var(--gf-field-gap-x, 12px);">Insert Current Time</button>');

    $button.click(function() {
        var now = new Date();
        var hours = now.getHours();
        var minutes = now.getMinutes();
        var ampm = 'AM';

        if ($fieldset.find('.gfield_time_ampm').length > 0) {
            if (hours >= 12) {
                ampm = 'PM';
            }
            if (hours > 12) {
                hours -= 12;
            } else if (hours === 0) {
                hours = 12;
            }
            $fieldset.find('.gfield_time_ampm select').val(ampm.toLowerCase());
        }

        // Ensure hours and minutes are two digits
        var formattedHours = ("0" + hours).slice(-2);
        var formattedMinutes = ("0" + minutes).slice(-2);

        $fieldset.find('.gfield_time_hour input').val(formattedHours);
        $fieldset.find('.gfield_time_minute input').val(formattedMinutes);
    });

    $fieldset.append($button);
});
