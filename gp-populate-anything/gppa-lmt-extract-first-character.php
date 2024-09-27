<?php
/**
 * Gravity Perks // Populate Anything // Extract First Character From Live Merge Tag
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Extract the first character of a string using when a Live Merge Tag.
 * This is useful when you need the first letter of a name, for example.
 */
// Update "123" to your form ID; and "4" to the field ID you are copying from.
add_filter( 'gppa_live_merge_tag_value_123_4', function( $value ) {
  return substr( $value, 0, 1 );
} );
