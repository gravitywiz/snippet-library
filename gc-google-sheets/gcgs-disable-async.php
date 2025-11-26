<?php
/**
 * Gravity Connect // Google Sheets // Disable Async on Google Sheets Feeds
 *
 * By default, GC Google Sheets feeds run asynchronously. This snippet will disable that, so they run in the same request as the form submission.
 *
 * This is useful for debugging whether there is a wider issue with asynchronous feeds on a site.
 *
 * Installation:
 *   1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gform_is_feed_asynchronous', function( $is_async, $feed, $entry, $form ) {
  if ( $feed['addon_slug'] === 'gp-google-sheets' ) {
    return false;
  }

  return $is_async;
}, 50, 4 );
