<?php
/**
 * Gravity Perks // Entry Blocks // Shows a log of edits that have been made to an entry.
 * http://gravitywiz.com/
 *
 * * Adds `{entry_edit_log}` merge tag to display Edit Entry Timestamp.
 * * Adds `{last_edited_by}` merge tag to display the User who last edited the entry.
 *
 * Instruction Video: https://www.loom.com/share/af4e456263324971b3463ff344c77830
 *
 */
class GPEB_Entry_Edit_Log {
	const META_KEY = 'gw_entry_edit_log';

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		add_action( 'gform_after_update_entry', array( $this, 'update_meta_after_update_entry' ), 10, 2 );
		add_action( 'gform_post_update_entry', array( $this, 'update_meta_post_update_entry' ), 10, 2 );
		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_last_edited_by' ), 10, 7 );
		add_filter( 'gform_replace_merge_tags', array( $this, 'replace_entry_edit_log' ), 10, 7 );

	}

	public function update_meta_after_update_entry( $form, $entry_id ) {
		$this->update_entry_meta( $entry_id );
	}

	public function update_meta_post_update_entry( $entry, $original_entry ) {
		$this->update_entry_meta( rgar( $entry, 'id' ) );
	}

	function update_entry_meta( $entry_id ) {
		$log = $this->get_log( $entry_id );

		// Use timestamp as key to allow for sorting
		$time = current_time( 'mysql' );

		$log[ $time ] = array(
			'time' => $time,
			'user' => get_current_user_id()
		);

		gform_update_meta( $entry_id, self::META_KEY, $log );
	}

	public function get_log( $entry_id ) {
		$log = gform_get_meta( $entry_id, self::META_KEY );

		if ( empty( $log ) || ! is_array( $log ) ) {
			$log = array();
		}

		return $log;
	}

	public function get_last_edit( $entry_id ) {
		$log = $this->get_log( $entry_id );

		if ( empty( $log ) ) {
			return null;
		}

		// Get the last edit, which is the last item in the array
		$last_edit = end( $log );

		return $last_edit;
	}

	public function replace_last_edited_by( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
		$custom_merge_tag = '{last_edited_by}';

		if ( strpos( $text, $custom_merge_tag ) === false ) {
			return $text;
		}

		$last_edit = $this->get_last_edit( $entry['id'] );

		if ( empty( $last_edit ) ) {
			return str_replace( $custom_merge_tag, '', $text );
		}

		$last_edit_by = get_user_by( 'id', $last_edit['user'] )->display_name;

		return str_replace( $custom_merge_tag, $last_edit_by, $text );
	}

	public function replace_entry_edit_log( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
		$custom_merge_tag = '{entry_edit_log}';

		if ( strpos( $text, $custom_merge_tag ) === false ) {
			return $text;
		}

		$log = $this->get_log( $entry['id'] );

		if ( empty( $log ) ) {
			return str_replace( $custom_merge_tag, '', $text );
		}

		$log_formatted = '<ul>';

		foreach ( $log as $edit ) {
			$user = get_user_by( 'id', $edit['user'] );
			$log_formatted .= sprintf( '<li>Entry updated at %s by %s.</li>', mysql2date( 'Y-m-d H:i:s', $edit['time'] ), $user->display_name );
		}

		$log_formatted .= '</ul>';

		return str_replace( $custom_merge_tag, $log_formatted, $text );
	}
}

# Configuration

new GPEB_Entry_Edit_Log();
