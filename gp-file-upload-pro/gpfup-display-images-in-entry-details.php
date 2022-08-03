<?php
/**
 * Gravity Perks // File Upload Pro // Show Images in Entry Details
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instead of an unordered list with links to the files, display the images. Image previews will be hyperlinked to the
 * original image.
 *
 * Instructions: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
add_filter( 'gform_entry_field_value', function ( $display_value, $field, $entry, $form ) {
	if ( ! rgar( $field, 'multipleFiles' ) || ! rgar( $field, 'gpfupEnable' ) ) {
		return $display_value;
	}

	/**
	 * Set to false if the images should not be links.
	 */
	$link_images = true;

	/**
	 * @var string Value from the entry. Is JSON if files are provided.
	 */
	$entry_value = rgar( $entry, $field->id );

	if ( ! $entry_value ) {
		return $entry_value;
	}

	$file_urls = json_decode( $entry_value, true );
	$html      = '';

	foreach ( $file_urls as $file_index => $file_url ) {
		$html .= '<a href="' . esc_url( $file_url ) . '"><img src="' . $file_url . '" style="max-width: 100%"  /></a>';
	}

	/**
	 * Strip all HTML except for img, a, ul, li, and br tags.
	 */
	return '<style>
.gpfup-preview-container { display: flex; flex-wrap: wrap; }
.gpfup-preview-container a {
	flex: 1; 
	flex-basis: 25%; 
	max-width: 25%; 
	overflow: hidden; 
	padding: 0.4rem;
	box-sizing: border-box;
}
.gpfup-preview-container img {
	display: block;
	object-fit: cover;
	aspect-ratio: 1/1;
}
</style>
<div class="gpfup-preview-container">' . wp_kses( $html, array(
			'img' => array(
				'src'   => array(),
				'style' => array(),
			),
			'a'   => array( 'href' => array() ),
			'br'  => array(),
			'ul'  => array(),
			'li'  => array(),
		) ) . '</div>';
}, 10, 4 );
