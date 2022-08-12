/**
 * Gravity Perks // GP Populate Anything // Change The Query Limit To For All Dynamically Populated Fields
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Change the query limit to 750 for all dynamically populated fields. 
 *
function example_increase_limit_to_750 ($query_limit, $object_type) {
    if ($object_type->id !== 'post') {
        return $query_limit;
    }

    return 750;
}
add_filter( 'gppa_query_limit', 'example_increase_limit_to_750', 10, 2 );
