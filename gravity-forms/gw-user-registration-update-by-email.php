<?php
/**
 * Gravity Wiz // GF User Registration // Update by Email
 * https://gravitywiz.com/
 *
 * Create User Registration feeds that will update the user by the submitted email address, allowing non-logged-in users
 * to be targeted by GFUR update feeds.
 *
 * Plugin Name: Gravity Forms User Registration - Update by Email
 * Plugin URI: https://gravitywiz.com/
 * Description: Create User Registration feeds that will update the user by the submitted email address, allowing non-logged-in users to be targeted by GFUR update feeds.
 * Author: Gravity Wiz
 * Version: 0.7
 * Author URI: https://gravitywiz.com/
 */
class GW_UR_Update_By_Email {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'    => false,
			'field_id'   => false,
			/*
			 * Require that the logged-in user have a specific capability to be able to edit users by email.
			 *
			 * Set to `false` to allow all users (including visitors who are logged out) to edit users by email.
			 * Set to `'read'` (Subscriber role and higher) to allow any logged-in user to edit users by email.
			 *
			 * Security Warning: If setting to `false` or a lesser setting, take care in your form's
			 * User Registration feed to ensure that you are not introducing a security vulnerability on your site
			 * by allowing user's roles to be escalated or admin users to have their passwords changed.
			 *
			 * See https://wordpress.org/support/article/roles-and-capabilities/#capabilities for the capabilities
			 * that are supported by default in WordPress.
			 */
			'capability' => 'edit_users',
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		if ( ! function_exists( 'gf_user_registration' ) ) {
			return;
		}

		add_filter( 'gform_userregistration_feed_settings_fields', array( $this, 'add_setting' ) );

		if ( current_user_can( $this->_args['capability'] ) || $this->_args['capability'] === false ) {
			add_filter( 'gform_get_form_filter', array( $this, 'remove_hide_form_function' ), 10, 2 );
			add_action( 'gform_pre_process', array( $this, 'handle_validation' ) );
			add_filter( 'gform_entry_post_save', array( $this, 'add_created_by_by_email' ), 9, 2 );
			add_filter( 'gform_gravityformsuserregistration_pre_process_feeds', array( $this, 'filter_feeds' ), 10, 3 );
		}

	}

	public function add_setting( $settings ) {

		$settings['additional_settings']['fields'][] = array(
			'name'       => 'updateByEmail',
			'label'      => esc_html__( 'Update by Email' ),
			'type'       => 'checkbox',
			'choices'    => array(
				array(
					'label' => esc_html__( 'Update by submitted email address.' ),
					'value' => 1,
					'name'  => 'updateByEmail',
				),
			),
			'tooltip'    => sprintf( '<h6>%s</h6> %s', esc_html__( 'Update by Email' ), esc_html__( 'Update non-logged-in users by the submitted email address (if matching user is found).' ) ),
			'dependency' => array(
				'field'  => 'feedType',
				'values' => 'update',
			),
		);

		return $settings;
	}

	public function remove_hide_form_function( $string, $form ) {

		if ( $this->has_update_by_email( $form ) ) {
			remove_action( 'gform_get_form_filter_' . $form['id'], array( gf_user_registration(), 'hide_form' ) );
		}

		return $string;
	}

	public function handle_validation( $form ) {

		$feed = $this->get_single_submission_feed( GFFormsModel::get_current_lead(), $form );

		if ( $this->is_update_by_email( $feed ) && $this->does_feed_email_exist( $feed, GFFormsModel::get_current_lead() ) ) {
			remove_filter( 'gform_validation', array( gf_user_registration(), 'validate' ) );
			add_action( 'gform_validation', array( $this, 'validate_role' ) );
		}

		// let's just be safe and reset current lead to false so it doesn't disrupt the normal flow of things...
		//GFFormsModel::set_current_lead( false );

	}

	public function add_created_by_by_email( $entry, $form ) {

		$feed = $this->get_single_submission_feed( $entry, $form );
		if ( ! $feed ) {
			return $entry;
		}

		$user = $this->get_user_by_feed_email( $feed, $entry );
		if ( $user !== false ) {
			$entry['created_by'] = $user->ID;
			GFAPI::update_entry_property( $entry['id'], 'created_by', $user->ID );
		}

		return $entry;
	}

	public function does_feed_email_exist( $feed, $entry ) {
		return $this->get_user_by_feed_email( $feed, $entry ) !== false;
	}

	public function get_user_by_feed_email( $feed, $entry ) {
		$email_field_id = rgars( $feed, 'meta/email' );
		$email          = rgar( $entry, $email_field_id );
		return get_user_by( 'email', $email );
	}

	public function filter_feeds( $feeds, $entry, $form ) {

		$feed = $this->get_single_submission_feed( $entry, $form );
		if ( $feed ) {
			$feeds = array( $feed );
		}

		return $feeds;
	}

	public function has_update_by_email( $form ) {

		$feeds = gf_user_registration()->get_active_feeds( $form['id'] );
		foreach ( $feeds as $feed ) {
			if ( $this->is_update_by_email( $feed ) ) {
				return true;
			}
		}

		return false;
	}

	public function is_update_by_email( $feed ) {
		return rgars( $feed, 'meta/updateByEmail' );
	}

	/**
	 * Prevent users from submitted the form if the Update by Email feed matches their current role.
	 *
	 * @param $result
	 */
	public function validate_role( $result ) {

		$entry = GFFormsModel::get_current_lead();
		$feed  = $this->get_single_submission_feed( $entry, $result['form'] );

		$target_role = rgars( $feed, 'meta/role' );
		$user        = $this->get_user_by_feed_email( $feed, $entry );

		if ( ! in_array( $target_role, $user->roles ) ) {
			return $result;
		}

		$result['is_valid'] = false;
		$email_field_id     = rgars( $feed, 'meta/email' );

		foreach ( $result['form']['fields'] as &$field ) {
			if ( $field->id == $email_field_id ) {
				$field->failed_validation  = true;
				$field->validation_message = 'The account registered with this email has already been upgraded.';
			}
		}

		return $result;
	}

	public function get_single_submission_feed( $entry, $form ) {

		$feeds = gf_user_registration()->get_active_feeds( $form['id'] );

		foreach ( $feeds as $feed ) {

			if ( ! $this->is_update_by_email( $feed ) || ! gf_user_registration()->is_feed_condition_met( $feed, $form, $entry ) ) {
				continue;
			}

			if ( ! $this->is_update_by_email( $feed ) || ! $this->does_feed_email_exist( $feed, $entry ) ) {
				continue;
			}

			return $feed;
		}

		return false;
	}

}

# Configuration

new GW_UR_Update_By_Email();
