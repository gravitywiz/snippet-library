<?php
/**
 * Gravity Wiz // GP Populate Anything // Fuzzy Match Property
 *
 * Adds a fuzzy match property available to all object types in Populate Anything that can be used to perform fuzzy
 * matching on the returned objects. Useful if the particular object type does not have built-in searching capabilities.
 *
 * Plugin Name:  GP Populate Anything Fuzzy Match Property
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-populat-anything
 * Description:  Adds a fuzzy match property available to all object types in Populate Anything that can be used to perform fuzzy matching on the returned objects.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GPPA Fuzzy Match Property
 *
 * Provides fuzzy string matching capabilities for GPPA object types.
 * Uses a combination of Levenshtein distance, subsequence matching, and token-based similarity.
 */
class GPPA_Fuzzy_Match_Property {

	/**
	 * Minimum similarity threshold for matches (0-100)
	 *
	 * @var float
	 */
	public $similarity_threshold = 50;

	/**
	 * Fuzzy search algorithm weights
	 *
	 * @var array
	 */
	private $default_weights = array(
		'lev'    => 0.4, // Levenshtein similarity weight
		'subseq' => 0.4, // Subsequence matching weight
		'token'  => 0.2, // Token set similarity weight
	);

	/**
	 * Singleton instance
	 *
	 * @var GPPA_Fuzzy_Match_Property
	 */
	private static $instance = null;

	/**
	 * Get singleton instance
	 *
	 * @return GPPA_Fuzzy_Match_Property
	 */
	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		// Add the fuzzy match property to all object types
		add_filter( 'gppa_object_type_properties', array( $this, 'add_fuzzy_match_property' ), 10, 2 );

