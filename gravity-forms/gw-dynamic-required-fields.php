<?php
/**
 * Gravity Wiz // Gravity Forms // Dynamically toggle field as required based on control field value
 * https://gravitywiz.com/
 *
 * Instruction Video: https://www.loom.com/share/d7499c8ae2924477ab9fbe5ef5be7c07
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
class GW_Dynamic_Required_Fields {

	public static $instances = array();

	public static function init( $args ) {
		$required_keys = array( 'form_id', 'control_field', 'rules' );

		foreach ( $required_keys as $key ) {
			if ( ! isset( $args[ $key ] ) ) {
				return new WP_Error( 'missing_parameter', "Missing required parameter: {$key}" );
			}
		}

		$instance          = new self( $args );
		self::$instances[] = $instance;

		return $instance;
	}

	private $args = array();

	public function __construct( $args ) {
		$this->args = wp_parse_args(
			$args,
			array(
				'form_id'       => false,
				'control_field' => false,
				'rules'         => array(),
				'field_labels'  => array(),
			)
		);

		$form_id = $this->args['form_id'];

		add_filter( "gform_pre_validation_{$form_id}", array( $this, 'validate_dynamic_fields' ) );
		add_filter( "gform_pre_submission_{$form_id}", array( $this, 'validate_dynamic_fields' ) );

		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
	}

	/**
	 * Validate dynamic fields based on control field value.
	 *
	 * @param array $form The form object.
	 *
	 * @return array Modified form object.
	 */
	public function validate_dynamic_fields( $form ) {
		$control_value   = rgpost( 'input_' . $this->args['control_field'] );
		$required_fields = $this->get_required_fields( $control_value );

		foreach ( $form['fields'] as &$field ) {
			if ( in_array( $field->id, $required_fields, true ) ) {
				$field->isRequired = true;

				$value = rgpost( 'input_' . $field->id );
				if ( GFCommon::is_empty_array( $value ) ) {
					$field->failed_validation  = true;
					$field->validation_message = $this->get_field_label( $field->id ) . ' is required';
				}
			} else {
				$field->isRequired = false;
			}
		}

		return $form;
	}

	/**
	 * Add initialization script for dynamic field requirements.
	 *
	 * @param array $form    The form object.
	 * @param bool  $is_ajax Whether the form is being submitted via AJAX.
	 */
	public function add_init_script( $form, $is_ajax ) {
		if ( $form['id'] !== $this->args['form_id'] ) {
			return;
		}

		$script = $this->get_js();
		$slug   = "gw_dynamic_required_{$form['id']}";

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	/**
	 * Get required fields based on control value.
	 *
	 * @param string $control_value The value of the control field.
	 *
	 * @return array Array of required field IDs.
	 */
	private function get_required_fields( $control_value ) {
		foreach ( $this->args['rules'] as $rule ) {
			if ( $rule['value'] === $control_value ) {
				return $rule['field_ids'];
			}
		}

		return array();
	}

	/**
	 * Get field label for validation message.
	 *
	 * @param int $field_id The field ID.
	 *
	 * @return string The field label.
	 */
	private function get_field_label( $field_id ) {
		if ( isset( $this->args['field_labels'][ $field_id ] ) ) {
			return $this->args['field_labels'][ $field_id ];
		}

		return "Field {$field_id}";
	}

	/**
	 * Generate JavaScript for dynamic field requirements.
	 *
	 * @return string JavaScript code.
	 */
	private function get_js() {
		$form_id  = $this->args['form_id'];
		$control  = $this->args['control_field'];
		$rules_js = array();
		$all_ids  = array();

		foreach ( $this->args['rules'] as $rule ) {
			$rules_js[ $rule['value'] ] = $rule['field_ids'];
			$all_ids                    = array_merge( $all_ids, $rule['field_ids'] );
		}

		$all_ids = array_unique( $all_ids );

		ob_start();
		?>
		(function($) {
			var formId = <?php echo intval( $form_id ); ?>;
			var controlId = <?php echo intval( $control ); ?>;
			var rules = <?php echo wp_json_encode( $rules_js ); ?>;
			var allIds = <?php echo wp_json_encode( $all_ids ); ?>;

			function updateRequirements() {
				var value = $('#input_' + formId + '_' + controlId).val();
				var requiredIds = rules[value] || [];

				allIds.forEach(function(id) {
					var isRequired = requiredIds.includes(id);
					var $field = $('#field_' + formId + '_' + id);
					var $input = $('#input_' + formId + '_' + id);

					if (isRequired) {
						$field.addClass('gfield_contains_required');
						$input.attr('aria-required', 'true');
						if (!$field.find('.gfield_required').length) {
							$field.find('.gfield_label').append('<span class="gfield_required">*</span>');
						}
					} else {
						$field.removeClass('gfield_contains_required');
						$input.attr('aria-required', 'false');
						$field.find('.gfield_required').remove();
						$field.removeClass('gfield_error').find('.validation_message').remove();
					}
				});
			}

			$('#input_' + formId + '_' + controlId).on('change', updateRequirements);
			$(document).on('gform_post_render', updateRequirements);
			$(document).ready(updateRequirements);

		})(jQuery);
		<?php
		return ob_get_clean();
	}
}

# Configuration

new GW_Dynamic_Required_Fields(
	array(
		'form_id'       => 3,
		'control_field' => 1,
		'rules'         => array(
			array(
				'value'     => 'Both Compulsory',
				'field_ids' => array( 2, 3 ),
			),
			array(
				'value'     => 'One Compulsory',
				'field_ids' => array( 2 ),
			),
			array(
				'value'     => 'No Compulsory',
				'field_ids' => array(),
			),
		),
	)
);
