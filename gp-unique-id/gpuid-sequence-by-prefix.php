<?php
/**
 * Gravity Perks // Unique ID // Sequence by Prefix
 * https://gravitywiz.com/path/to/article/
 *
 * Create unique sequences based on the generated prefix. For example, if you have a Drop Down field where the user
 * can select A, B, or C, and you set the prefix of your Unique ID field to the merge tag of this Drop Down field, this
 * snippet will then treat each value as a unique sequence (e.g. A00001, A00002, B00001, A00003, B00002, etc).
 */
// Update "123" to your form ID and "4" to your Unique ID field ID.
add_filter( 'gpui_unique_id_attributes_123_4', function ( $atts, $form_id, $field_id, $entry ) {

	$prefix = GFCommon::replace_variables( $atts['prefix'], GFAPI::get_form( $form_id ), $entry, false, true, false, 'text' );

	$atts['slug'] = "seq-by-prefix-{$prefix}";

	return $atts;
}, 10, 4 );
