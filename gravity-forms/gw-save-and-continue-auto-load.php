<?php
/**
 * Gravity Wiz // Gravity Forms // Save & Continue Auto Load
 * https://gravitywiz.com
 *
 * Automatically load previously saved data (via Gravity Forms' Save & Continue functionality) when a logged-in user views a form.
 * This snippet is useful when you want logged in users to continue filling their form without using the Save and Continue link.
 *
 * Plugin Name: Gravity Forms - Save & Continue Auto Load
 * Plugin URI: https://gravitywiz.com
 * Description: Automatically load previously saved data (via Gravity Forms' Save & Continue functionality) when a logged-in user views a form.
 * Author: Gravity Wiz
 * Version: 0.4
 * Author URI: https://gravitywiz.com
 */
class GW_Save_Continue_Auto_Load {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_ids'                   => array(),
			'enable_inline_confirmation' => false,
			'auto_save'                  => true,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// load token on load
		add_action( 'gform_form_args', array( $this, 'maybe_resume' ) );

		// save token on submission - or - delete token on successful submission
		add_action( 'gform_post_process', array( $this, 'handle_token_storage' ), 10, 2 );

		// display inline confirmation
		add_filter( 'gform_form_args', array( $this, 'maybe_display_inline_confirmation' ), 10, 2 );

	}

	public function maybe_resume( $args ) {

		$is_submitting = rgpost( "is_submit_{$args['form_id']}" );
		if( ! $this->is_applicable_form( $args['form_id'] ) || ! is_user_logged_in() || $is_submitting ) {
			return $args;
		}

		$token = $this->get_resume_token( get_current_user_id(), $args['form_id'] );
		if( empty( $token ) ) {
			return $args;
		}

		// A bit of a hack... but it makes things much cleaner/easier to just let GF handle loading the data.
		$_GET['gf_token'] = $token;

		return $args;
	}

	public function handle_token_storage( $form, $page_number ) {

		if( ! is_user_logged_in() ) {
			return;
		}

		$submission = GFFormDisplay::$submission[ $form['id'] ];
		$is_successful_submission = $page_number == 0 && $submission['is_valid'];

		if( $is_successful_submission ) {
			$this->delete_resume_token( get_current_user_id(), $form['id'] );
		} else if( rgar( $submission, 'saved_for_later' ) ) {
			$this->save_resume_token( get_current_user_id(), $form['id'], $submission['resume_token'] );
		} else if( $this->_args['auto_save'] ) {
			$resume_token = $this->auto_save( $form, $page_number );
			$this->save_resume_token( get_current_user_id(), $form['id'], $resume_token );
		}

	}

	public function auto_save( $form, $page_number ) {

		$entry          = GFFormsModel::get_current_lead();
		$form_unique_id = GFFormsModel::get_form_unique_id( $form['id'] );
		$ip             = rgars( $form, 'personalData/preventIP' ) ? '' : GFFormsModel::get_ip();
		$source_url     = GFFormsModel::get_current_page_url();
		$source_url     = esc_url_raw( $source_url );
		$resume_token   = GFFormsModel::save_draft_submission( $form, $entry, rgpost( 'gform_field_values' ), $page_number, rgar( GFFormsModel::$uploaded_files, $form['id'] ), $form_unique_id, $ip, $source_url, sanitize_key( rgpost( 'gform_resume_token' ) ) );

		return $resume_token;
	}

	public function save_resume_token( $user_id, $form_id, $token ) {
		return update_user_meta( $user_id, sprintf( '_gform-resume-token-%d', $form_id ), $token );
	}

	public function get_resume_token( $user_id, $form_id ) {
		return get_user_meta( $user_id, sprintf( '_gform-resume-token-%d', $form_id ), true );
	}

	public function delete_resume_token( $user_id, $form_id ) {
		return delete_user_meta( $user_id, sprintf( '_gform-resume-token-%d', $form_id ) );
	}

	public function maybe_display_inline_confirmation( $args ) {
		if( $this->_args['enable_inline_confirmation'] && rgars( GFFormDisplay::$submission, "{$args['form_id']}/saved_for_later" ) ) {
			unset( GFFormDisplay::$submission[ $args['form_id'] ] );
			add_filter( 'gform_get_form_filter_' . $args['form_id'], array( $this, 'prepend_inline_confirmation' ), 10, 2 );
		}
		return $args;
	}

	public function prepend_inline_confirmation( $markup, $form ) {
		return $this->get_confirmation_message( $form ) . $markup;
	}

	public function get_confirmation_message( $form ) {

		$confirmation = wp_filter_object_list( $form['confirmations'], array( 'event' => 'form_saved' ) );
		$confirmation = reset( $confirmation );
		$resume_token = $this->get_resume_token( get_current_user_id(), $form['id'] );

		$message = GFCommon::maybe_sanitize_confirmation_message( $confirmation['message'] );
		$message = GFFormDisplay::replace_save_variables( $message, $form, $resume_token, null );
		$message = GFCommon::gform_do_shortcode( $message );
		$message = sprintf( "<div class='gf_browser_chrome gform_wrapper'><div class='form_saved_message'><span>%s</span></div></div>", $message );

		return $message;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_ids'] ) || in_array( $form_id, $this->_args['form_ids'] );
	}

}

# Configuration

new GW_Save_Continue_Auto_Load();
