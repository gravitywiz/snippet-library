<?php
/**
 * Gravity Perks // GP Media Library // Populate ACF Image Fields in User Profile
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 *
 * Plugin Name:  GP Media Library - Populate ACF Image Fields in User Profile
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-media-library/
 * Description:  Set the user profile image using image uploaded via Gravity Forms.
 * Author:       Gravity Wiz
 * Version:      1.2
 * Author URI:   https://gravitywiz.com/
 */
class GPML_ACF_User_Image_Field {

	private $_args = array();

	public function __construct( $args ) {

		$this->_args = wp_parse_args(
			$args, array(
				'form_id'         => 0,
				'field_id'        => 0,
				'meta_key'        => '',
				'format'          => 'id',
				'is_multi'        => false,
				'append'          => false,
				'remove_if_empty' => false,
			)
		);

		add_action( 'gform_user_registered', array( $this, 'update_user_image_field' ), 10, 3 );
		add_action( 'gform_user_updated', array( $this, 'update_user_image_field' ), 10, 3 );

	}

	function update_user_image_field( $user_id, $feed, $entry ) {
		if ( $entry['form_id'] == $this->_args['form_id'] && is_callable( 'gp_media_library' ) ) {

			$form  = GFAPI::get_form( $entry['form_id'] );
			$value = gp_media_library()->acf_get_field_value( $this->_args['format'], $entry, GFFormsModel::get_field( $form, $this->_args['field_id'] ), $this->_args['is_multi'] );

			if ( $value && $this->_args['is_multi'] && $this->_args['append'] ) {
				$current_value = wp_list_pluck( (array) get_field( $this->_args['meta_key'], 'user_' . $user_id ), 'ID' );
				$value         = array_merge( $current_value, $value );
			}

			if ( empty( $value ) && ! $this->_args['remove_if_empty'] ) {
				return;
			}

			update_field( $this->_args['meta_key'], $value, 'user_' . $user_id );

		}
	}

}

# Configuration

new GPML_ACF_User_Image_Field( array(
	'form_id'         => 123,
	'field_id'        => 4,
	'meta_key'        => 'your_custom_field',
	'format'          => 'id',
	'is_multi'        => false, // Set to true for ACF Gallery fields.
	'append'          => false, // Set to true to append (rather than replace) values in multi-value fields.
	'remove_if_empty' => false, // Set to true if existing image should be removed if no mapped field is submitted without a new image.
) );
