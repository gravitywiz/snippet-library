<?php
/**
 * Gravity Wiz // Gravity Forms // Tag Editor
 *
 * Provides the ability to more easily modify the properties of specific tags in Gravity Forms markup. Currently supports the <form> tag.
 *
 * Plugin Name: Gravity Forms Tag Editor
 * Plugin URI:  https://gravitywiz.com/gravity-forms-tag-editor/
 * Description: Provides the ability to more easily modify the properties of specific tags in Gravity Forms markup. Currently supports the <form> tag.
 * Version:     1.1
 * Author:      Gravity Wiz
 * Author URI:  http://gravitywiz.com
 */
class GW_Tag_Editor {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, $this->get_default_args() );

		add_filter( 'gform_form_tag', array( $this, 'edit_form_tag' ), 10, 2 );

	}

	/**
	 * Modify form tag. Default properties are:
	 *
	 *     method:  post
	 *     enctype: multipart/form-data
	 *     id:      gform_{formId}            (i.e. 'gform_123')
	 *     action:  Current Page URL          (i.e. 'http://mysite.com/my-form-page/')
	 *     target:  gform_ajax_frame_{formId} (i.e. 'gform_ajax_frame_123')
	 *
	 * @param $tag string Default form tag.
	 * @param $form array Current form object.
	 *
	 * @return string
	 */
	public function edit_form_tag( $tag, $form ) {

		if ( $this->_args['tag'] != 'form' || ( $this->_args['form_id'] && $this->_args['form_id'] != $form['id'] ) ) {
			return $tag;
		}

		return sprintf( '<form %s>', $this->build_properties_string( $this->get_full_tag_properties( $tag, $form ) ) );
	}

	public function get_custom_properties() {
		$exclude_props = $this->get_default_args();
		return array_diff_key( $this->_args, array_flip( array_keys( $exclude_props ) ) );
	}

	public function get_tag_properties( $tag ) {

		preg_match_all( '/(\w+)=["\']([^\n\r ]*)[\'"]/', $tag, $matches, PREG_SET_ORDER );

		$tag_props = array();
		foreach ( $matches as $match ) {
			list( , $prop, $value ) = $match;
			$tag_props[ $prop ]     = $value;
		}

		return $tag_props;
	}

	/**
	 * Get properties from the default tag and custom properties defined when the class is instantiated.
	 *
	 * @param $tag string An HTML tag string.
	 *
	 * @return array Array of all default and custom tag properites.
	 */
	public function get_full_tag_properties( $tag, $form = null ) {

		$custom_props  = $this->get_custom_properties();
		$default_props = $this->get_tag_properties( $tag );

		$props = wp_parse_args( $custom_props, $default_props );

		if ( $form ) {
			$props = $this->replace_merge_tags( $props, $form );
		}

		return $props;
	}

	public function replace_merge_tags( $props, $form ) {

		foreach ( $props as &$value ) {
			$value = str_replace( '{formId}', $form['id'], $value );
		}

		return $props;
	}

	public function get_default_args() {
		return array(
			'tag'     => false,
			'form_id' => false,
		);
	}

	public function build_properties_string( $tag_props ) {

		$prop_strings = array();
		foreach ( $tag_props as $prop => $value ) {
			$prop_strings[] = sprintf( "%s='%s'", $prop, $value );
		}

		return implode( ' ', $prop_strings );
	}

}

# Configuration

if ( class_exists( 'GW_Tag_Editor' ) ) {
	new GW_Tag_Editor( array(
		'tag'     => 'form',
		'form_id' => 123,
		'action'  => 'http://google.com',
	) );
}
