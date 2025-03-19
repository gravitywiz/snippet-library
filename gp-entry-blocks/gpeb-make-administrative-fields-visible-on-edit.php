<?php
/**
 * Gravity Perks // Entry Blocks // Make Administrative Fields Visible on Edit
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 *
 * Make administrative fields visible when editing via Entry Blocks. Includes support for Nested Forms.
 */
class GPEB_Editable_Admin_Fields {

	private static $instance;

	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	private function __construct() {

		add_filter( 'gform_pre_render', array( $this, 'set_field_visbility_on_edit' ) );
		add_filter( 'gpnf_init_script_args', array( $this, 'add_gpep_context_for_gpnf_ajax_requests' ) );

	}

	public function set_field_visbility_on_edit( $form ) {

		if ( ! $this->is_edit_entry_context( $form['id'] ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( $field->visibility === 'administrative' ) {
				$field->visibility = 'visible';
			}
		}

		return $form;
	}

	public function add_gpep_context_for_gpnf_ajax_requests( $args ) {
		$payload    = array();
		$block_uuid = $this->get_edit_block_uuid( $args['formId'] );
		if ( $block_uuid ) {
			$payload['uuid']     = $block_uuid;
			$payload['entry_id'] = $this->get_edit_block_entry( $args['formId'] );
			$payload['nonce']    = wp_create_nonce( $this->get_edit_block_nonce_action( $payload['uuid'], $payload['entry_id'] ) );
		}
		$args['ajaxContext']['gpebEditEntry'] = $payload;
		return $args;
	}

	public function is_edit_entry_context( $form_id ) {

		$block_uuid = $this->get_edit_block_uuid( $form_id );
		if ( $block_uuid ) {
			return true;
		}

		if ( ! defined( 'DOING_AJAX' ) ) {
			return false;
		}

		$action = rgpost( 'action' );
		if ( ! in_array( $action, array( 'gpnf_edit_entry', 'gpnf_refresh_markup' ) ) ) {
			return false;
		}

		$payload = rgars( $_REQUEST, 'gpnf_context/gpebEditEntry' );
		if ( ! $payload || ! wp_verify_nonce( $payload['nonce'], $this->get_edit_block_nonce_action( $payload['uuid'], $payload['entry_id'] ) ) ) {
			return false;
		}

		// Additional security not required for adding new child entries.
		if ( rgpost( 'action' ) === 'gpnf_refresh_markup' ) {
			return true;
		}

		$child_entry  = GFAPI::get_entry( gp_nested_forms()->get_posted_entry_id() );
		$parent_entry = GFAPI::get_entry( rgar( $child_entry, 'gpnf_entry_parent' ) );
		if ( $parent_entry['id'] == $payload['entry_id'] ) {
			return true;
		}

		return false;
	}

	public function get_edit_queryer( $form_id ) {
		if ( method_exists( 'GP_Entry_Blocks\GF_Queryer', 'attach_to_current_block' ) ) {
			$gpeb_queryer = GP_Entry_Blocks\GF_Queryer::attach_to_current_block();
			if ( $gpeb_queryer && $gpeb_queryer->is_edit_entry() && $gpeb_queryer->form_id == $form_id ) {
				return $gpeb_queryer;
			}
		}
		return false;
	}

	public function get_edit_block_uuid( $form_id ) {
		$gpeb_queryer = $this->get_edit_queryer( $form_id );
		if ( $gpeb_queryer ) {
			return $gpeb_queryer->block_context['gp-entry-blocks/uuid'];
		}
	}

	public function get_edit_block_entry( $form_id ) {
		$gpeb_queryer = $this->get_edit_queryer( $form_id );
		if ( $gpeb_queryer ) {
			return $gpeb_queryer->entry['id'];
		}
	}

	public function get_edit_block_nonce_action( $block_uuid, $entry_id ) {
		return implode( '/', array( 'gpeb_edit_entry', $block_uuid, $entry_id ) );
	}

}

function gpeb_editable_admin_fields() {
	return GPEB_Editable_Admin_Fields::get_instance();
}

gpeb_editable_admin_fields();
