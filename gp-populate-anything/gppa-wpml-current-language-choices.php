<?php
/**
 * Gravity Perks // Populate Anything // WPML Current Language Choices
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * With WPML you can create multiple versions of posts each with their own language.
 * When populating posts with Populate Anything, there's no way to identify the current
 * language and filter by that language with the existing UI. This snippet automatically
 * filters posts by the current language.
 *
 * Instructions:
 * 1. [Install the snippet.](https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets)
 * 2. Add the CSS class `wpml-limit-to-current-language` to any GPPA-enabled field you want filtered.
 * 3. Populate that field with posts; the snippet filters choices to the current WPML language.
 */
add_filter( 'gppa_input_choices', 'hb_wpml_limit_gppa_choices_to_current_language', 10, 4 );

function hb_wpml_limit_gppa_choices_to_current_language( $choices, $field, $objects, $field_values ) {
	// Ensure WPML is active.
	if ( ! function_exists( 'icl_object_id' ) ) {
		return $choices;
	}

	// Only apply filtering if the marker CSS class is present.
	$css_class = isset( $field->cssClass ) ? $field->cssClass : '';
	if ( strpos( $css_class, 'wpml-limit-to-current-language' ) === false ) {
		return $choices;
	}

	// Get current WPML language (e.g. 'nl', 'en').
	$current_lang = apply_filters( 'wpml_current_language', null );
	if ( ! $current_lang ) {
		return $choices;
	}

	$filtered_choices = [];

	foreach ( $choices as $index => $choice ) {
		$post_id   = 0;
		$post_type = null;

		// Preferred: WP_Post object from $objects array.
		if ( isset( $objects[ $index ] ) && $objects[ $index ] instanceof WP_Post ) {
			$post_id   = $objects[ $index ]->ID;
			$post_type = $objects[ $index ]->post_type;
		}

		// Fallback: use the choice value as post ID.
		if ( ! $post_id && isset( $choice['value'] ) ) {
			$post_id = absint( $choice['value'] );

			if ( $post_id && ! $post_type ) {
				$post_obj  = get_post( $post_id );
				$post_type = $post_obj ? $post_obj->post_type : null;
			}
		}

		// If we can resolve a post and post type, apply WPML language check.
		if ( $post_id && $post_type ) {
			// Convert the post type to a WPML element type, e.g. 'post_post'.
			$element_type = apply_filters( 'wpml_element_type', $post_type );

			// Get the language code for this specific element.
			$choice_lang = apply_filters(
				'wpml_element_language_code',
				null,
				[
					'element_id'   => $post_id,
					'element_type' => $element_type,
				]
			);

			// If WPML knows the language and it does not match the current language, skip this choice.
			if ( $choice_lang && $choice_lang !== $current_lang ) {
				continue;
			}
		}

		// Keep the choice.
		$filtered_choices[] = $choice;
	}

	return $filtered_choices;
}
