<?php
/**
* Gravity Wiz // Gravity Forms // Use List Field as Choices for Gravity Forms
* https://gravitywiz.com/use-list-field-choices-gravity-forms/
*
* Adds support for populating choice-based fields (i.e. checkboxes, selects, radio buttons) with values entered in a
* List field. This functionality requires that the form has multiple pages and that the List field must be placed on
* a page prior to the choice-based field that it will populate.
*
* Plugin Name:  Gravity Forms - Use List Field as Choices
* Plugin URI:   https://gravitywiz.com/use-list-field-choices-gravity-forms/
* Description:  Adds support for populating choice-based fields with values entered in a List field.
* Author:       Gravity Wiz
* Version:      1.0
* Author URI:   https://gravitywiz.com/
*/
class GW_List_Field_As_Choices {

	private $_args = array();

	function __construct( $args ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'          => false,
			'list_field_id'    => false,
			'choice_field_ids' => false,
			'label_template'   => '{0}',
			'sort'             => false,
		) );

		if ( ! is_array( $this->_args['choice_field_ids'] ) ) {
			$this->_args['choice_field_ids'] = array( $this->_args['choice_field_ids'] );
		}

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $this->_args ); // gives us $form_id, $list_field_id, $choices_field_id

		add_filter( 'gform_pre_render', array( $this, 'populate_choice_fields' ), 9 );
		add_filter( 'gform_pre_validation', array( $this, 'populate_choice_fields' ), 9 );
		add_filter( 'gform_pre_submission_filter_' . $form_id, array( $this, 'populate_choice_fields' ) );

	}

	function populate_choice_fields( $form ) {

		if ( $form['id'] != $this->_args['form_id'] ) {
			return $form;
		}

		$list_field = GFFormsModel::get_field( $form, $this->_args['list_field_id'] );
		$values     = GFFormsModel::get_field_value( $list_field );

		/**
		 * Filter the values from the List field that will be used to populate field choices.
		 *
		 * @param array|mixed|string $values The List field values that will be used to populate field choices.
		 * @param array              $form   The current form.
		 * @param array              $args   The arguments used to initialize this instance of GW_List_Field_As_Choices.
		 */
		$values = apply_filters( 'gwlfac_list_field_values', $values, $form, $this->_args );

		// if list field doesn't have any values, let's ditch this party
		if ( ! is_array( $values ) ) {
			return $form;
		}

		$choices = $this->get_list_choices( $values );

		foreach ( $form['fields'] as &$field ) {

			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			// set 'choices' for choice fields
			$field['choices'] = $choices;

			// only set inputs for 'checkbox' choice fields
			if ( GFFormsModel::get_input_type( $field ) == 'checkbox' ) {
				$inputs = array();
				foreach ( $choices as $index => $choice ) {
					$inputs[] = array(
						'label' => $choice['text'],
						'id'    => $field['id'] . '.' . ( $index + 1 ),
					);
				}
				$field['inputs'] = $inputs;
			}
		}

		return $form;
	}

	function get_list_choices( $values ) {

		$choices = array();

		foreach ( $values as $row ) {

			$label = $this->replace_template( $this->_args['label_template'], $row );
			$value = isset( $this->_args['value_template'] ) ? $this->replace_template( $this->_args['value_template'], $row ) : $label;

			$choices[] = array(
				'text'  => $label,
				'value' => $value,
			);

		}

		if ( $this->_args['sort'] == true ) {
			usort( $choices, function( $a, $b ) {
				return strnatcasecmp( $a['text'], $b['text'] );
			} );
		}

		return $choices;
	}

	function replace_template( $template, $row ) {

		// combine our templates so we can find all matches at once
		preg_match_all( '/{(\w+)}/', $template, $matches, PREG_SET_ORDER );

		if ( is_array( $row ) ) {

			$mega_row = array_merge( $row, array_values( $row ) );

			foreach ( $matches as $match ) {
				$template = str_replace( $match[0], rgar( $mega_row, $match[1] ), $template );
			}
		} else {

			foreach ( $matches as $match ) {
				$template = str_replace( $match[0], $row, $template );
			}
		}

		return $template;
	}

	function is_applicable_field( $field ) {

		$is_choice_field     = is_array( rgar( $field, 'choices' ) );
		$is_registered_field = in_array( $field['id'], $this->_args['choice_field_ids'] );

		return $is_choice_field && $is_registered_field;
	}

}

/**
* Uncomment the code below by removing the pound symbols (#) in front of each line. See @link in the comment section
* at the top for additional usage instructions.
*/

# Basic Usage
//new GW_List_Field_As_Choices( array(
//	'form_id'          => 1,
//	'list_field_id'    => 2,
//	'choice_field_ids' => 3,
//) );


# Enable Sorting of Choices Alphanumerically
//new GW_List_Field_As_Choices( array(
//	'form_id'          => 1,
//	'list_field_id'    => 2,
//	'choice_field_ids' => 3,
//	'sort'             => true,
//) );

# Populating Multiple Choice Fields
//new GW_List_Field_As_Choices( array(
//	'form_id'          => 384,
//	'list_field_id'    => 3,
//	'choice_field_ids' => array( 6, 7 ),
//) );


# Customizing the Choice Label and Value
//new GW_List_Field_As_Choices( array(
//	'form_id'          => 384,
//	'list_field_id'    => 2,
//	'choice_field_ids' => array( 4, 5 ),
//	'label_template'   => '{Name} <span style="color:#999;font-style:italic;">({Age})</span>',
//	'value_template'   => '{Name}',
//) );

# Filter Usage
## Customize List field values to be populated as choices based on Gravity Flow User Input step.
//add_filter( 'gwlfac_list_field_values', function( $values, $form, $args ) {
//	if ( is_array( $values ) ) {
//		return $values;
//	}

# Confirm we are within a Gravity Flow Inbox.
//	if ( rgget( 'lid' ) && rgget( 'page' ) == 'gravityflow-inbox' ) {
//		$entry = GFAPI::get_entry( (int) rgget( 'lid' ) );
//		// Verify the entry list field has previously stored values to use.
//		if ( $entry ) {
//			$values = unserialize( $entry[ $args['list_field_id'] ] );
//			if ( ! is_array( $values ) ) {
//				return false;
//			} else {
//				return $values;
//			}
//		}
//	}
//	return false;
//}, 10, 3 );
