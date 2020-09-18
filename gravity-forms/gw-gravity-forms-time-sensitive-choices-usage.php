<?php
/**
 * Gravity Wiz // Gravity Forms // Time Sensitive Choices
 * https://gravitywiz.com/time-sensitive-choices-with-gravity-forms/
 */

// Time Sensitive Choices
new GW_Time_Sensitive_Choices( array(
	'form_id' => 964,
	'field_ids' => array( 10, 12, 13 ),
	'time_mod' => '+1 hours',
) );

// Link a Date field to the Choice field when using GF Limit Choices and Field Groups.
new GW_Time_Sensitive_Choices( array(
	'form_id' => 964,
	'field_ids' => array( 10, 12, 13 ),
	'time_mod' => '+1 hours',
	'date_field_id' => 1,
) );
