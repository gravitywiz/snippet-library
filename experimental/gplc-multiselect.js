    var formId      = 560; // Change this to the form ID
    var fieldId     = 2;   // Change this to the field ID
    var maxSelected = 2;   // Number to limit maximum selected options to
    $('#input_' + formId + '_' + fieldId + ' option').on('click change keyup blur', function () {
        var $select = $(this).parent();
        var disable = ($select.find('option:checked').length === maxSelected) ? true : false;
        $select.find('option:not(:checked)').prop( 'disabled', disable );
    });
