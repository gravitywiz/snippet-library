/**
 * Gravity Perks // GP Populate Anything // Change The Query Limit For a Specific Field
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Change the query limit to 1000 for a specific field on a form.
 *
function example_increase_limit_to_1000 ($query_limit, $object_type) {
	return 1000;
}
add_filter( 'gppa_query_limit_1_3', 'example_increase_limit_to_1000', 10, 2 );
