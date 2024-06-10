<?php
/**
 * Gravity Perks // Address Autocomplete // Generate Static Map for the entered Address.
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Instruction Video: https://www.loom.com/share/3f849ad53c0745e082caf55adaedbe9d
 *
 * Generate Static Map for the Entered Address.
 */
class GPAA_Generate_Static_Map {

	/** @var int */
	private $_form_id;

	/** @var int */
	private $_field_id;

	/** @var array */
	private $_args;

	public function __construct( $form_id, $field_id, $args = array() ) {

		$this->_form_id  = $form_id;
		$this->_field_id = $field_id;

		// Default values for zoom, width, and height.
		$this->_args = wp_parse_args( $args, array(
			'zoom'    => 13,
			'width'   => 640,
			'height'  => 480,
			'api_key' => 'YOUR_API_KEY_HERE'
		) );

		// Register Map Meta
		add_filter( 'gform_entry_meta', array( $this, 'register_map_meta' ), 10, 2 );
		// Store Map Entra
		add_filter( 'gform_entry_post_save', array( $this, 'store_map_as_meta' ), 6, 2 );
		// Adding the Map "Box" on Entry Detail Meta Boxes View
		add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'add_custom_map_box' ), 10, 3 );

	}

	public function register_map_meta( $entry_meta, $form_id ) {
		$entry_meta[ "gpaa_map_{$this->_field_id}" ] = array(
			'label'                      => 'Static Map',
			'is_numeric'                 => false,
			'update_entry_meta_callback' => null,
			'is_default_column'          => false,
			'filter'                     => false,
		);

		return $entry_meta;
	}

	public function store_map_as_meta( $entry, $form ) {
		if ( $form['id'] != $this->_form_id ) {
			return;
		}

		$latitude  = $entry['gpaa_lat_' . $this->_field_id];
		$longitude = $entry['gpaa_lng_' . $this->_field_id];
		$url       = sprintf(
			'https://maps.googleapis.com/maps/api/staticmap?center=%s,%s&zoom=%d&size=%dx%d&maptype=roadmap&key=%s',
			$latitude,
			$longitude,
			$this->_args['zoom'],
			$this->_args['width'],
			$this->_args['height'],
			$this->_args['api_key'],
		);

		gform_update_meta( $entry['id'], 'gpaa_map_' . $this->_field_id, $url );	
		$entry[ 'gpaa_map_' . $this->_field_id ] = $url;	

		return $entry;
	}

	public function add_custom_map_box( $meta_boxes, $entry, $form ) {
		if ( $form['id'] != $this->_form_id ) {
			return $meta_boxes;
		}

		$meta_boxes['custom_map_box'] = array(
			'title'    => 'Custom Map Box',
			'callback' => array( $this, 'custom_map_box_content' ),
			'context'  => 'normal',
		);

		return $meta_boxes;
	}

	public function custom_map_box_content( $args ) {
		$entry = $args['entry'];

		// Retrieve the custom meta value using gform_get_meta
		$map_url = gform_get_meta( $entry['id'], "gpaa_map_{$this->_field_id}" );
		echo '<img src="' . esc_url( $map_url ) . '"/>';
	}
}

// Create a new instance of the GPAA_Generate_Static_Map class
new GPAA_Generate_Static_Map( 310, 3, array(
	'zoom'    => 13,
	'width'   => 640,
	'height'  => 480,
	'api_key' => 'Enter Google Maps API Key',
) );
