<?php
/**
 * Gravity Wiz // Gravity Forms // Display Entries Left Based on Entry Limit
 *
 * https://gravitywiz.com/display-number-of-entries-left-based-on-entry-limit/
 *
 * @version  1.0
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/
 */

// update the "4" to the ID of your form
add_action('gform_pre_render_1', 'gform_display_limit');
function gform_display_limit($form) {

    // put the %s wherever you want the number of entries to display in your message
    $entries_left_message = 'Only %s positions left!';

    /* You do not need to edit below this line */

    $entry_count = RGFormsModel::get_lead_count($form['id'], '');
    $entries_left = $form["limitEntriesCount"] - $entry_count;

    if($entries_left > 0) {
        $form['description'] .= sprintf('<div class="entries-left">' . $entries_left_message . '</div>', $entries_left);
    }

    return $form;
}
