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
 * Version:      0.6
 * Author URI:   https://gravitywiz.com
 */
class GW_Format_Date_Merge_Tag {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
			'locale'   => null,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gform_pre_replace_merge_tags', array( $this, 'replace_merge_tags' ), 10, 7 );
	}

	public function replace_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {
		$current_locale = determine_locale();
		$locale         = $this->_args['locale'];
		if ( $locale ) {
			switch_to_locale( $locale );
		}

		preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $text, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {

			$input_id = $match[1];
			$field    = GFFormsModel::get_field( $form, $input_id );

			if ( ! $field || $field->get_input_type() !== 'date' ) {
				continue;
			}

			$i        = $match[0][0] === '{' ? 4 : 5;
			$modifier = rgar( array_map( 'trim', explode( ',', rgar( $match, $i ) ) ), 0 );
			if ( ! $modifier ) {
				continue;
			}

			$value = GFFormsModel::get_lead_field_value( $entry, $field );
			$value = $field->get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $value, $url_encode, $esc_html, $format, $nl2br );
			if ( ! $value ) {
				continue;
			}

			$format      = $field->dateFormat ? $field->dateFormat : 'mdy';
			$parsed_date = GFCommon::parse_date( $value, $format );

			// On the Notifications/Confirmation side, & gets encoded to &amp;. Decode it back.
			$modifier = htmlspecialchars_decode( $modifier );

			// For whatever reason, Populate Anything's LMTs works better with `&comma` than `&#44;`. But... date() doesn't
			// like it so let's replace it before we pass it to date().
			$modifier = str_replace( '&comma;', ',', $modifier );

			$timestamp = strtotime( sprintf( '%d-%d-%d', $parsed_date['year'], $parsed_date['month'], $parsed_date['day'] ) );

			// phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
			$replace = wp_date( $modifier, $timestamp, new DateTimeZone( 'UTC' ) );

			$text = str_replace( $match[0], $replace, $text );
		}

		// Switch back to default locale.
		if ( $locale ) {
			switch_to_locale( $current_locale );
		}

		return $text;
	}

	public function is_applicable_form( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}
}

# Configuration

new GW_Format_Date_Merge_Tag();

# Apply locale to all forms.
//new GW_Format_Date_Merge_Tag( array(
//	'locale'  => 'fr_FR',
//) );

# Apply locale to a specific form.
//new GW_Format_Date_Merge_Tag( array(
//	'form_id' => 123,
//	'locale' => 'fr_FR',
//) );
