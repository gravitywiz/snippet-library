<?php
/**
 * Gravity Perks // Advanced Select // Search Words in Any Order
 * https://gravitywiz.com/documentation/gravity-forms-advanced-select/
 *
 * When Populate Anything filters an Advanced Select field's choices by the "Advanced Select Search Value" with the
 * "contains" operator, the search is matched as one exact phrase: "blue shirt" finds "Blue Shirt" but not
 * "Blue Cotton Shirt" or "Shirt (Blue)".
 *
 * This snippet splits the search into words and requires every word to match, in any order. Each "contains" filter
 * that uses the search value is replaced with one "contains" filter per word in the same filter group. The matching
 * still happens in the database (or Google Sheets) query, so it works with large lists and with lazy loading.
 *
 * Each filter group is still matched on its own. If one group searches the title and another searches the SKU,
 * every word must be found in the title, or every word in the SKU.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the instructions here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 * 2. By default, every Advanced Select field filtered by "Advanced Select Search Value" with "contains" matches words
 *    in any order. To limit it to one form or field, set the form_id and field_id at the bottom of this snippet.
 */
class GPADVS_Search_Words_In_Any_Order {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( ! is_callable( 'gp_advanced_select' ) || ! is_callable( 'gp_populate_anything' ) ) {
			return;
		}

		add_filter( 'gppa_field_objects_query_args', array( $this, 'split_search_value_filters' ), 10, 2 );
		add_filter( 'gppa_special_value', array( $this, 'replace_search_word' ), 10, 3 );

	}

	/**
	 * Replaces each "contains" filter that uses the search value with one filter per word, in the same filter group.
	 */
	public function split_search_value_filters( $args, $field ) {

		if ( ! $this->is_applicable_field( $field ) ) {
			return $args;
		}

		$words = $this->get_search_words();

		if ( count( $words ) < 2 || ! is_array( $args['filter_groups'] ) ) {
			return $args;
		}

		foreach ( $args['filter_groups'] as &$filter_group ) {
			$split_group = array();

			foreach ( $filter_group as $filter ) {
				if ( rgar( $filter, 'value' ) !== 'special_value:advanced_select_search_value' || rgar( $filter, 'operator' ) !== 'contains' ) {
					$split_group[] = $filter;
					continue;
				}

				foreach ( array_keys( $words ) as $index ) {
					$filter['value'] = 'special_value:advanced_select_search_word:' . $index;
					$split_group[]   = $filter;
				}
			}

			$filter_group = $split_group;
		}
		unset( $filter_group );

		return $args;
	}

	/**
	 * Resolves the special value each split filter uses to its word of the search.
	 */
	public function replace_search_word( $value, $special_value, $special_value_parts ) {

		if ( $special_value_parts[0] !== 'advanced_select_search_word' ) {
			return $value;
		}

		return rgar( $this->get_search_words(), (int) rgar( $special_value_parts, 1 ) );
	}

	public function get_search_words() {

		if ( ! doing_action( 'wp_ajax_gp_advanced_select_get_gppa_results' ) && ! doing_action( 'wp_ajax_nopriv_gp_advanced_select_get_gppa_results' ) ) {
			return array();
		}

		return preg_split( '/\s+/u', trim( (string) rgpost( 'term' ) ), -1, PREG_SPLIT_NO_EMPTY );
	}

	public function is_applicable_field( $field ) {

		if ( ! empty( $this->_args['form_id'] ) && (int) rgar( $field, 'formId' ) !== (int) $this->_args['form_id'] ) {
			return false;
		}

		if ( ! empty( $this->_args['field_id'] ) && (int) rgar( $field, 'id' ) !== (int) $this->_args['field_id'] ) {
			return false;
		}

		return true;
	}

}

# Configuration

new GPADVS_Search_Words_In_Any_Order( array(
	// 'form_id'  => 123,
	// 'field_id' => 4,
) );
