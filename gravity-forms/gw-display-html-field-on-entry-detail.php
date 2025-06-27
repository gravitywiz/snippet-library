<?php
/**
 * Gravity Wiz // Gravity Forms // Display HTML Field on Entry Details
 *
 * Displays dynamically rendered HTML field content (with merge tags and shortcodes) on the entry detail and entry list view.
 *
 * Plugin Name:  Display HTML Field on Entry Details
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Display HTML field content (with Live Merge Tags and shortcodes) dynamically in the entry detail view.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GW_Display_HTML_Field_Entry_Detail {

	private $_args = array();

	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args(
			$args,
			array(
				'form_id'  => false,
				'field_id' => false,
			)
		);

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_action( 'gform_entry_detail', array( $this, 'display_html_field_content_entry_detail' ), 10, 2 );
		add_filter( 'gform_get_input_value', array( $this, 'display_html_field_content_entry_list' ), 10, 4 );
	}

	private function is_applicable_form( $form ) {
		$form_id = is_array( $form ) && isset( $form['id'] ) ? $form['id'] : (int) $form;
		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

	private function is_applicable_field( $field ) {
		return $field->get_input_type() === 'html' &&
			( empty( $this->_args['field_id'] ) || (int) $field->id === (int) $this->_args['field_id'] );
	}

	private function process_html_content( $content, $form, $entry ) {
		// Process Live Merge Tags if available.
		if (
			method_exists( 'GP_Populate_Anything_Live_Merge_Tags', 'has_live_merge_tag' ) &&
			GP_Populate_Anything_Live_Merge_Tags::get_instance()->has_live_merge_tag( $content )
		) {
			$content = gp_populate_anything()->live_merge_tags->replace_live_merge_tags_static( $content, $form, $entry );
		}

		// Replace merge tags and shortcodes.
		$content = GFCommon::replace_variables( $content, $form, $entry );
		$content = do_shortcode( $content );

		return ! empty( $content ) ? wp_kses_post( $content ) : '';
	}

	public function display_html_field_content_entry_detail( $form, $entry ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		foreach ( $form['fields'] as $field ) {
			if ( $this->is_applicable_field( $field ) ) {
				$content = $this->process_html_content( $field->content, $form, $entry );

				if ( $content ) {
					printf(
						'<h4>%s</h4><div>%s</div><hr>',
						esc_html( $field->label ),
						$content
					);
				}
			}
		}
	}

	public function display_html_field_content_entry_list( $value, $entry, $field, $input_id ) {
		static $is_running = false;

		if ( $is_running || rgget( 'page' ) !== 'gf_entries' || ! $this->is_applicable_field( $field ) ) {
			return $value;
		}

		$form = GFAPI::get_form( $field->formId );
		if ( ! $this->is_applicable_form( $form ) ) {
			return $value;
		}

		$is_running = true;
		$content    = $this->process_html_content( $field->content, $form, $entry );
		$is_running = false;

		return $content ?: $value;
	}
}

# Configuration
new GW_Display_HTML_Field_Entry_Detail( array(
	'form_id'  => 846, // Replace with your form ID or leave false for all.
	'field_id' => 4,   // Replace with your HTML field ID or leave false to process all HTML fields.
) );
