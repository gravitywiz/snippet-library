<?php
/**
 * Gravity Perks // Entry Blocks // Show Edit Entry Timestamp on Entries Block.
 * http://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/af4e456263324971b3463ff344c77830
 *
 */
add_action( 'gform_after_update_entry', function ( $form, $entry_id ) {
	update_entry_meta( $entry_id );
}, 10, 2 );

add_action( 'gform_post_update_entry', function ( $entry, $original_entry ) {
	update_entry_meta( rgar( $entry, 'id' ) );
}, 10, 2 );

function update_entry_meta( $entry_id ) {
	$timestamp      = 'timestamp_' . $entry_id;
	$timestamp_meta = gform_get_meta( $entry_id, $timestamp );
	$current_user   = get_userdata( get_current_user_id() );
	$username       = $current_user->user_login;
	$current_value  = 'Entry updated at ' . current_datetime()->format( 'Y-m-d H:i:s' ) . ' by ' . $username . '.<br>';
	$timestamp_meta = $current_value . $timestamp_meta;
	gform_update_meta( $entry_id, $timestamp, $timestamp_meta );	
	gform_update_meta( $entry_id, 'gf_entry_editor', get_current_user_id() );	
}

add_filter( 'gform_replace_merge_tags', function ( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
	$custom_merge_tag = '{last_edited_by}';

	if ( strpos( $text, $custom_merge_tag ) === false ) {
		return $text;
	}
 
	$download_link = gform_get_meta( $entry['id'], 'gf_entry_editor' );

	return str_replace( $custom_merge_tag, $download_link, $text );
}, 10, 7 );

add_filter( 'gform_replace_merge_tags', function ( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
	$custom_merge_tag = '{entry_edit_timestamp}';

	if ( strpos( $text, $custom_merge_tag ) === false ) {
		return $text;
	}
	$timestamp = 'timestamp_' . $entry['id'];
	$download_link = gform_get_meta( $entry['id'], $timestamp );
	
	return str_replace( $custom_merge_tag, $download_link, $text );
}, 10, 7 );
