/**
 * Gravity Perks // Populate Anything // Default the First Populated Choice for Radio having choices populated with GPPA
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */

// Update field ID to match your Radio Button.
var fieldID = 4; 
$('input:radio[name=input_' + fieldID + ']')[0].checked = true;
