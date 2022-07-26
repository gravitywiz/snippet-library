<?php
/**
 * Gravity Perks // GP Populate Anything // Change The Query Limit
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
function example_increase_limit_to_1000 ($query_limit) {
    return 1000;
}

// Replace 123 with your Form ID and 4 with your Field ID
add_filter( 'gppa_query_limit_123_4', 'example_increase_limit_to_1000' );
