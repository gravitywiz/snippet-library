<?php
/**
 * Gravity Perks // Populate Anything // JSON API with Query Params
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * This snippet adds a new Object Type
 *
 * THE SOFTWARE/SNIPPET IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT
 * LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
class GPPA_Object_Type_Sample_JSON_API extends GPPA_Object_Type {

	public function __construct( $id ) {
		parent::__construct( $id );

		add_action( 'gppa_pre_object_type_query_sample_json_api', array( $this, 'add_filter_hooks' ) );
	}

	public function add_filter_hooks() {
		add_filter( 'gppa_object_type_sample_json_api_filter', array( $this, 'process_filter_default' ), 10, 4 );
	}

	public function get_object_id( $object, $primary_property_value = null ) {
		return rgar( $object, 'id' );
	}

	public function get_label() {
		return esc_html__( 'Sample JSON API', 'gp-populate-anything' );
	}

	public function get_groups() {
		return array(
			'query_properties'  => array(
				'label' => esc_html__( 'Query Properties', 'gp-populate-anything' ),
			),
			'result_properties' => array(
				'label' => esc_html__( 'Result Properties', 'gp-populate-anything' ),
			),
		);
	}

	public function get_properties( $_ = null ) {

		$properties = array();

		/* Examples */
		$json_props = array(
			'name',
			'email',
			'company',
		);

		foreach ( $json_props as $json_prop ) {
			$properties[ $json_prop ] = array(
				'group'     => 'result_properties',
				'label'     => $json_prop,
				'value'     => $json_prop,
				'orderby'   => false,
				'callable'  => '__return_empty_array',
				'operators' => null,
			);
		}

		/* Query Properties */
		$properties['query_sales_rep'] = array(
			'group'     => 'query_properties',
			'label'     => 'Sales Rep',
			'value'     => 'sales_rep',
			'orderby'   => false,
			'callable'  => '__return_empty_array',
			'operators' => array(
				'is',
			),
		);

		return $properties;

	}

	public function fetch( $sales_rep ) {

		if ( ! $sales_rep ) {
			return array();
		}

		$cache_key = 'sample_json_json_' . $sales_rep;

		if ( $data = get_transient( $cache_key ) ) {
			return $data;
		}

		$url = add_query_arg( array(
			'salesRep' => $sales_rep,
		), 'https://samplejson.com' );

		$results = wp_remote_get( $url );

		$api_response = json_decode( wp_remote_retrieve_body( $results ), true );
		set_transient( $cache_key, $api_response, DAY_IN_SECONDS );

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

		$search[ $property_id ] = $filter_value;

		return $search;
	}

	public function query( $args ) {
		$search_params = $this->process_filter_groups( $args );
		$query_results = $this->fetch( rgar( $search_params, 'sales_rep' ) );

		return $query_results;

	}

	public function get_object_prop_value( $object, $prop ) {
		if ( ! isset ( $object[ $prop ] ) ) {
			return null;
		}

		return $object[ $prop ];
	}

}

add_action( 'init', function () {
	gp_populate_anything()->register_object_type( 'sample_json_api', 'GPPA_Object_Type_Sample_JSON_API' );
} );
