<?php
/**
 * Gravity Perks // Populate Anything // JSON API Object Type
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * This snippet adds a new Object Type that pulls from a JSON file containing airports and their IATA codes.
 * Use this snippet as a starting point for other HTTP-based APIs that you would like to populate fields from.
 *
 * Installation: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
class GPPA_Object_Type_JSON_API extends GPPA_Object_Type {

	public function __construct( $id ) {
		parent::__construct( $id );

		add_action( 'gppa_pre_object_type_query_' . $this->id, array( $this, 'add_filter_hooks' ) );
	}

	public function add_filter_hooks() {
		add_filter( 'gppa_object_type_' . $this->id . '_filter', array( $this, 'process_filter_default' ), 10, 4 );
	}

	public function get_object_id( $object, $primary_property_value = null ) {
		return $object['iata'];
	}

	public function get_label() {
		return esc_html__( 'JSON API', 'gp-populate-anything' );
	}

	public function get_groups() {
		return array(
			'columns' => array(
				'label' => esc_html__( 'Columns', 'gp-populate-anything' ),
			),
		);
	}

	public function get_properties( $_ = null ) {

		$properties = array();

		$json_props = array(
			'iata'      => 'IATA Code',
			'lon'       => 'Longitude',
			'iso'       => 'ISO Code',
			'status'    => 'Status',
			'name'      => 'Airport Name',
			'continent' => 'Continent (Abbr)',
			'type'      => 'Type',
			'lat'       => 'Latitude',
			'size'      => 'Size',
		);

		foreach ( $json_props as $json_prop => $label ) {
			$properties[ $json_prop ] = array(
				'group'     => 'columns',
				'label'     => $label,
				'value'     => $json_prop,
				'orderby'   => false,
				'callable'  => '__return_empty_array',
				'operators' => array(
					'is',
					'isnot',
					'contains',
				),
			);
		}

		return $properties;

	}

	public function fetch() {

		$cache_key = 'airports_json';

		if ( $data = get_transient( $cache_key ) ) {
			return $data;
		}

		$url     = esc_url_raw( 'https://raw.githubusercontent.com/jbrooksuk/JSON-Airports/master/airports.json' );
		$results = wp_remote_get( $url );

		$api_response = json_decode( wp_remote_retrieve_body( $results ), true );

		set_transient( $cache_key, $api_response );

		return $api_response;

	}

	public function process_filter_default( $search, $args ) {

		/**
		 * @var $filter_value
		 * @var $filter
		 * @var $filter_group
		 * @var $filter_group_index
		 * @var $primary_property_value
		 * @var $property
		 * @var $property_id
		 */
		extract( $args );

		$search[ $filter_group_index ][] = array(
			'property' => $property_id,
			'operator' => $filter['operator'],
			'value'    => $filter_value,
		);

		return $search;

	}

	public function perform_search( $var, $search ) {

		switch ( $search['operator'] ) {
			case 'is':
				return ( $var[ $search['property'] ] == $search['value'] );

			case 'isnot':
				return ( $var[ $search['property'] ] != $search['value'] );

			case 'contains':
				return ( stripos( $var[ $search['property'] ], $search['value'] ) !== false );

			default:
				throw new Error( 'Invalid operator provided.' );
		}

	}

	/**
	 * Each search group is an OR
	 *
	 * If everything matches in one group, we can immediately bail out as we have a positive match.
	 *
	 * @param $var
	 * @param $search_params
	 *
	 * @return bool
	 */
	public function search( $var, $search_params ) {
		foreach ( $search_params as $search_group ) {
			$matches_group = true;

			foreach ( $search_group as $search ) {
				$matches_group = $this->perform_search( $var, $search );

				if ( ! $matches_group ) {
					break;
				}
			}

			if ( $matches_group ) {
				return true;
			}
		}

		return false;
	}

	public function query( $args ) {

		$search_params = $this->process_filter_groups( $args );
		$results       = $this->fetch();

		if ( ! empty( $search_params ) ) {
			$results = array_filter( $results, function ( $var ) use ( $search_params ) {
				return $this->search( $var, $search_params );
			} );
		}

		$query_limit   = gp_populate_anything()->get_query_limit( $this, $args['field'] );
		$query_results = array_slice( $results, 0, $query_limit );

		return $query_results;

	}

	public function get_object_prop_value( $object, $prop ) {

		if ( ! isset( $object[ $prop ] ) ) {
			return null;
		}

		return $object[ $prop ];

	}

}

add_action('init', function() {
	gp_populate_anything()->register_object_type( 'json-api', 'GPPA_Object_Type_JSON_API' );
});
