<?php
/**
 * Gravity Perks // File Upload Pro // Show Images in Entry Details
 * https://gravitywiz.com/documentation/gravity-forms-file-upload-pro/
 *
 * Instead of an unordered list with links to the files, display the images. Non-images will still
 * be displayed as a text link.
 *
 * By default, the images will be hyperlinks linking to the original image.
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
		/* Skip file if the file is not an image supported by mPDF */
		$extension = pathinfo( $file_url, PATHINFO_EXTENSION );
		$file_name = pathinfo( $file_url, PATHINFO_FILENAME );

		if ( ! in_array( strtolower( $extension ), array(
			'gif',
			'png',
			'jpg',
			'wmf',
			'svg',
			'bmp',
		), true ) ) {
			// Link directly to other file types (e.g. PDF) without further processing
			$html .= '<ul><li><a href="' . esc_url( $file_url ) . '">' . $file_name . '</a>' . "</li></ul>\n";
			continue;
		}

		if ( $link_images ) {
			$html .= '<a href="' . esc_url( $file_url ) . '"><img src="' . $file_url . '" style="max-width: 100%"  /></a>' . "\n";
		} else {
			$html .= '<img src="' . $file_url . '" style="max-width: 100%" />' . "\n";
		}

		if ( count( $file_urls ) > 1 && $file_index + 1 < count( $file_urls ) ) {
			$html .= "<br />\n";
		}
	}

	/**
	 * Strip all HTML except for img, a, ul, li, and br tags.
	 */
	return wp_kses( $html, array(
		'img' => array(
			'src'   => array(),
			'style' => array(),
		),
		'a'   => array( 'href' => array() ),
		'br'  => array(),
		'ul'  => array(),
		'li'  => array(),
	) );
}, 10, 4 );
