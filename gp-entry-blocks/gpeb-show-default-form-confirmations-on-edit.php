<?php
/**
 * Gravity Perks // Entry Blocks // Show Default Form Confirmations on Edit.
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Use confirmation message from Form Settings when editing an entry in GPEB.
 * 
 * Instruction Video: https://www.loom.com/share/4bdd738bbd0342548807c2dffd93bd73
 */

/**
* @param array $confirmation The edit confirmation.
* @param array $form The current form.
* @param array $entry The entry being edited.
 *
 * @return string
 */
add_filter( 'gpeb_edit_confirmation', function ( $confirmation, $form, $entry ) {

    // Replace with your form ID.
    if ( $form['id'] != 137 ) {
        return $confirmation;
    }

    // Populates the form confirmation property with the confirmation to be used for the current submission.
    $form = GFFormDisplay::update_confirmation( $form, $entry );
    return $form['confirmation'];
}, 10, 3 );
