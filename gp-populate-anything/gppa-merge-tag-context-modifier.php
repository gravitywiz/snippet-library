<?php
/**
 * Gravity Perks // Populate Anything // Replace Merge Tags in Specific Context
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * @video https://www.loom.com/share/d626a60769ee48579b6f426a677918a3
 *
 * This snippet allows you to replace a merge tag in the context of an entry selected in a GPPA-populated field. For
 * example, if you populate a Drop Down field with entries and then have an HTML field which includes the {all_fields}
 * merge tag, you could use the context modifier to specify that Drop Down field as the context like so:
 *
 * {all_fields:context[1]}
 *
 * In this example, we'll assume that the ID of the Drop Down field is 1.
 *
 * Note: This currently is limited to entries.
 *
 * Plugin Name:  Populate Anything - Replace Merge Tags in Specific Context
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  This snippet allows you to replace a merge tag in the context of an entry selected in a GPPA-populated field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gppa_live_merge_tag_value', function( $merge_tag_match_value, $merge_tag, $form, $field_id, $entry_values ) {

	if ( strpos( $merge_tag, 'context[' ) === false ) {
		return $merge_tag_match_value;
	}

	if ( ! function_exists( 'gw_parse_modifiers' ) ) {
		function gw_parse_modifiers( $modifiers_str ) {

			preg_match_all( '/([a-z]+)(?:(?:\[(.+?)\])|,?)/i', $modifiers_str, $modifiers, PREG_SET_ORDER );
			$parsed  = array();

			foreach( $modifiers as $modifier ) {

				list( $match, $modifier, $value ) = array_pad( $modifier, 3, null );
				if( $value === null ) {
					$value = $modifier;
				}

				// Split '1,2,3' into array( 1, 2, 3 ).
				if( strpos( $value, ',' ) !== false ) {
					$value = array_map( 'trim', explode( ',', $value ) );
				}

				$parsed[ strtolower( $modifier ) ] = $value;

			}

			return $parsed;
		}
	}

	$bits      = explode( ':', preg_replace( '/[{}]/', '', $merge_tag ) );
	$modifiers = gw_parse_modifiers( array_pop( $bits ) );
	$context   = rgar( $modifiers, 'context' );

	if ( ! $context ) {
		return $merge_tag_match_value;
	}

	$context_entry = GFAPI::get_entry( $entry_values[ $context ] );
	$context_field = GFAPI::get_field( $form, $context );
	$context_form  = $form;

	// Check if the context field is populating from a different form. If so, fetch that form.
	if ( $form['id'] != $context_field->{'gppa-choices-primary-property'} ) {
		$context_form = GFAPI::get_form( $context_field->{'gppa-choices-primary-property'} );
	}

	// Replace variables in the specified context.
	$merge_tag_match_value = GFCommon::replace_variables( $merge_tag, $context_form, $context_entry );

	return $merge_tag_match_value;
}, 10, 5 );
