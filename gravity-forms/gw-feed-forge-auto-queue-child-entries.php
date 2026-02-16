<?php
/**
 * Gravity Wiz // Feed Forge // Auto-Queue Nested Forms Child Entries
 * https://gravitywiz.com/gravity-forms-feed-forge/
 *
 * When processing feeds of parent entries using Feed Forge, automatically queue the parent
 * entry's associated Nested Forms child entries immediately after, preserving the
 * parent-child ordering, which is especially useful for feeds like Google Sheets.
 */
class GW_Feed_Forge_Auto_Queue_Child_Entries {

	private $args = array();

	public function __construct( $args = array() ) {
		$this->args = wp_parse_args( $args, array(
			'parent_form_id'   => 0,
			'nested_field_id'  => 0,
			'child_feed_ids'   => array(),
			'throttle_seconds' => 0,
		) );

		add_action( 'gfff_entry_queued', array( $this, 'queue_child_entries' ), 10, 4 );

		if ( ! empty( $this->args['throttle_seconds'] ) ) {
			add_filter( 'wp_gf_feed_processor_seconds_between_batches', array( $this, 'maybe_throttle_feed_processor' ) );
		}
	}

	public function queue_child_entries( $entry, $feed, $form, $addon ) {
		if ( (int) $form['id'] !== (int) $this->args['parent_form_id'] ) {
			return;
		}

		$entry_id = $entry['id'];

		$child_entry_ids = $this->get_child_ids_from_field( $entry, $entry_id, $this->args['nested_field_id'] );

		if ( empty( $child_entry_ids ) ) {
			return;
		}

		$reprocess_feeds = gf_apply_filters( array( 'gfff_reprocess_feeds', $form['id'] ), false ) || rgpost( 'reprocess_feeds' ) === 'true';

		foreach ( (array) $this->args['child_feed_ids'] as $child_feed_id ) {
			$this->queue_entries_for_feed( $child_entry_ids, $child_feed_id, $reprocess_feeds );
		}
	}

	private function queue_entries_for_feed( $entry_ids, $feed_id, $reprocess_feeds ) {
		$feed = GFAPI::get_feed( $feed_id );

		if ( ! $feed || is_wp_error( $feed ) ) {
			return;
		}

		$addon = $this->get_addon_for_feed( $feed );

		if ( ! $addon ) {
			return;
		}

		foreach ( $entry_ids as $child_entry_id ) {
			if ( $reprocess_feeds ) {
				GWiz_GF_Feed_Forge::clear_processed_feeds( $child_entry_id, $feed, $addon );
			}

			gf_feed_processor()->push_to_queue( array(
				'addon'    => get_class( $addon ),
				'feed'     => $feed,
				'entry_id' => $child_entry_id,
				'form_id'  => $feed['form_id'],
			) );
		}
	}

	public function maybe_throttle_feed_processor( $seconds ) {
		$throttle_seconds = (int) $this->args['throttle_seconds'];

		if ( $throttle_seconds <= 0 ) {
			return $seconds;
		}

		// Only throttle during active Feed Forge batch processing
		if ( ! $this->is_feed_forge_batch_active() ) {
			return $seconds;
		}

		return max( $seconds, $throttle_seconds );
	}

	private function is_feed_forge_batch_active() {
		if ( ! class_exists( 'GWiz_GF_Feed_Forge' ) ) {
			return false;
		}

		$batch_option_names = get_transient( GWiz_GF_Feed_Forge::TRANSIENT_CURRENT_BATCH_OPTION_NAMES );

		return is_array( $batch_option_names ) && ! empty( $batch_option_names );
	}

	private function get_child_ids_from_field( $entry, $entry_id, $field_id ) {
		$raw = rgar( $entry, $field_id );

		if ( empty( $raw ) ) {
			$raw = gform_get_meta( $entry_id, $field_id );
		}

		if ( empty( $raw ) ) {
			return array();
		}

		return $this->parse_child_ids( $raw );
	}

	private function parse_child_ids( $raw ) {
		if ( empty( $raw ) || ! is_string( $raw ) ) {
			return array();
		}

		return array_filter( array_map( 'intval', explode( ',', $raw ) ) );
	}

	private function get_addon_for_feed( $feed ) {
		foreach ( GFAddOn::get_registered_addons() as $addon_class ) {
			$addon = call_user_func( array( $addon_class, 'get_instance' ) );

			if ( $addon instanceof GFFeedAddOn && $addon->get_slug() === $feed['addon_slug'] ) {
				return $addon;
			}
		}

		return null;
	}

}

# Configuration

new GW_Feed_Forge_Auto_Queue_Child_Entries( array(
	'parent_form_id'  => 123,
	'nested_field_id' => 4,
	'child_feed_ids'  => array( 56 ),
	/**
	 * Optional: Add delay between feed processing to prevent rate limit errors when processing Google Sheets feeds
	 * that can break parent->child ordering. This is normally only necessary when bulk processing 100+ entries,
	 * but can vary based on various factors. A 2-3 second delay is usually sufficient to avoid rate limits.
	 */
	// 'throttle_seconds' => 3,
) );
