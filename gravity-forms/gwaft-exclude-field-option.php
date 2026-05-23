<?php
/**
 * Gravity Wiz // Gravity Forms All Fields Template // Exclude Field from All Fields Template
 * https://gravitywiz.com/gravity-forms-all-fields-template/
 *
 * Adds a checkbox to each field's Advanced settings allowing you to exclude that specific field from the {all_fields}
 * merge tag output when using the All Fields Template plugin.
 *
 * Plugin Name:  GF All Fields Template — Exclude Field
 * Plugin URI:   https://gravitywiz.com/gravity-forms-all-fields-template/
 * Description:  Adds a checkbox to each field's Advanced settings allowing you to exclude that specific field from the {all_fields} merge tag output when using the All Fields Template plugin.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GWAFT_Exclude_Field {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		// Add the checkbox to the field settings
		add_action( 'gform_field_advanced_settings', array( $this, 'add_exclude_checkbox' ), 10, 2 );

		// Register the setting and sync UI
		add_action( 'gform_editor_js', array( $this, 'register_setting_js' ) );

		// Filter the {all_fields} merge tag
		add_filter( 'gform_merge_tag_filter', array( $this, 'filter_all_fields_merge_tag' ), 5, 6 );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

	public function is_applicable_field( $field ) {

		if ( ! empty( $this->_args['field_id'] ) ) {
			if ( is_array( $this->_args['field_id'] ) ) {
				return in_array( $field->id, $this->_args['field_id'] );
			} else {
				return (int) $field->id === (int) $this->_args['field_id'];
			}
		}

		return true;
	}

	/**
	 * Add checkbox to the Advanced Settings tab.
	 *
	 * @param int $position The position of the settings tab.
	 * @param int $form_id  The ID of the current form.
	 */
	public function add_exclude_checkbox( $position, $form_id ) {

		// Position 50 is a common slot in the Advanced tab.
		if ( (int) $position !== 50 ) {
			return;
		}

		?>
		<li class="gwaft-exclude-from-all-fields-template field_setting">
			<input
				type="checkbox"
				id="gwaft_exclude_from_all_fields_template"
				onclick="SetFieldProperty('gwaftExcludeFromAllFieldsTemplate', this.checked);"
			/>
			<label for="gwaft_exclude_from_all_fields_template" class="inline">
				<?php echo esc_html__( 'Exclude this field from All Fields Template', 'gw-aft-exclude-field' ); ?>
			</label>
		</li>
		<?php
	}

	/**
	 * Register the setting so GF shows/saves it for all field types and sync UI on load.
	 */
	public function register_setting_js() {
		?>
		<script type="text/javascript">
			(function($) {

				// Make GF aware of our setting for all field types.
				if (window.fieldSettings) {
					for (var type in fieldSettings) {
						if (!Object.prototype.hasOwnProperty.call(fieldSettings, type)) {
							continue;
						}
						if (fieldSettings[type].indexOf('.gwaft-exclude-from-all-fields-template') === -1) {
							fieldSettings[type] += ', .gwaft-exclude-from-all-fields-template';
						}
					}
				}

				// Populate the checkbox when loading a field's settings panel.
				$(document).on('gform_load_field_settings', function(event, field) {
					$('#gwaft_exclude_from_all_fields_template').prop('checked', !!(field.gwaftExcludeFromAllFieldsTemplate));
				});

			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Honor the setting when rendering {all_fields}.
	 *
	 * @param mixed  $value     The value to be filtered.
	 * @param string $merge_tag The merge tag being processed.
	 * @param string $modifiers The modifiers for the merge tag.
	 * @param object $field     The current field object.
	 * @param mixed  $raw_value The raw field value.
	 * @param string $format    The format of the output (html or text).
	 *
	 * @return mixed|false Returns false to exclude the field, otherwise the original value.
	 */
	public function filter_all_fields_merge_tag( $value, $merge_tag, $modifiers, $field, $raw_value, $format ) {

		// Only target the all_fields merge tag and ensure we have a field object
		if ( $merge_tag !== 'all_fields' || ! is_object( $field ) ) {
			return $value;
		}

		// Check if this field should be excluded
		if ( ! empty( $field->gwaftExcludeFromAllFieldsTemplate ) && $this->is_applicable_field( $field ) ) {
			return false;
		}

		return $value;
	}

}

# Configuration

new GWAFT_Exclude_Field();

// Optional: Limit to specific forms and/or fields
// new GWAFT_Exclude_Field( array(
//     'form_id'  => 123,
//     'field_id' => 4,
// ) );
//
// new GWAFT_Exclude_Field( array(
//     'form_id'  => 123,
//     'field_id' => array( 5, 6, 7 ),
// ) );
