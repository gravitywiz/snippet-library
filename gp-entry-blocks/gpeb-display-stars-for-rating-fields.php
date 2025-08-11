<?php
/**
 * Gravity Perks // Entry Blocks // Display Stars for Rating Fields
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 */
add_filter( 'gpeb_entry', function( $entry, $form ) {

	// Configure star settings
	$config = [
		'star_size'        => 24,        // Width/height of stars in pixels
		'star_color'       => '#FFAC33', // Color of the stars
		'stroke_width'     => 1.5,       // Thickness of star outline
		'show_empty_stars' => false,     // Whether to show empty stars
	];

	$star_size    = max( 1, (int) $config['star_size'] );
	$stroke_width = (float) $config['stroke_width'];
	$star_color   = sanitize_hex_color( $config['star_color'] ) ?: '#FFAC33';

	$star_svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . esc_attr( $star_size ) . '" height="' . esc_attr( $star_size ) . '" viewBox="0 0 24 24" stroke="' . esc_attr( $star_color ) . '" stroke-width="' . esc_attr( $stroke_width ) . '" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>';

	foreach ( $form['fields'] as $field ) {
		if ( $field->get_input_type() === 'rating' ) {
			$selected_value = $entry[ $field->id ];
			foreach ( $field->choices as $index => $choice ) {
				if ( $choice['value'] === $selected_value ) {
					$filled_stars = str_repeat(str_replace('<svg ', '<svg fill="' . esc_attr( $star_color ) . '" class="gpeb-filled-star" ', $star_svg), $index + 1);
					$empty_stars = $config['show_empty_stars'] ? str_repeat(str_replace('<svg ', '<svg fill="none" class="gpeb-outline-star" ', $star_svg), 5 - ($index + 1)) : '';

					$entry[$field->id] = $filled_stars . $empty_stars;
					break;
				}
			}
		}
	}
	return $entry;
}, 10, 2 );

// Unescape HTML-encoded SVG content for rating fields
if ( ! function_exists( 'decode_rating_field_svgs' ) ) {
	function decode_rating_field_svgs($content, $entry_form, $entry) {
		foreach ($entry_form['fields'] as $field) {
			if ($field->get_input_type() === 'rating' && isset($entry[$field->id]) && strpos($entry[$field->id], '<svg') !== false) {
				$content = str_replace(esc_html($entry[$field->id]), $entry[$field->id], $content);
			}
		}
		return $content;
	}
}
add_filter('gpeb_loop_entry_content', 'decode_rating_field_svgs', 10, 3);
add_filter('gpeb_view_entry_content', 'decode_rating_field_svgs', 10, 3);