<?php
/**
 * Gravity Wiz // Gravity Forms // Choice Counter
 * http://gravitywiz.com/count-the-total-number-of-checked-checkboxes-with-gravity-forms/
 */

// Configuration
new GW_Choice_Count( array(
	'form_id'          => 123,          // The ID of your form.
	'count_field_id'   => 4,            // Any Number field on your form in which the number of checked checkboxes should be dynamically populated; you can configure conditional logic based on the value of this field.
	'choice_field_ids' => array( 5, 6 ) // Any array of Checkbox or Multi-select field IDs which should be counted.
) );
