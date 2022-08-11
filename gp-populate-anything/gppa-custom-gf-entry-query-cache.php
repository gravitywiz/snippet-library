<?php
/**
 * Gravity Perks // GP Populate Anything // Custom GF_Entry Query Cache
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * This may improve performance but is known to return incorrect results when multiple fields are populated and chained to each other.
 */
add_filter( 'gppa_query_cache_hash', function( $query_cache_hash, $object_type, $args ) {
    if ( $object_type === 'gf_entry' ) {
        return sha1( sprintf( '%s-%s-%s-%s-%s',
            $args['field']->formId,
            json_encode( $args['filter_groups'] ),
            json_encode( $args['ordering'] ),
            json_encode( $args['primary_property_value'] ),
            json_encode( $args['unique'] )
        ) );
    }
    return $query_cache_hash;
}, 10, 3 );
