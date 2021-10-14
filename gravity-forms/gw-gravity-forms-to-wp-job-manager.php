<?php
/**
 * Gravity Wiz // Gravity Forms // Populate GF Data into WP Job Manager Custom Fields
 * https://gravitywiz.com/
 *
 * Provides support for mapping a GF multiselect field to a WPJM multiselect field.
 *
 * Plugin Name: Gravity Froms to WP Job Manager
 * Plugin URI: https://gravitywiz.com/
 * Description: Provides support for mapping a GF multiselect field to a WPJM multiselect field.
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: https://gravitywiz.com/
 */
class GW_GF_To_WP_Job_Manager {

	var $post_custom_fields;

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'custom_fields' => array(),
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_action( 'gform_post_data', array( $this, 'stash_post_custom_fields_data' ) );
		add_action( 'gform_after_create_post', array( $this, 'populate_custom_fields' ) );

	}

	public function stash_post_custom_fields_data( $post_data ) {
		$this->post_custom_fields = $post_data['post_custom_fields'];
	}

	public function populate_custom_fields( $post_id ) {

		foreach ( $this->_args['custom_fields'] as $custom_field ) {
			delete_post_meta( $post_id, $custom_field );
			$value = json_decode( rgar( $this->post_custom_fields, $custom_field ) );
			update_post_meta( $post_id, $custom_field, $value );
		}

	}

}

# Configuration

new GW_GF_To_WP_Job_Manager( array(
	'custom_fields' => array( '_job_core_skills' ),
) );
