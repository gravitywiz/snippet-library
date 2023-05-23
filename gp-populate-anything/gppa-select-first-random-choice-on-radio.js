/**
 * Gravity Perks // Populate Anything // Default the First Populated Choice for Radio having choices populated with GPPA
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */

// click vs checked: Click triggers other GPPA Fields to be populated appropriately as well, whereas checked does not trigger further reloads.
// Update "3" to the the ID of your Radio Button field, and keep it as "0" for for the first choice.
// Rename "SelectDefaultRoomType" to something unique.
const SelectDefaultRoomType = document.getElementById('choice_' + GFFORMID + '_3_0'); SelectDefaultRoomType.click();

