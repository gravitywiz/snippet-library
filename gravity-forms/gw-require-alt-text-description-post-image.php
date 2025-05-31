<?php
/**
 * Gravity Wiz // Gravity Forms // Required Alt Text & Description for Post Image Field
 * https://gravitywiz.com/
 *
 * Ensures the Alt Text and Description for the Post Image field are required if the field is marked as required.
 * 
 * Instruction Video: https://www.loom.com/share/6d0e70da14c64f5ea40d0aa0f918684d
 *
 * Plugin Name:  Gravity Forms - Required Alt Text & Description for Post Image Field
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Ensures the Alt Text and Description for the Post Image field are required if the field is marked as required.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_field_validation', function ( $result, $value, $form, $field ) {
	if ( $field->type == 'post_image' && $field->displayAlt && $field->displayDescription && $field->wasRequired ) {

		$alt_text    = $value[ $field->id . '.2' ];
		$description = $value[ $field->id . '.7' ];

		if ( ! $alt_text && ! $description ) {
			$result['is_valid'] = false;
			$result['message']  = esc_html__( 'Check Post Image. Please enter Alt Text and Description.', 'gravityforms' );
		} elseif ( ! $alt_text ) {
			$result['is_valid'] = false;
			$result['message']  = esc_html__( 'Check Post Image. Please enter Alt Text.', 'gravityforms' );
		} elseif ( ! $description ) {
			$result['is_valid'] = false;
			$result['message']  = esc_html__( 'Check Post Image. Please enter Description.', 'gravityforms' );
		}

	}
	return $result;
}, 10, 4 );
