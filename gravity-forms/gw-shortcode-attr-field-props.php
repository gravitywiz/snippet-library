<?php
/**
 * Gravity Wiz // Gravity Forms // Custom Field Properties via [gravityforms] Shortcode
 * https://gravitywiz.com/
 *
 * Provide a custom "field_props" attribute for the /[gravityform/] shortcode. This allows you to modify field
 * properties (e.g. label, cssClass, isRequired, etc).
 *
 * Set Field ID 1 as not required.
 * [[gravityform id="1" field_props="1:isRequired:0"]]
 *
 * Set "my-special-class" as the CSS class for field ID 2.
 * [gravityform id="1" field_props="2:cssClass:my-special-class"]
 *
 * Set the discount amount to 10% and discount type to flat for GP eCommerce Fields Discount field ID 3.
 * [gravityform id="1" field_props="3:taxAmount:10,3:taxAmountType:flat"]
 *
 * Plugin Name:  Gravity Forms - Shortcode Field Properties
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Specify custom field properties with the "field_props" attribute for the [gravityforms] shortcode.
 * Author:       Gravity Wiz
 * Version:      0.9.1
 * Author URI:   https://gravitywiz.com/
 */
class GW_Shortcode_Attr_Field_Props {

	private $_field_props;

	public function __construct( $args = array() ) {

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'shortcode_atts_gravityforms', array( $this, 'stash_shortcode_field_props_attr' ), 10, 4 );
		add_filter( 'gform_pre_render', array( $this, 'set_field_props' ) );

	}

	public function stash_shortcode_field_props_attr( $out, $pairs, $atts ) {

		// We don't really need to return the field_props attribute but let's do it just to be thorough.
		$defaults = array_merge( $pairs, array(
			'field_props' => '',
		) );

		$out = shortcode_atts( $defaults, $atts );

		// Now let's parse and stash the field_props attribute for use in the gform_pre_render hook (after which it will be cleared).
		$this->_field_props = $this->parse_field_props( $out['field_props'] );

		return $out;
	}

	public function parse_field_props( $string ) {

		$props = array();

		if ( empty( $string ) ) {
			return $props;
		}

		$strings = explode( ',', $string );

		foreach ( $strings as $_string ) {
			list( $field_id, $prop, $value ) = explode( ':', $_string );
			if ( ! isset( $props[ $field_id ] ) ) {
				$props[ $field_id ] = array();
			}
			$props[ $field_id ][ $prop ] = $value;
		}

		return $props;
	}

	public function set_field_props( $form ) {

		if ( empty( $this->_field_props ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {

			if ( ! isset( $this->_field_props[ $field->id ] ) ) {
				continue;
			}

			foreach ( $this->_field_props[ $field->id ] as $prop => $value ) {
				$field->$prop = $value;
			}
		}

		return $form;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

}

# Configuration

new GW_Shortcode_Attr_Field_Props();
