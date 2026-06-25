<?php
/**
 * Gravity Wiz // Gravity Forms // Choice Groups
 * https://gravitywiz.com/
 *
 * Organize Drop Down and Multi Select choices into visual groups using special heading choices.
 *
 * Syntax:
 *
 * [Group Name]
 * - Choice 1
 * - Choice 2
 * [Group Name 2]
 * - Choice A
 * - Choice B
 * Standalone Choice
 * 
 * Example:
 *
 * [Category 1]
 * - First Choice
 * - Second Choice
 * [Category 2]
 * - Choice A
 * - Choice B
 * Standalone Choice
 *
 * Supported Fields:
 * - Drop Down
 * - Multi Select
 *
 * Plugin Name:  Gravity Forms - Choice Groups for Drop Down and Multi Select
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Organize Drop Down and Multi Select choices into visual groups using special heading choices.
 * Author:       Gravity Wiz
 * Version:      0.3
 * Author URI:   https://gravitywiz.com/
 */

class GW_Choice_Groups {

	const GROUP_PATTERN      = '/^\[(.+)\]$/';
	const GROUP_ITEM_PATTERN = '/^[-*•–]\s*(.+)$/u';

	public function __construct() {
		add_filter( 'gform_field_input', array( $this, 'field_input' ), 10, 5 );
	}

	public function field_input( $input, $field, $value, $entry_id, $form_id ) {

		if ( ! in_array( $field->type, array( 'select', 'multiselect' ), true ) ) {
			return $input;
		}

		if ( ! $this->has_groups( $field->choices ) ) {
			return $input;
		}

		$is_multiselect = $field->type === 'multiselect';
		$groups         = $this->get_groups( $field->choices );

		$field_id = (int) $field->id;

		$name  = 'input_' . $field_id . ( $is_multiselect ? '[]' : '' );
		$class = sprintf(
			'%s %s',
			$field->size,
			$is_multiselect ? 'gfield_multiselect' : 'gfield_select'
		);

		$select_attributes = array(
			sprintf( 'name="%s"', esc_attr( $name ) ),
			sprintf( 'id="input_%d_%d"', $form_id, $field_id ),
			sprintf( 'class="%s"', esc_attr( $class ) ),
		);

		if ( $is_multiselect ) {
			$select_attributes[] = 'multiple="multiple"';
			$select_attributes[] = 'size="5"';
		}

		if ( $tabindex = $field->get_tabindex() ) {
			$select_attributes[] = trim( $tabindex );
		}
		$input = sprintf(
			'<select %s>',
			implode( ' ', $select_attributes )
		);

		if ( ! $is_multiselect && ! empty( $field->placeholder ) ) {
			$input .= sprintf(
				'<option value="">%s</option>',
				esc_html( $field->placeholder )
			);
		}

		foreach ( $groups as $group ) {

			if ( isset( $group['label'] ) ) {

				$input .= sprintf(
					'<optgroup label="%s">',
					esc_attr( $group['label'] )
				);

				foreach ( $group['choices'] as $choice ) {
					$input .= $this->get_option_markup( $choice, $value );
				}

				$input .= '</optgroup>';

			} else {

				$input .= $this->get_option_markup( $group, $value );

			}
		}

		$input .= '</select>';

		return $input;

	}

	private function has_groups( $choices ) {

		foreach ( $choices as $choice ) {

			if ( preg_match( self::GROUP_PATTERN, trim( rgar( $choice, 'text' ) ) ) ) {
				return true;
			}
		}

		return false;

	}

	private function get_groups( $choices ) {

		$groups = array();

		$current_group_index = null;

		foreach ( $choices as $choice ) {

			$text = trim( rgar( $choice, 'text' ) );

			// Start a new group/optgroup.
			if ( preg_match( self::GROUP_PATTERN, $text, $matches ) ) {

				$groups[] = array(
					'label'   => trim( $matches[1] ),
					'choices' => array(),
				);

				$current_group_index = count( $groups ) - 1;

				continue;

			}

			// This choice belongs to the currently open group (prefixed with -, * or •).
			if ( $current_group_index !== null && preg_match( self::GROUP_ITEM_PATTERN, $text, $matches ) ) {

				$choice['text'] = trim( $matches[1] );

				$groups[ $current_group_index ]['choices'][] = $choice;

				continue;

			}

			$current_group_index = null;

			$groups[] = $choice;

		}

		return $groups;

	}

	private function get_option_markup( $choice, $value ) {

		$choice_value = rgar( $choice, 'value' );

		if ( $choice_value === '' || $choice_value === null ) {
			$choice_value = rgar( $choice, 'text' );
		}

		$selected = '';

		if ( is_array( $value ) ) {

			if ( in_array( (string) $choice_value, array_map( 'strval', $value ), true ) ) {
				$selected = ' selected="selected"';
			}
		} elseif ( (string) $choice_value === (string) $value ) {
			$selected = ' selected="selected"';
		}

		return sprintf(
			'<option value="%s"%s>%s</option>',
			esc_attr( $choice_value ),
			$selected,
			esc_html( rgar( $choice, 'text' ) )
		);

	}

}

new GW_Choice_Groups();
