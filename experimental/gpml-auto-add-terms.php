<?php
/**
 * Gravity Perks // Media Library // Add terms to new Media Library files.
 * https://gravitywiz.com/documentation/gravity-forms-media-library/
 */
// Update "123" to your form ID and "4" to your GPML-enabled File Upload field.
add_action( 'gpml_media_data_123_4', function( $gpml_media_data ) {
	$terms                                     = array(
		'category' => array(
			'Red',
			'Green',
			'Blue',
		),
		'post_tag' => array(
			'Small',
			'Medium',
			'Large',
		),
	);
	$gpml_media_data['post_data']['tax_input'] = $terms;
	return $gpml_media_data;
}, 10, 2 );
