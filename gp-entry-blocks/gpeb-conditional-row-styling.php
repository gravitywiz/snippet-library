<?php
/**
 * Gravity Perks // Entry Blocks // Conditional Row Styling
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Apply custom CSS styling to table rows based on entry field values or entry properties.
 * Style rows differently based on status fields, payment status, user roles, or any other entry data.
 *
 * Instructions:
 *
 * 1. Install this snippet with your preferred snippet management solution.
 *    https://gravitywiz.com/documentation/managing-snippets/
 *
 * 2. Update the configuration section at the bottom of this snippet with your form ID and styling rules.
 *
 * 3. Configure your rules:
 *    - 'field_id' can be a form field ID (e.g., 5) or an entry property:
 *      Common entry properties: 'payment_status', 'payment_amount', 'is_starred',
 *      'is_read', 'created_by', 'status', 'date_created', 'source_url', 'ip', etc.
 *    - 'conditions' maps field values to CSS styles (for exact matches)
 *    - 'operator_conditions' allows comparison operators (>, <, >=, <=, !=, contains)
 *    - Styles can include any valid CSS property (use underscores or hyphens: background_color or 'background-color')
 *
 * 4. Multiple rules can be stacked - if multiple conditions match, all styles will be applied.
 */
class GPEB_Conditional_Row_Styling {

	private $form_id;
	private $rules;

	public function __construct( $config = array() ) {
		if ( empty( $config['form_id'] ) || empty( $config['rules'] ) ) {
			return;
		}

		$this->form_id = rgar( $config, 'form_id' );
		$this->rules   = rgar( $config, 'rules', array() );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		if ( ! function_exists( 'gp_entry_blocks' ) ) {
			return;
		}

		add_filter( 'gpeb_table_row_markup', array( $this, 'apply_conditional_styling' ), 10, 2 );
	}

	private function is_applicable_form( $current_form_id ) {
		return (int) $current_form_id === (int) $this->form_id;
	}

	public function apply_conditional_styling( $output, $row ) {
		if ( empty( $this->rules ) ) {
			return $output;
		}

		$entry = $row->entry;

		if ( ! $this->is_applicable_form( $entry['form_id'] ) ) {
			return $output;
		}

		$inline_styles = $this->get_matching_styles( $entry );

		if ( empty( $inline_styles ) ) {
			return $output;
		}

		return $this->inject_style_attribute( $output, $this->build_style_string( $inline_styles ) );
	}

	private function get_matching_styles( $entry ) {
		$styles = array();

		foreach ( $this->rules as $rule ) {
			$field_id = rgar( $rule, 'field_id' );

			if ( ! $field_id ) {
				continue;
			}

			$field_value = rgar( $entry, $field_id, '' );

			// Handle exact match conditions.
			$conditions = rgar( $rule, 'conditions', array() );
			foreach ( $conditions as $match_value => $rule_styles ) {
				if ( (string) $field_value === (string) $match_value && is_array( $rule_styles ) ) {
					$styles = array_merge( $styles, $rule_styles );
				}
			}

			// Handle operator-based conditions.
			$operator_conditions = rgar( $rule, 'operator_conditions', array() );
			foreach ( $operator_conditions as $condition ) {
				$operator      = rgar( $condition, 'operator', '=' );
				$compare_value = rgar( $condition, 'value' );
				$rule_styles   = rgar( $condition, 'styles', array() );

				if ( $this->evaluate_condition( $field_value, $operator, $compare_value ) && is_array( $rule_styles ) ) {
					$styles = array_merge( $styles, $rule_styles );
				}
			}
		}

		return $styles;
	}

	private function evaluate_condition( $field_value, $operator, $compare_value ) {
		// Handle non-numeric operators first.
		if ( $operator === '!=' ) {
			return (string) $field_value !== (string) $compare_value;
		}

		if ( $operator === 'contains' ) {
			return stripos( (string) $field_value, (string) $compare_value ) !== false;
		}

		// Strip non-numeric characters for numeric comparisons.
		$numeric_value = floatval( preg_replace( '/[^0-9.\-]/', '', $field_value ) );

		switch ( $operator ) {
			case '>':
				return $numeric_value > (float) $compare_value;

			case '>=':
				return $numeric_value >= (float) $compare_value;

			case '<':
				return $numeric_value < (float) $compare_value;

			case '<=':
				return $numeric_value <= (float) $compare_value;

			default:
				return false;
		}
	}

