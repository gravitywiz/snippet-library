<?php
/**
 * Gravity Perks // Populate Anything // Add Live Merge Tag to Whitelist
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
function add_ip_lmt_to_whitelist ($whitelist, $form) {

    $merge_tag = '{ip}';
    
    $whitelist[ $merge_tag ] = wp_create_nonce( 'gppa-lmt-' . $form['id'] . '-' . $merge_tag );
    
    return $whitelist;
}
add_filter( 'gppa_lmt_whitelist', 'add_ip_lmt_to_whitelist', 10, 2 );
