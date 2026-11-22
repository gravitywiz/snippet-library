<?php
/**
 * Gravity Perks // Popups // Modify Popup Config by Gravity Forms Entry
 * https://gravitywiz.com/documentation/gravity-forms-popups/
 *
 * Show, hide, or customize a popup based on whether the current user has
 * already submitted a Gravity Forms form. For example, stop showing a lead
 * magnet popup to people who already claimed it, show a review request only
 * to customers who have placed an order, or pre-fill a popup form with a
 * returning visitor's name and email from their last submission.
 *
 * Instructions:
 *
 * 1. Install this snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. Update the configuration at the bottom of the snippet.
 *
 * Settings:
 *
 * - `popup_feed_id`: The ID of the popup to modify.
 * - `form_id`: The ID of the form to check for entries.
 * - `if_entry` / `if_no_entry`: What to do when the user has (or doesn't have) a
 *   matching entry. Use one or both. Each accepts the actions below.
 * - `current_user_only` (optional): By default, only the current logged-in
 *   user's entries are counted. Set to false to count entries from anyone.
 * - `field_filters` (optional): Only count entries matching specific values.
 *   Pass an array of filters, each with a `key` and `value`. The `key` can be
 *   a field ID (e.g. '3'), an entry property (e.g. 'payment_status'), or an
 *   entry meta key. Filters are combined with AND.
 *
 * Actions (used inside if_entry / if_no_entry):
 *
 * - `is_active`: Set to false to stop the popup from showing.
 * - `iframe_query_args`: Add query parameters to the popup form's URL (useful
 *   for dynamically populating fields with values from the previous entry).
 * - `set_config`: Override any popup setting using a dot path, e.g.
 *   'behavior.respectDismissal' => false.
 *
 * You can use these placeholders in action values:
 *
 * - `{entry:id}`, `{entry:1}`, `{entry:1.3}`, `{entry:payment_status}` — values from
 *   the user's latest matching entry (the entry ID, field 1, input 1.3, or
 *   any entry property/meta key).
 * - `{count}` — how many matching entries were found.
 * - `{user_id}` — the current user's ID.
 */

class GPP_Modify_Popup_By_GF_Entry {

	private $rule = array();

	public function __construct( array $rule ) {
		$this->rule = $rule;

		add_filter( 'gpp_popup_config', array( $this, 'filter_popup_config' ), 10, 2 );
	}

	public function filter_popup_config( $config, $feed ) {
		if ( ! class_exists( 'GFAPI' ) || ! $this->applies_to_popup( $config ) ) {
			return $config;
		}

		$context = $this->get_entry_context();
		$actions = $context['has_entry'] ? rgar( $this->rule, 'if_entry', array() ) : rgar( $this->rule, 'if_no_entry', array() );

		return $this->apply_actions( $config, $actions, $context );
	}

	private function applies_to_popup( $config ) {
		if ( empty( $this->rule['popup_feed_id'] ) || empty( $config['feedId'] ) ) {
			return false;
		}

		return (int) $this->rule['popup_feed_id'] === (int) $config['feedId'];
	}

	private function get_entry_context() {
		$context = array(
			'count'     => 0,
			'has_entry' => false,
			'entry'     => null,
			'user_id'   => get_current_user_id(),
		);

		$current_user_only = rgar( $this->rule, 'current_user_only', true );

		if ( empty( $this->rule['form_id'] ) || ( $current_user_only && ! is_user_logged_in() ) ) {
			return $context;
		}

		$entries = GFAPI::get_entries(
			(int) $this->rule['form_id'],
			$this->get_search_criteria( $current_user_only ),
			array(
				'key'       => 'date_created',
				'direction' => 'DESC',
			),
			array(
				'offset'    => 0,
				'page_size' => 1,
			),
			$total_count
		);

		if ( ! is_wp_error( $entries ) ) {
			$context['count']     = (int) $total_count;
			$context['has_entry'] = $total_count > 0;
			$context['entry']     = isset( $entries[0] ) ? $entries[0] : null;
		}

		return $context;
	}

