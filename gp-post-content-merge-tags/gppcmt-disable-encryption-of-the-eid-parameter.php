<?php
/**
 * Gravity Perks // Post Content Merge Tags // Disable Encryption of the EID Parameter
 * https://gravitywiz.com/documentation/gravity-forms-post-content-merge-tags/
 *
 * By default, the `eid` parameter is encrypted. This snippet is used to disable encryption revealing
 * the raw Gravity Forms entry ID.
 */
add_filter( 'gppcmt_encrypt_eid', '__return_false' );