		// Filter query results to apply fuzzy matching
		add_filter( 'gppa_object_type_query_results', array( $this, 'apply_fuzzy_matching' ), 10, 3 );

	}

	/**
	 * Add the fuzzy match property to all object types
	 *
	 * @param array $props The properties available for filtering/ordering for the current object type.
	 * @param string $object_type The current object type.
	 *
	 * @return array
	 */
	public function add_fuzzy_match_property( $props, $object_type ) {

		$props['fuzzy_match'] = array(
			'label'              => esc_html__( 'Fuzzy Match', 'gp-populate-anything' ),
			'value'              => 'fuzzy_match',
			'operators'          => array( 'contains', 'does_not_contain' ),
			'supports_filters'   => true,
			'supports_templates' => false,
			'orderby'            => false,
			'callable'           => '__return_empty_array', // No predefined values
		);

		return $props;
	}

	/**
	 * Apply fuzzy matching to query results if fuzzy match filters are present
	 *
	 * @param array $objects The objects returned from the object type's query method.
	 * @param GPPA_Object_Type $object_type_instance The current GPPA object type instance
	 * @param array $args Query arguments
	 *
	 * @return array
	 */
	public function apply_fuzzy_matching( $objects, $object_type_instance, $args ) {
		if ( empty( $objects ) || ! is_array( $objects ) ) {
			return $objects;
		}

		// Check if any filter uses fuzzy_match property
		$fuzzy_filters = $this->extract_fuzzy_filters( rgar( $args, 'filter_groups', array() ) );

		if ( empty( $fuzzy_filters ) ) {
			return $objects; // No fuzzy matching needed
		}

		// Apply fuzzy matching to each filter
		foreach ( $fuzzy_filters as $filter ) {
			if ( empty( $filter['value'] ) ) {
				continue;
			}

			// Expand filter value using GPPA's existing replace_gf_field_value method
			$expanded_search_term = $object_type_instance->replace_gf_field_value(
				$filter['value'],
				rgar( $args, 'field_values', array() ),
				rgar( $args, 'primary_property_value' ),
				$filter,
				rgar( $args, 'ordering', array() ),
				rgar( $args, 'field' )
			);

			$objects = $this->filter_objects_by_fuzzy_match(
				$objects,
				$expanded_search_term,
				rgar( $args, 'templates', array() ),
				$object_type_instance,
				rgar( $args, 'field' ),
				rgar( $filter, 'operator', 'contains' )
			);
		}

		return $objects;
	}

	/**
	 * Extract fuzzy match filters from filter groups
	 *
	 * @param array $filter_groups
	 *
	 * @return array
	 */
	private function extract_fuzzy_filters( $filter_groups ) {
		$fuzzy_filters = array();

		if ( ! is_array( $filter_groups ) ) {
			return $fuzzy_filters;
		}

		foreach ( $filter_groups as $filter_group ) {
			if ( ! is_array( $filter_group ) ) {
				continue;
			}

			foreach ( $filter_group as $filter ) {
				if ( rgar( $filter, 'property' ) === 'fuzzy_match' ) {
					$fuzzy_filters[] = $filter;
				}
			}
		}

		return $fuzzy_filters;
	}

	/**
	 * Filter objects using fuzzy matching
	 *
	 * @param array $objects
	 * @param string $search_term
	 * @param array $templates
	 * @param GPPA_Object_Type $object_type_instance
	 * @param GF_Field $field
	 * @param string $operator
	 *
	 * @return array
	 */
	private function filter_objects_by_fuzzy_match( $objects, $search_term, $templates, $object_type_instance, $field, $operator = 'contains' ) {
		if ( empty( $search_term ) || empty( $objects ) || ! is_array( $objects ) ) {
			return $objects;
		}

		// Prepare objects for fuzzy search by extracting searchable content
		$searchable_objects = array();
		foreach ( $objects as $object ) {
			$searchable_content = $this->extract_searchable_content( $object, $templates, $object_type_instance, $field );
			if ( ! empty( $searchable_content ) ) {
				$searchable_objects[] = array(
					'original_object' => $object,
					'searchable_text' => $searchable_content,
				);
			}
		}

		if ( empty( $searchable_objects ) ) {
			return array();
		}

		// Use the fuzzy search method
		$fuzzy_results = $this->fuzzy_search(
			$searchable_objects,
			$search_term,
			array(
				'threshold'      => $this->similarity_threshold / 100, // Convert percentage to 0-1 scale
				'keys'           => array( 'searchable_text' ),
				'case_sensitive' => false,
				'strip_accents'  => true,
			)
		);

		// Extract the original objects from the fuzzy search results
		$matched_objects = array();
		foreach ( $fuzzy_results as $result ) {
			$matched_objects[] = $result['item']['original_object'];
		}

		// Handle 'does_not_contain' operator by returning objects that didn't match
		if ( $operator === 'does_not_contain' ) {
			$matched_indices = array();
			foreach ( $fuzzy_results as $result ) {
				$matched_indices[] = $result['refIndex'];
			}

			$filtered_objects = array();
			foreach ( $objects as $index => $object ) {
				if ( ! in_array( $index, $matched_indices, true ) ) {
					$filtered_objects[] = $object;
				}
			}

			return $filtered_objects;
		}

		// Default 'contains' behavior
		return $matched_objects;
	}

	/**
	 * Extract searchable content from an object using templates
	 *
	 * @param mixed $object
	 * @param array $templates
	 * @param GPPA_Object_Type $object_type_instance
	 * @param GF_Field $field
	 *
	 * @return string
	 */
	private function extract_searchable_content( $object, $templates, $object_type_instance, $field ) {
		// Try templates in priority order
		foreach ( array( 'label', 'value' ) as $template_type ) {
			if ( ! empty( $templates[ $template_type ] ) ) {
				$content = $this->process_template_content( $object, $templates[ $template_type ], $object_type_instance, $field );
				if ( ! empty( $content ) ) {
					return trim( wp_strip_all_tags( $content ) );
				}
			}
		}

		// Final fallback: concatenate all string properties
		return trim( wp_strip_all_tags( $this->extract_all_string_properties( $object ) ) );
	}

	/**
	 * Process template content using GPPA's template system
	 *
	 * @param mixed $object
	 * @param string $template
	 * @param GPPA_Object_Type $object_type_instance
	 * @param GF_Field $field
	 *
	 * @return string
	 */
	private function process_template_content( $object, $template, $object_type_instance, $field ) {
		// Use GPPA's process_template method for proper template processing
		$processed_content = gp_populate_anything()->process_template(
			$field,
			'label', // template name
			$object,
			'choices', // populate type
			array( $object ), // objects array
			$template // actual template
		);

		return is_string( $processed_content ) ? $processed_content : '';
	}

	/**
	 * Extract all string properties from an object as fallback
	 *
	 * @param mixed $object
	 *
	 * @return string
	 */
	private function extract_all_string_properties( $object ) {
		$content_parts = array();

		if ( is_array( $object ) ) {
			foreach ( $object as $value ) {
				if ( is_string( $value ) && ! empty( trim( $value ) ) ) {
					$content_parts[] = $value;
				}
			}
		} elseif ( is_object( $object ) ) {
			foreach ( get_object_vars( $object ) as $value ) {
				if ( is_string( $value ) && ! empty( trim( $value ) ) ) {
					$content_parts[] = $value;
				}
			}
		} elseif ( is_string( $object ) ) {
			$content_parts[] = $object;
		}

		return implode( ' ', $content_parts );
	}

	/**
	 * Fuzzy search implementation with multiple scoring algorithms
	 *
	 * @param array $collection Array of items to search through
	 * @param string $query Search query
	 * @param array $options Search options
	 *
	 * @return array Sorted array of matching results
	 */
	private function fuzzy_search( $collection, $query, $options = array() ) {
		$threshold      = isset( $options['threshold'] ) ? (float) $options['threshold'] : 0.3;
		$limit          = isset( $options['limit'] ) ? (int) $options['limit'] : null;
		$case_sensitive = isset( $options['case_sensitive'] ) ? (bool) $options['case_sensitive'] : false;
		$strip_accents  = array_key_exists( 'strip_accents', $options ) ? (bool) $options['strip_accents'] : true;

		// Scoring weights (normalized to sum = 1)
		$weights = $this->default_weights;
		if ( isset( $options['weights'] ) && is_array( $options['weights'] ) ) {
			$weights           = array_merge( $weights, $options['weights'] );
			$sum               = max( 1e-9, (float) $weights['lev'] + (float) $weights['subseq'] + (float) $weights['token'] );
			$weights['lev']    = (float) $weights['lev'] / $sum;
			$weights['subseq'] = (float) $weights['subseq'] / $sum;
			$weights['token']  = (float) $weights['token'] / $sum;
		}

		$keys             = isset( $options['keys'] ) ? (array) $options['keys'] : array();
		$normalized_query = $this->normalize_text( $query, $case_sensitive, $strip_accents );

		$results = array();

		foreach ( $collection as $idx => $item ) {
			$searchables = $this->get_searchable_fields( $item, $keys );
			$best_match  = null;

			foreach ( $searchables as $searchable ) {
				$text = $this->normalize_text( $searchable['text'], $case_sensitive, $strip_accents );

				if ( $text === '' && $normalized_query !== '' ) {
					continue;
				}

				$score  = $this->calculate_fuzzy_score( $normalized_query, $text, $weights );
				$score *= max( 0.0, $searchable['weight'] );

				// Boost exact and substring matches
				if ( $text === $normalized_query ) {
					$score = min( 1.0, $score + 0.2 );
				} elseif ( $normalized_query !== '' && mb_strpos( $text, $normalized_query, 0, 'UTF-8' ) !== false ) {
					$score = min( 1.0, $score + 0.05 );
				}

				if ( $best_match === null || $score > $best_match['score'] ) {
					$best_match = array(
						'score'   => $score,
						'matched' => $searchable['text'],
						'key'     => $searchable['key'],
					);
				}
			}

			if ( $best_match !== null && $best_match['score'] >= $threshold ) {
				$results[] = array(
					'item'       => $item,
					'refIndex'   => $idx,
					'score'      => round( $best_match['score'], 6 ),
					'matched'    => $best_match['matched'],
					'matchedKey' => $best_match['key'],
				);
			}
		}

		// Sort by score (descending), then by original index
		usort( $results, function ( $a, $b ) {
			if ( $a['score'] === $b['score'] ) {
				return $a['refIndex'] <=> $b['refIndex'];
			}
			return ( $a['score'] > $b['score'] ) ? -1 : 1;
		} );

		if ( $limit !== null && $limit >= 0 ) {
			$results = array_slice( $results, 0, $limit );
		}

		return $results;
	}

	/**
	 * Normalize text for comparison
	 */
	private function normalize_text( $text, $case_sensitive = false, $strip_accents = true ) {
		if ( $strip_accents && function_exists( 'iconv' ) ) {
			$normalized = iconv( 'UTF-8', 'ASCII//TRANSLIT//IGNORE', $text );
			if ( $normalized !== false ) {
				$text = $normalized;
			}
		}

		if ( ! $case_sensitive ) {
			$text = mb_strtolower( $text, 'UTF-8' );
		}

		// Collapse whitespace
		$text = preg_replace( '/\s+/u', ' ', $text );
		return trim( $text );
	}

	/**
	 * Extract searchable fields from an item
	 */
	private function get_searchable_fields( $item, $keys ) {
		if ( empty( $keys ) ) {
			return array(
				array(
					'text'   => is_scalar( $item ) ? (string) $item : '',
					'key'    => null,
					'weight' => 1.0,
				),
			);
		}

		$searchables = array();
		foreach ( $keys as $key ) {
			$path   = is_array( $key ) ? (string) rgar( $key, 'path', '' ) : (string) $key;
			$weight = is_array( $key ) ? (float) rgar( $key, 'weight', 1.0 ) : 1.0;

			if ( $path === '' ) {
				continue;
			}

			$value = $this->extract_by_path( $item, $path );
			if ( $value !== null && $value !== '' ) {
				$searchables[] = array(
					'text'   => $value,
					'key'    => $path,
					'weight' => max( 0.0, $weight ),
				);
			}
		}

		if ( empty( $searchables ) ) {
			$searchables[] = array(
				'text'   => is_scalar( $item ) ? (string) $item : '',
				'key'    => null,
				'weight' => 1.0,
			);
		}

		return $searchables;
	}

	/**
	 * Extract value from item using dot notation path
	 */
	private function extract_by_path( $item, $path ) {
		$segments = explode( '.', $path );
		$current  = $item;

		foreach ( $segments as $segment ) {
			if ( is_array( $current ) && array_key_exists( $segment, $current ) ) {
				$current = $current[ $segment ];
			} elseif ( is_object( $current ) && isset( $current->{$segment} ) ) {
				$current = $current->{$segment};
			} else {
				return null;
			}
		}

		return is_scalar( $current ) ? (string) $current : null;
	}

	/**
	 * Calculate fuzzy score using multiple algorithms
	 */
	private function calculate_fuzzy_score( $query, $text, $weights ) {
		return $weights['subseq'] * $this->subsequence_score( $query, $text ) +
			$weights['lev'] * $this->levenshtein_similarity( $query, $text ) +
			$weights['token'] * $this->token_set_similarity( $query, $text );
	}

	/**
	 * Calculate subsequence matching score
	 */
	private function subsequence_score( $needle, $haystack ) {
		if ( $needle === '' ) {
			return 1.0;
		}

		$needle_len   = mb_strlen( $needle, 'UTF-8' );
		$haystack_len = mb_strlen( $haystack, 'UTF-8' );
		$matched      = 0;

		for ( $i = 0, $j = 0; $i < $haystack_len && $j < $needle_len; $i++ ) {
			if ( mb_substr( $haystack, $i, 1, 'UTF-8' ) === mb_substr( $needle, $j, 1, 'UTF-8' ) ) {
				$matched++;
				$j++;
			}
		}

		$base_score = $matched / max( 1, $needle_len );

		// Boost for substring matches
		if ( $needle !== '' && mb_strpos( $haystack, $needle, 0, 'UTF-8' ) !== false ) {
			$base_score = min( 1.0, $base_score + 0.15 );
			// Extra boost for prefix matches
			if ( mb_strpos( $haystack, $needle, 0, 'UTF-8' ) === 0 ) {
				$base_score = min( 1.0, $base_score + 0.10 );
			}
		}

		return $base_score;
	}

	/**
	 * Calculate Levenshtein similarity
	 */
	private function levenshtein_similarity( $a, $b ) {
		if ( $a === '' && $b === '' ) {
			return 1.0;
		}

		$max_len = max( mb_strlen( $a, 'UTF-8' ), mb_strlen( $b, 'UTF-8' ) );
		if ( $max_len === 0 ) {
			return 1.0;
		}

		$distance = levenshtein( $a, $b );
		return max( 0.0, 1.0 - ( $distance / $max_len ) );
	}

	/**
	 * Calculate token set similarity (Jaccard index)
	 */
	private function token_set_similarity( $a, $b ) {
		$tokens_a = array_filter( preg_split( '/\s+/u', trim( $a ) ) );
		$tokens_b = array_filter( preg_split( '/\s+/u', trim( $b ) ) );

		if ( empty( $tokens_a ) && empty( $tokens_b ) ) {
			return 1.0;
		}

		$set_a        = array_unique( $tokens_a );
		$set_b        = array_unique( $tokens_b );
		$intersection = array_intersect( $set_a, $set_b );
		$union        = array_unique( array_merge( $set_a, $set_b ) );

		return count( $union ) > 0 ? count( $intersection ) / count( $union ) : 0.0;
	}

}

# Initialize the property

GPPA_Fuzzy_Match_Property::get_instance();
