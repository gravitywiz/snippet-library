<?php
/**
 ** Gravity Wiz // Gravity Forms // Populate Date One Year from Current Date
 * https://gravitywiz.com/populate-date-one-year-from-current-date/
 *
 */
add_filter('gform_field_value_year_from_date', function() {
	return date('Y-m-d', strtotime('+1 year'));
} );
