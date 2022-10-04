<?php
/**
 * Gravity Perks // GP Advanced Phone Field // Add country modifier
 * https://gravitywiz.com/gravity-forms-advanced-phone-field/
 *
 * Adds support for {Phone A:1:phone[country]} modifier which will output the full country name rather than just the
 * ISO 3166-1 alpha-2 country abbreviation that's provided by {Phone A:1:phone[regionCode]}.
 */
add_filter( 'gform_pre_replace_merge_tags', function ( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
	preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $text, $field_variable_matches, PREG_SET_ORDER );

	if ( ! function_exists( 'gp_advanced_phone_field' ) ) {
		return $text;
	}

	foreach ( $field_variable_matches as $match ) {
		$input_id      = $match[1];
		$field         = GFFormsModel::get_field( $form, $input_id );
		$i             = $match[0][0] === '{' ? 4 : 5;
		$modifiers_str = rgar( $match, $i );
		$modifiers     = gp_advanced_phone_field()->parse_modifiers( $modifiers_str );

		if ( ! gp_advanced_phone_field()->is_advanced_phone_field( $field ) ) {
			continue;
		}

		if ( ! isset( $modifiers['phone'] ) ) {
			continue;
		}

		if ( $modifiers['phone'] !== 'country' ) {
			continue;
		}

		/**
		 * @var $proto libphonenumber\PhoneNumber
		 */
		$proto = gform_get_meta( rgar( $entry, 'id' ), "gpapf_proto_{$field->id}" );

		/**
		 * Get proto from meta if set, otherwise try to create one on the fly for situations such as LMTs.
		 */
		if ( ! $proto ) {
			$entry_value = GFFormsModel::get_lead_field_value( $entry, $field );

			try {
				$proto = gp_advanced_phone_field()->get_phone_number_proto( $entry_value );
			} catch ( Exception $e ) {
				// Intentionally blank.
			}

			if ( ! $proto ) {
				continue;
			}
		}

		if ( ! $proto->regionCode ) {
			continue;
		}

		$countries   = GF_Fields::get( 'address' )->get_default_countries();
		$replacement = rgar( $countries, $proto->regionCode );

		if ( $replacement || $replacement === '' ) {
			$text = str_replace( $match[0], $replacement, $text );
		}
	}

	return $text;
}, 10, 7 );
