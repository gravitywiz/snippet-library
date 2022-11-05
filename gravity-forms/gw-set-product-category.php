<?php


add_action( 'gform_advancedpostcreation_post_after_creation', 'update_product_information', 10, 4 );
function update_product_information( $post_id, $feed, $entry, $form ){
// Update "5" to the ID of the field whose value should be used for the minimum range.
        $cat_ids = array( (int)$entry['1'],(int)$entry['3'],(int)$entry['4'],(int)$entry['5']//,and ect ); //setting your data $entry['5'] 5-ID fileld dropdown list
        wp_set_object_terms( $post_id, $cat_ids, 'product_cat' );
}
