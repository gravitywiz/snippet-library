<?php
/**
 * Gravity Wiz // Form Notices // Apply Form Notices Globally or to Multiple Forms
 * https://gravitywiz.com/gravity-forms-form-notices/
 *
 * Instruction Video: https://www.loom.com/share/0407ade615a04d1a8c73b837cfdc3f1c
 *
 * Inject a specific Form Notices feed into any form(s) regardless of its assigned form ID.
 * Useful for displaying the same notice across many forms without duplicating feeds.
 *
 * Plugin Name:  Gravity Forms — Global Form Notices Feed
 * Plugin URI:   https://gravitywiz.com/gravity-forms-form-notices/
 * Description:  Inject a specific Form Notices feed into any form(s) regardless of its assigned form ID.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
class GW_Form_Notices_Global_Feed {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'feed_id'  => false,   // Required: the ID of the Form Notices feed to inject.
			'form_ids' => array(), // Optional: array of form IDs. Empty = all forms.
		) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		if ( ! class_exists( 'GF_Form_Notices' ) ) {
			return;
		}

		$feed_id = $this->_args['feed_id'];
		if ( ! $feed_id ) {
			return; // No feed ID provided.
		}

		$feed = GFAPI::get_feed( $feed_id );
		if ( ! $feed || empty( $feed['meta'] ) ) {
			return;
		}

		$target_form_ids = $this->_args['form_ids'];
		if ( empty( $target_form_ids ) ) {
			$forms           = GFAPI::get_forms();
			$target_form_ids = wp_list_pluck( $forms, 'id' );
		}

		// Add the filter for each targeted form.
		foreach ( $target_form_ids as $form_id ) {
			add_filter( 'gform_get_form_filter_' . $form_id, array( $this, 'inject_notice' ), 10, 2 );
		}
	}

	public function inject_notice( $form_html, $form ) {

		$feed_id = $this->_args['feed_id'];
		$feed    = GFAPI::get_feed( $feed_id );

		// If the feed already belongs to this form, skip injection to avoid duplication.
		if ( rgar( $feed, 'form_id' ) == $form['id'] ) {
			return $form_html;
		}

		$gf_form_notices = GF_Form_Notices::get_instance();

		// Enqueue the plugin's frontend CSS.
		wp_enqueue_style(
			'gf-form-notices-frontend',
			plugins_url( 'css/frontend.css', 'gf-form-notices/gf-form-notices.php' ),
			array(),
			defined( 'GF_FORM_NOTICES_VERSION' ) ? GF_FORM_NOTICES_VERSION : null
		);

		$messages = array(
			array(
				'content'                => $gf_form_notices->replace_date_merge_tags(
					$feed['meta']['message'],
					array(
						'start_date' => isset( $feed['meta']['start_date'] ) ? $feed['meta']['start_date'] : '',
						'end_date'   => isset( $feed['meta']['end_date'] ) ? $feed['meta']['end_date'] : '',
					)
				),
				'disable_default_styles' => ! empty( $feed['meta']['disable_default_styles'] ),
				'feed_id'                => $feed['id'],
				'feed_name'              => isset( $feed['meta']['feed_name'] ) ? $feed['meta']['feed_name'] : '',
			),
		);

		$notice_html = $gf_form_notices->format_messages( $messages, $form );

		return $notice_html . $form_html;
	}
}

# Configuration

// Replace 37 with your actual Form Notices feed ID.
new GW_Form_Notices_Global_Feed( array(
	'feed_id' => 37,
	// 'form_ids' => array( 1, 2, 3 ), // Uncomment to target specific forms; empty = all forms.
) );
