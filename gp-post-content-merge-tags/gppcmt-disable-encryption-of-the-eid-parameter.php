<?php
/**
 * Gravity Perks // Post Content Merge Tags  // Disable Encryption of the EID Parameter
 * https://gravitywiz.com/documentation/gravity-forms-post-content-merge-tags/
 *
 * By default, all eid parameters within GP Post Content Merge Tags are encrypted. 
 * This snippet is used to disable encryption of the Gravity Forms Entry ID.
 */
add_filter( 'gppcmt_encrypt_eid', '__return_false' );
