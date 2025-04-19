<?php
/**
 * Gravity Wiz // Gravity Forms // Remove Spaces Merge Tag Modifier
 * https://gravitywiz.com/
 *
 * Use the ":remove_spaces" modifier to remove any spaces within a merge tag value.
 *
 * Ideal for when using field values within a URL.
 *
 * Example: {:1:remove_spaces}
 */
add_filter( 'gform_merge_tag_filter', function( $value, $merge_tag, $modifier, $field_id ) {
  if ( ! rgblank( $value ) && $modifier === 'remove_spaces' ) {
    return str_replace( ' ', '', $value );
  }

  return $value;
}, 10, 4 );
