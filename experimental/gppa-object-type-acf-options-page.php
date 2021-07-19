<?php
/**
 * Gravity Perks // Populate Anything // Advanced Custom Fields Options Page Integration
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * This snippet adds a new Object Type that enables pulling values directly from Options Pages added via
 * Advanced Custom Fields. For more on Options Pages, see https://www.advancedcustomfields.com/resources/options-page/
 *
 * Installation: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 * 
 * Known Limitations:
 *  * Filtering is not supported with this Object Type
 */
class GPPA_Object_Type_ACF_Options_Page extends GPPA_Object_Type {
	public function get_label() {
		return esc_html__( 'ACF Options Page', 'gp-populate-anything' );
	}

	public function get_primary_property() {
		return array(
			'id'       => 'options-page',
			'label'    => esc_html__( 'Options Page', 'gp-populate-anything' ),
			'callable' => array( $this, 'get_options_pages' ),
		);
	}

	public function get_options_pages() {
		return wp_list_pluck( acf_get_options_pages(), 'page_title', 'menu_slug' );
	}

	public function get_properties( $options_page = null ) {
	    $field_groups = acf_get_field_groups(array(
		    'options_page' => $options_page
	    ));

	    $properties = array();

	    foreach ( $field_groups as $field_group ) {
		    $fields = acf_get_fields( $field_group );

		    foreach ( $fields as $field ) {
			    $properties[ $field['name'] ] = array(
				    'value'    => $field['name'],
				    'label'    => $field['label'],
				    'callable' => '__return_false',
				    'orderby'  => false,
			    );
            }
	    }

		return $properties;
	}

	public function get_object_id( $object, $primary_property_value = null ) {
		return null;
	}

	public function query( $args ) {
		/**
		 * @var $primary_property_value string
		 * @var $field_values array
		 * @var $filter_groups array
		 * @var $ordering array
		 * @var $field GF_Field
		 */
		extract( $args );

		$all_fields = (object) get_fields( 'option' );

		return array( $all_fields );
	}
}

add_action('init', function() {
	gp_populate_anything()->register_object_type( 'acf-options-page', 'GPPA_Object_Type_ACF_Options_Page' );
});
