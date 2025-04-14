<?php
/**
 * Gravity Wiz // Gravity Forms // Display HTML Field on Entry Details
 *
 * Save and display HTML field content (including Live Merge Tags and shortcodes) in the entry detail view.
 * Useful for retaining dynamic HTML field output as it appeared when the entry was submitted.
 *
 * Plugin Name:  Display HTML Field on Entry Details
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Save and display HTML field content (including Live Merge Tags and shortcodes) in the entry detail view.
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

		add_filter( 'gform_entry_post_save', array( $this, 'save_html_field_content' ), 10, 1 );
		add_action( 'gform_entry_detail', array( $this, 'display_html_field_content' ), 10, 2 );
	}

	public function is_applicable_form( $form ) {
		$form_id = is_array( $form ) && isset( $form['id'] ) ? $form['id'] : (int) $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

	/**
	 * Save HTML field content to entry meta.
	 *
	 * @param array $entry The entry object.
	 * @return array
	 */
	public function save_html_field_content( $entry ) {

		$form = GFAPI::get_form( rgar( $entry, 'form_id' ) );

		if ( ! $this->is_applicable_form( $form ) ) {
			return $entry;
		}

		foreach ( $form['fields'] as $field ) {
			if ( $field->get_input_type() === 'html' ) {

				// Only process matching field_id if defined.
				if ( ! empty( $this->_args['field_id'] ) && (int) $field->id !== (int) $this->_args['field_id'] ) {
					continue;
				}

				gform_update_meta( $entry['id'], 'html_field_' . $field->id, $field->content );
			}
		}

		return $entry;
	}

	/**
	 * Display HTML content on entry detail page.
	 *
	 * @param array $form  The form object.
	 * @param array $entry The entry object.
	 */
	public function display_html_field_content( $form, $entry ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		foreach ( $form['fields'] as $field ) {

			if ( $field->get_input_type() !== 'html' ) {
				continue;
			}

			// Only process matching field_id if defined.
			if ( ! empty( $this->_args['field_id'] ) && (int) $field->id !== (int) $this->_args['field_id'] ) {
				continue;
			}

			$content = gform_get_meta( $entry['id'], 'html_field_' . $field->id );

			// Process GPPA Live Merge Tags if present.
			if (
				method_exists( 'GP_Populate_Anything_Live_Merge_Tags', 'has_live_merge_tag' )
				&& GP_Populate_Anything_Live_Merge_Tags::get_instance()->has_live_merge_tag( $content )
			) {
				$content = gp_populate_anything()->live_merge_tags->replace_live_merge_tags_static( $content, $form, $entry );
			}

			// Process shortcodes.
			$content = do_shortcode( $content );

			if ( ! empty( $content ) ) {
				printf(
					'<h4>%s</h4><div>%s</div><hr>',
					esc_html( $field->label ),
					wp_kses_post( $content )
				);
			}
		}
	}
}

# Configuration
new GW_Display_HTML_Field_Entry_Detail( array(
	'form_id'  => 846, // Replace with your form ID or leave false for all.
	'field_id' => 4,   // Replace with your HTML field ID or leave false to process all HTML fields.
) );
