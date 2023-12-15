/**
 * Gravity Perks // Populate Anything // Default the First Populated Choice for Radio having choices populated with GPPA
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */

// Update "4" to the the ID of your Radio Button field.
$( 'input[id^=choice_' + GFFORMID + '_4_' )[0].checked = true;
