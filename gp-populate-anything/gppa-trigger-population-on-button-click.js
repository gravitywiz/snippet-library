/**
 * Gravity Perks // GP Populate Anything // Trigger Population on Button Click
 *
 * Instead of automatically populating fields when an input is changed, trigger the population when a button is clicked.
 *
 * Instructions:
 *  1. Add snippet to form using https://gravitywiz.com/gravity-forms-code-chest/
 *  2. Customize inputButton and optionally buttonSelector to match your form the desired field
 *  3. Add HTML field to your form and add the following code to the HTML field:
 *      <button class="trigger-gppa">Click Me</button>
 */
var inputSelector = '#input_FORMID_FIELDID';
var buttonSelector = '.trigger-gppa';

function disableListener() {
    $(inputSelector).data('gppaDisableListener', true);
}

disableListener();

$(document).on('gppa_updated_batch_fields', function( event, formId, fieldIds ) {
    disableListener();
});

$('#gform_GFFORMID').on('click', buttonSelector, function(event) {
    // Prevent submission of form when this button is clicked
    event.preventDefault();

    $(inputSelector)
        .data('gppaDisableListener', false)
        .trigger('forceReload');
});