	private function get_search_criteria( $current_user_only ) {
		$field_filters = (array) rgar( $this->rule, 'field_filters', array() );

		if ( $current_user_only ) {
			$field_filters[] = array(
				'key'   => 'created_by',
				'value' => get_current_user_id(),
			);
		}

		$search_criteria = array(
			'status' => 'active',
		);

		if ( ! empty( $field_filters ) ) {
			$search_criteria['field_filters'] = $field_filters;
		}

		return $search_criteria;
	}

	private function apply_actions( $config, $actions, $context ) {
		if ( empty( $actions ) || ! is_array( $actions ) ) {
			return $config;
		}

		if ( array_key_exists( 'is_active', $actions ) ) {
			$config['isActive'] = (bool) $actions['is_active'];
		}

		if ( ! empty( $actions['iframe_query_args'] ) && is_array( $actions['iframe_query_args'] ) ) {
			$query_args = array();

			foreach ( $actions['iframe_query_args'] as $key => $value ) {
				$query_args[ $key ] = $this->replace_placeholders( $value, $context );
			}

			$config['iframeUrl'] = add_query_arg( $query_args, $config['iframeUrl'] );
		}

		if ( ! empty( $actions['set_config'] ) && is_array( $actions['set_config'] ) ) {
			foreach ( $actions['set_config'] as $path => $value ) {
				$config = $this->set_config_value( $config, $path, $this->replace_placeholders( $value, $context ) );
			}
		}

		return $config;
	}

	private function replace_placeholders( $value, $context ) {
		if ( ! is_string( $value ) ) {
			return $value;
		}

		return preg_replace_callback( '/\{([^}]+)\}/', function( $matches ) use ( $context ) {
			$key = $matches[1];

			if ( strpos( $key, 'entry:' ) === 0 ) {
				return (string) rgar( (array) $context['entry'], substr( $key, 6 ), '' );
			}

			$tokens = array(
				'count'   => (string) $context['count'],
				'user_id' => (string) $context['user_id'],
			);

			return isset( $tokens[ $key ] ) ? $tokens[ $key ] : $matches[0];
		}, $value );
	}

	private function set_config_value( $config, $path, $value ) {
		$keys = explode( '.', (string) $path );
		$last = array_pop( $keys );
		$ref  = &$config;

		foreach ( $keys as $key ) {
			if ( ! isset( $ref[ $key ] ) || ! is_array( $ref[ $key ] ) ) {
				$ref[ $key ] = array();
			}

			$ref = &$ref[ $key ];
		}

		$ref[ $last ] = $value;

		return $config;
	}
}

# Configuration

// Hide a popup from users who have already submitted a specific form.
new GPP_Modify_Popup_By_GF_Entry( array(
	'popup_feed_id' => 8,
	'form_id'       => 25,
	'if_entry'      => array(
		'is_active' => false,
	),
) );

// Example: pass values from a user's previous entry into the popup form, and
// hide the popup from users who don't have one.
// new GPP_Modify_Popup_By_GF_Entry( array(
// 	'popup_feed_id' => 8,
// 	'form_id'       => 25,
// 	'if_entry'      => array(
// 		'iframe_query_args' => array(
// 			'trial_entry_id'   => '{entry:id}',
// 			'email'            => '{entry:2}',
// 			'submission_count' => '{count}',
// 			'offer'            => 'trial_upgrade',
// 		),
// 	),
// 	'if_no_entry'   => array(
// 		'is_active' => false,
// 	),
// ) );

// Example: only act on entries matching specific values, using field_filters.
// Filter keys can be field IDs, entry properties, or meta.
// new GPP_Modify_Popup_By_GF_Entry( array(
// 	'popup_feed_id' => 8,
// 	'form_id'       => 25,
// 	'field_filters' => array(
// 		array(
// 			'key'   => '3',
// 			'value' => 'Premium Guide',
// 		),
// 		array(
// 			'key'   => 'payment_status',
// 			'value' => 'Paid',
// 		),
// 	),
// 	'if_entry'      => array(
// 		'is_active' => false,
// 	),
// ) );
