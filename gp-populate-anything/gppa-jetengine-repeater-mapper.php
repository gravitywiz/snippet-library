<?php
/**
 * Gravity Perks // Populate Anything // JetEngine Repeater Mapper
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Populate all rows from a JetEngine Repeater into a choice-based field.
 * 
 * Instruction Video
 *
 * https://www.loom.com/share/2266f91f6bd942c6915fcb8e60e819a6
 *
 * Instructions
 *
 * 1. Enable "Populate choices dynamically" on any choice-based field.
 * 2. Select the "Post" object type.
 * 3. Apply any desired filters to determine which post(s) should have their repeater data populated.
 * 4  Use the "Choice Template" to map each choice property to a repeater subfield. To do so you will need to:
 *
 *    a. Select "Custom Value" from the "Value" dropdown and specify the repeater name and desire subfield like so:
 *       "repeater_name/subfield_name".
 *    b. Repeat for each Choice Template property (e.g. Label, and Price, if applicable).
 *
 * 5. Add the "gppa-jetengine-repeater-mapper" to the field's CSS Class Name setting.
 *
 * Plugin Name:  GP Populate Anything — JetEngine Repeater Mapper
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Populate all rows from a JetEngine Repeater into a choice-based field.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
add_filter( 'gppa_input_choices', function( $choices, $field, $objects ) {

	if ( strpos( $field->cssClass, 'gppa-jetengine-repeater-mapper' ) === false ) {
		return $choices;
	}

	$map = array();

	foreach ( $field->{'gppa-choices-templates'} as $template => $key ) {
		// Look for ACF repeater meta: gf_custom:repeater-name/subfield-name
		if ( preg_match( '/^gf_custom:(.+?)\/(.+?)$/', $key, $matches ) ) {
			list( , $repeater, $subfield ) = $matches;
			$map[ $template ]              = $subfield;
		}
	}

	if ( ! $repeater ) {
		return $choices;
	}

	$choices = array();

	foreach ( $objects as $object ) {
		$rows = rgar( get_post_meta( $object->ID, $repeater ), 0 );
		if ( $rows ) {
			foreach ( $rows as $row ) {
				if ( isset( $map['price'] ) ) {
					$choices[] = array(
						'value' => rgar( $row, $map['value'] ),
						'text'  => rgar( $row, $map['label'] ),
						'price' => rgar( $row, $map['price'] ),
					);
				} else {
					$choices[] = array(
						'value' => rgar( $row, $map['value'] ),
						'text'  => rgar( $row, $map['label'] ),
					);
				}
			}
		}
	}

	return $choices;
}, 10, 3 );
