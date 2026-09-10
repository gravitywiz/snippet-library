<?php
/**
 * Gravity Connect // API Alchemist // Auto-map Nested Properties for GPPA Endpoints
 * https://gravitywiz.com/documentation/gravity-connect-api-alchemist/
 *
 * Experimental Snippet 🧪
 *
 * A GPPA endpoint's "Auto-detect all properties from sample data" only maps top-level keys.
 * This snippet also maps nested objects as dot-notation properties (e.g. `address.street`),
 * making them available in GPPA's property dropdowns.
 */
class GCAPI_GPPA_Nested_Auto_Properties {

	private $config;
	private static $is_sampling = false;

	public function __construct( $args = array() ) {
		$this->config = wp_parse_args( $args, array(
			'connection_profile_ids' => array(),
			'max_depth'              => 3,
			'separator'              => '.',
			'sample_size'            => 10,
		) );

		add_filter( 'gppa_object_type_query_results', array( $this, 'flatten_query_results' ), 10, 3 );
		add_filter( 'gppa_object_type_properties_api_alchemist', array( $this, 'add_nested_properties' ) );
	}

	public function flatten_query_results( $results, $object_type, $args ) {
		if ( self::$is_sampling ) {
			return $results;
		}

		if ( ! $object_type instanceof \GPPA_Object_Type || $object_type->id !== 'api_alchemist' || ! is_array( $results ) ) {
			return $results;
		}

		if ( ! $this->profile_in_scope( rgar( $args, 'primary_property_value' ) ) ) {
			return $results;
		}

		foreach ( $results as &$item ) {
			if ( is_array( $item ) ) {
				$item = array_merge( $item, $this->flatten( $item ) );
			}
		}

		return $results;
	}

	public function add_nested_properties( $properties ) {
		if ( self::$is_sampling || ! is_admin() ) {
			return $properties;
		}

		$primary_property_value = sanitize_text_field( wp_unslash( rgar( $_REQUEST, 'primary-property-value' ) ) );

		if ( ! $primary_property_value || ! function_exists( 'gp_populate_anything' ) ) {
			return $properties;
		}

		if ( ! $this->profile_in_scope( $primary_property_value ) ) {
			return $properties;
		}

		$object_type = gp_populate_anything()->get_object_type( 'api_alchemist' );
		if ( ! $object_type ) {
			return $properties;
		}

		self::$is_sampling = true;

		try {
			$results = $object_type->query( array(
				'primary_property_value' => $primary_property_value,
			) );
		} catch ( \Exception $e ) {
			$results = array();
		} finally {
			self::$is_sampling = false;
		}

		if ( ! is_array( $results ) ) {
			return $properties;
		}

		$nested = array();
		foreach ( array_slice( $results, 0, $this->config['sample_size'] ) as $item ) {
			if ( is_array( $item ) ) {
				$nested = array_merge( $nested, $this->flatten( $item ) );
			}
		}

		foreach ( array_keys( $nested ) as $name ) {
			if ( isset( $properties[ $name ] ) ) {
				continue;
			}

			$properties[ $name ] = array(
				'label'              => $name,
				'value'              => $name,
				'orderby'            => false,
				'callable'           => '__return_empty_array',
				'supports_filters'   => false,
				'supports_templates' => true,
			);
		}

		return $properties;
	}

	private function profile_in_scope( $primary_property_value ) {
		$ids = array_map( 'intval', (array) $this->config['connection_profile_ids'] );

		if ( empty( $ids ) ) {
			return true;
		}

		$profile_id = (int) explode( '|', (string) $primary_property_value )[0];

		return in_array( $profile_id, $ids, true );
	}

	private function flatten( $item, $prefix = '', $depth = 0, &$flat = array() ) {
		if ( $depth >= $this->config['max_depth'] ) {
			return $flat;
		}

		foreach ( $item as $key => $value ) {
			$name = $prefix . $key;

			if ( is_array( $value ) && ! wp_is_numeric_array( $value ) && ! empty( $value ) ) {
				$this->flatten( $value, $name . $this->config['separator'], $depth + 1, $flat );
			} elseif ( $prefix ) {
				$flat[ $name ] = $value;
			}
		}

		return $flat;
	}
}

# Configuration

new GCAPI_GPPA_Nested_Auto_Properties( array(
	'connection_profile_ids' => array( 123 ), // Optionally restrict to specific connection profile's endpoints.
	'max_depth'              => 3, // How many levels of nested objects to flatten.
	'separator'              => '.', // Separator used in flattened property names, e.g. "address.street".
	'sample_size'            => 10, // How many results to sample when detecting nested properties in the form editor.
) );
