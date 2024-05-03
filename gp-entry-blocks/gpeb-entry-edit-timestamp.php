<?php
/**
 * Gravity Perks // Entry Blocks // Show Edit Entry Timestamp on Entries Block.
 * http://gravitywiz.com/
 * 
 * Adds `{entry_edit_timestamp}` merge tag to display Edit Entry Timestamp.
 * 
 * Also adds `{last_edited_by}` merge tag to display the User who last edited the entry.
 *
 * Instruction Video: https://www.loom.com/share/af4e456263324971b3463ff344c77830
 *
 */
class GPEB_Entry_Edit_Timestamp {

	public function __construct() {

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_action( 'gform_after_update_entry', array( $this, 'update_meta_after_update_entry' ), 10, 2 );
		add_action( 'gform_post_update_entry', array( $this, 'update_meta_post_update_entry' ), 10, 2 );
		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_last_edited_by' ), 10, 7 );
		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_entry_edit_timestamp' ), 10, 7 );

	}

	public function update_meta_after_update_entry( $form, $entry_id ) {

		$this->update_entry_meta( $entry_id );
	}

	public function update_meta_post_update_entry( $entry, $original_entry ) {

		$this->update_entry_meta( rgar( $entry, 'id' ) );
	}

	function update_entry_meta( $entry_id ) {

		$timestamp      = 'timestamp_' . $entry_id;
		$timestamp_meta = gform_get_meta( $entry_id, $timestamp );
		$current_user   = get_userdata( get_current_user_id() );
		$username       = $current_user->user_login;
		$current_value  = 'Entry updated at ' . current_datetime()->format( 'Y-m-d H:i:s' ) . ' by ' . $username . '.<br>';
		$timestamp_meta = $current_value . $timestamp_meta;

		gform_update_meta( $entry_id, $timestamp, $timestamp_meta );
		gform_update_meta( $entry_id, 'last_edit_by', get_current_user_id() );
	}

	public function replace_last_edited_by( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

		$custom_merge_tag = '{last_edited_by}';

		if ( strpos( $text, $custom_merge_tag ) === false ) {
			return $text;
		}

		$last_edit_by = gform_get_meta( $entry['id'], 'last_edit_by' );

		return str_replace( $custom_merge_tag, $last_edit_by, $text );
	}

	public function replace_entry_edit_timestamp( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

		$custom_merge_tag = '{entry_edit_timestamp}';

		if ( strpos( $text, $custom_merge_tag ) === false ) {
			return $text;
		}

		$timestamp       = 'timestamp_' . $entry['id'];
		$timestamp_value = gform_get_meta( $entry['id'], $timestamp );

		return str_replace( $custom_merge_tag, $timestamp_value, $text );
	}

}

# Configuration

new GPEB_Entry_Edit_Timestamp();
