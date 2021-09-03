<?php
/**
 * Gravity Wiz // GF User Registration // Skip Registration if Email Exists
 * https://gravitywiz.com/
 *
 * Skip registration if the submitted email addresss, is already registered with a user account. 
 *
 * Plugin Name: Gravity Forms - Skip Registration if Email Exists
 * Plugin URI:  https://gravitywiz.com/
 * Description: If submitted email is already registered, skip registration.
 * Author: Gravity Wiz
 * Version: 0.4
 * Author URI: https://gravitywiz.com/
 */
class GW_GFUR_Skip_Registration_If_Email_Exists {

    private static $instance = null;

    public static function get_instance( $args ) {
        if( null == self::$instance ) {
	        self::$instance = new self( $args );
        }
        return self::$instance;
    }

    private function __construct( $args = array() ) {

	    // set our default arguments, parse against the provided arguments, and store for use throughout the class
	    $this->_args = wp_parse_args( $args, array(
	    	'form_ids' => array(),
		    'exclude_form_ids' => array(),
	    ) );

	    add_filter( 'gform_is_delayed_pre_process_feed', array( $this, 'maybe_delay_feeds' ), 10, 4 );
	    add_action( 'gform_pre_process', array( $this, 'maybe_skip_user_registration_validation' ) );
	    add_action( 'gform_validation', array( $this, 'ignore_email_validation' ) );

    }

	public function maybe_delay_feeds( $is_delayed, $form, $entry, $slug ) {
		if ( $slug == 'gravityformsuserregistration' && $this->is_applicable_form( $form ) ) {
			if( ( $this->does_feed_email_exist( $form, $entry ) || ! $this->is_email_valid( $form, $entry ) ) && ! is_user_logged_in() ) {
				return true;
			}
		}
		return $is_delayed;
	}

	public function maybe_skip_user_registration_validation( $form ) {

		if ( function_exists( 'gf_user_registration' ) && $this->is_applicable_form( $form ) && ! is_user_logged_in() ) {
			remove_filter( 'gform_validation', array( gf_user_registration(), 'validate' ) );
		}

	}

	public function does_feed_email_exist( $form, $entry = false ) {
		$email = $this->get_email_by_feed( $form, $entry );
		return get_user_by( 'email', $email ) !== false;
	}

	public function is_email_valid( $form, $entry = false ) {
		$email = $this->get_email_by_feed( $form, $entry );
		return ! rgblank( $email ) && GFCommon::is_valid_email( $email );
	}

	public function get_email_by_feed( $form, $entry = false ) {

		if( $entry == false ) {
			$entry = GFFormsModel::get_current_lead();
		}

		$feed = gf_user_registration()->get_single_submission_feed( $entry, $form );
		if( empty( $feed ) ) {
			return false;
		}

		$email_field_id = rgars( $feed, 'meta/email' );
		$email = rgar( $entry, $email_field_id );

		return $email;
	}

	public function ignore_email_validation( $validation_result ) {

		// invalid emails should only be ignored for users who are registering; logged in users should not be able to
		// enter an incorrect email
		if( is_user_logged_in() ) {
			return $validation_result;
		}

		$has_validation_error = false;

		foreach( $validation_result['form']['fields'] as &$field ) {

			if( $field['validation_message'] == esc_html__( 'Please enter a valid email address.', 'gravityforms' ) ) {
				$field['failed_validation'] = false;
			}

			if( $field['failed_validation'] ) {
				$has_validation_error = true;
			}

		}

		$validation_result['is_valid'] = ! $has_validation_error;

		return $validation_result;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		if( ! empty( $this->_args['exclude_form_ids'] ) ) {
			return ! in_array( $form_id, $this->_args['exclude_form_ids'] );
		} else if( ! empty( $this->_args['form_ids'] ) ) {
			return in_array( $form_id, $this->_args['form_ids'] );
		}

		return true;
	}


}

function gw_gfur_skip_registration_if_email_exists( $args = array() ) {
    return GW_GFUR_Skip_Registration_If_Email_Exists::get_instance( $args );
}

// Default configuration; applies to all applicable forms automatically.
gw_gfur_skip_registration_if_email_exists();

// Use this to exclude forms from this snippet.
// gw_gfur_skip_registration_if_email_exists( array( 
// 	'exclude_form_ids' => array( 1, 2, 3 )
// ) );