	private function build_style_string( $styles ) {
		$style_parts = array();

		foreach ( $styles as $property => $value ) {
			$css_property  = str_replace( '_', '-', $property );
			$css_value     = esc_attr( $value );
			$style_parts[] = $css_property . ': ' . $css_value;
		}

		return implode( '; ', $style_parts ) . ';';
	}

	private function inject_style_attribute( $html, $new_styles ) {
		// Check for existing style attribute with double quotes.
		if ( preg_match( '/<tr\s[^>]*style="([^"]*)"/i', $html, $matches ) ) {
			$combined = rtrim( $matches[1], '; ' ) . '; ' . $new_styles;
			return preg_replace(
				'/(<tr\s[^>]*)style="[^"]*"/i',
				'$1style="' . esc_attr( $combined ) . '"',
				$html,
				1
			);
		}

		// Check for existing style attribute with single quotes.
		if ( preg_match( "/<tr\s[^>]*style='([^']*)'/i", $html, $matches ) ) {
			$combined = rtrim( $matches[1], '; ' ) . '; ' . $new_styles;
			return preg_replace(
				"/(<tr\s[^>]*)style='[^']*'/i",
				'$1style="' . esc_attr( $combined ) . '"',
				$html,
				1
			);
		}

		// No existing style attribute, add one.
		return preg_replace(
			'/<tr(\s|>)/i',
			'<tr style="' . esc_attr( $new_styles ) . '"$1',
			$html,
			1
		);
	}
}

# Configuration
new GPEB_Conditional_Row_Styling( array(
	'form_id' => 123, // Update to your form ID.

	'rules' => array(

		// Example 1: Style rows by payment status (exact match).
		array(
			'field_id'   => 'payment_status',
			'conditions' => array(
				'Paid'    => array(
					'background-color' => '#d4edda',
					'border-left'      => '4px solid #28a745',
				),
				'Pending' => array(
					'background-color' => '#fff3cd',
					'border-left'      => '4px solid #ffc107',
				),
				'Failed'  => array(
					'background-color' => '#f8d7da',
					'border-left'      => '4px solid #dc3545',
				),
			),
		),

		// Example 2: Style rows by a dropdown field value (field ID 5).
		array(
			'field_id'   => 5,
			'conditions' => array(
				'High Priority' => array(
					'background-color' => '#fff0f0',
					'font-weight'      => 'bold',
					'color'            => '#c00',
				),
				'Low Priority'  => array(
					'background-color' => '#f0f0f0',
					'color'            => '#666',
					'font-style'       => 'italic',
				),
			),
		),

		// Example 3: Highlight starred entries.
		array(
			'field_id'   => 'is_starred',
			'conditions' => array(
				'1' => array(
					'background-color' => '#fffbeb',
					'border-left'      => '4px solid #f59e0b',
				),
			),
		),

		// Example 4: Style based on numeric comparison (e.g., order total in field 10).
		array(
			'field_id'            => 10,
			'operator_conditions' => array(
				array(
					'operator' => '>=',
					'value'    => 500,
					'styles'   => array(
						'background-color' => '#d4edda',
						'font-weight'      => 'bold',
					),
				),
				array(
					'operator' => '<',
					'value'    => 50,
					'styles'   => array(
						'background-color' => '#f8d7da',
						'color'            => '#721c24',
					),
				),
			),
		),

		// Example 5: Use != operator to highlight incomplete entries.
		array(
			'field_id'            => 8, // Status field.
			'operator_conditions' => array(
				array(
					'operator' => '!=',
					'value'    => 'Complete',
					'styles'   => array(
						'background-color' => '#fff3cd',
						'border-left'      => '4px solid #856404',
					),
				),
			),
		),

		// Example 6: Use 'contains' to match partial text.
		array(
			'field_id'            => 3, // Email field.
			'operator_conditions' => array(
				array(
					'operator' => 'contains',
					'value'    => '@company.com',
					'styles'   => array(
						'background-color' => '#e7f3ff',
						'border-left'      => '4px solid #0066cc',
					),
				),
			),
		),

	),
) );
