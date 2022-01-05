<?php
/**
 * Gravity Wiz // Gravity Forms User Registration // Update & Create Feeds on Same Form
 * https://gravitywiz.com/
 *
 * Allows the creation (and processing) of both "update" and "create" feeds on the same form with the Gravity Forms User Registration add-on.
 * Requires feeds to be configured with conditional logic based on a User Logged In field on the form.
 *
 * USE WITH CAUTION! If you are not handling which feed should be used via custom code or conditional logic, you will receive errors.
 *
 * Plugin Name: Gravity Forms User Registration - Update & Create Feeds on Same Form
 * Plugin URI: https://gravitywiz.com/
 * Description: Allows the creation (and processing) of both "update" and "create" feeds on the same form with the Gravity Forms User Registration add-on.
 * Author: Gravity Wiz
 * Version: 0.2
 * Author URI: https://gravitywiz.com/
 */
class GW_GFUR_Update_Create_Same_Form {

    public function __construct( $args = array() ) {

        // set our default arguments, parse against the provided arguments, and store for use throughout the class
        $this->_args = wp_parse_args( $args, array(
            'form_ids'         => array(),
            'exclude_form_ids' => array()
        ) );

        // do version check in the init to make sure if GF is going to be loaded, it is already loaded
        add_action( 'init', array( $this, 'init' ) );

    }

    public function init() {

        // make sure we're running the required minimum version of Gravity Forms
        if( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
            return;
        }

        // make sure User Registration is loaded
	    if( ! is_callable( 'gf_user_registration' ) || ! is_callable( array( gf_user_registration(), 'get_single_submission_feed' ) ) ) {
		    return;
	    }

	    add_filter( 'gform_userregistration_feed_settings_fields', array( $this, 'enable_both_feed_types' ) );
	    add_filter( 'gform_get_form_filter', array( $this, 'remove_hide_form_function' ), 10, 2 );

    }

	public function enable_both_feed_types( $fields ) {

		foreach( $fields as &$group ) {
			if( rgar( $group, 'title' ) == esc_html__( 'Feed Settings', 'gravityformsuserregistration' ) ) {
				foreach( $group['fields'] as &$field ) {
					if( rgar( $field, 'name' ) == 'feedType' ) {
						unset( $field['choices']['create']['disabled'] );
						unset( $field['choices']['update']['disabled'] );
					}
				}
			}
		}

		return $fields;
	}

    public function remove_hide_form_function( $string, $form ) {

    	// if we have both feed types and the user is not logged in, make sure we don't hide the form
        if( $this->is_applicable_form( $form ) && ! is_user_logged_in() && $this->has_both_feed_types( $form ) ) {
            remove_action( 'gform_get_form_filter_' . $form['id'], array( gf_user_registration(), 'hide_form' ) );
        }

        return $string;
    }

    public function has_both_feed_types( $form ) {

    	$feeds = gf_user_registration()->get_feeds( $form['id'] );

	    $has_create_feed = false;
	    $has_update_feed = false;

	    foreach( $feeds as $feed ) {

	    	if( ! $feed['is_active'] ) {
	    		continue;
		    }

	    	if( $feed['meta']['feedType'] == 'create' ) {
	    		$has_create_feed = true;
		    } else if( $feed['meta']['feedType'] == 'update' ) {
			    $has_update_feed = true;
		    }

	    }

	    return $has_create_feed && $has_update_feed;
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

# Configuration

new GW_GFUR_Update_Create_Same_Form( array(
	'exclude_form_ids' => array( '1426' ),
) );
