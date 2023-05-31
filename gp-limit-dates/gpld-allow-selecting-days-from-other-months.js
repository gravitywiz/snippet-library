/**
 * Gravity Wiz // Gravity Forms // Allow Selecting Days From Another Month in Datepicker
 * https://gravitywiz.com/
 *
 * By default you have to click "next month" or "previous month" to be able to select days from
 * those months, even if they're available and visible in the calendar. This snippet allow days 
 * from other months to be selectable in the calendar month view.
 * 
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 *
 * Limitations:
 * 
 * Only works with inline date picker right now.
 *
 */

// When the page is ready
window.addEventListener('load', function () {
  if (document.querySelector('body') !== null) {
    
    jQuery("[id^=datepicker_GFFORMID_]").datepicker( "option", "showOtherMonths", true );
    jQuery("[id^=datepicker_GFFORMID_]").datepicker( "option", "selectOtherMonths", true );
    
  }
});
