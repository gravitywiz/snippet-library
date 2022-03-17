<?php
/**
 * Gravity Wiz // Gravity Forms // Format Date Merge Tags
 * https://gravitywiz.com/gravity-forms-date-merge-tags/
 *
 * Adds merge tag modifiers for formatting date merge tags using PHP Date Formats.
 *
 * Plugin Name:  Gravity Forms — Format Date Merge Tags
 * Plugin URI:   https://gravitywiz.com/gravity-forms-date-merge-tags/
 * Description:  Adds merge tag modifiers for formatting date merge tags using PHP Date Formats.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gform_pre_replace_merge_tags', function( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

	preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $text, $matches, PREG_SET_ORDER );

	foreach ( $matches as $match ) {

		$input_id = $match[1];
		$field    = GFFormsModel::get_field( $form, $input_id );

		if ( $field ) {
			if ( $field->get_input_type() !== 'date' ) {
				continue;
			}
		}

		$i        = $match[0][0] === '{' ? 4 : 5;
		$modifier = rgar( array_map( 'trim', explode( ',', rgar( $match, $i ) ) ), 0 );
		if ( ! $modifier ) {
			continue;
		}

		$value = GFFormsModel::get_lead_field_value( $entry, $field );
		$value = $field->get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $value, $url_encode, $esc_html, $format, $nl2br );

		$format      = $field->dateFormat ? $field->dateFormat : 'mdy';
		$parsed_date = GFCommon::parse_date( $value, $format );

		$replace = date( $modifier, strtotime( sprintf( '%d-%d-%d', $parsed_date['year'], $parsed_date['month'], $parsed_date['day'] ) ) );

		$text = str_replace( $match[0], $replace, $text );

	}

	return $text;
}, 10, 7 );
