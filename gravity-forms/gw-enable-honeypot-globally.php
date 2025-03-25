<?php
/**
 * Gravity Wiz // Gravity Forms // Enable Honeypot on All Forms
 * http://gravitywiz.com/
 *
 * Enable Gravity Forms' honeypot functionality on all forms. By default, entries will be sent to spam. Additionally,
 * if users are logged in, the honeypot will not be enabled (unless already enabled on form).
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 * 2. Customize instantiation of class if you'd like to abort submission instead of sending to spam and/or exclude
 *    specific forms.
 */
class GWiz_Global_Honeypot {
    protected $excluded_form_ids = array();

    protected $honeypot_action = true;

    public function __construct( $excluded_form_ids = array(), $honeypot_action = 'spam' ) {
        add_filter( 'gform_form_post_get_meta', array( $this, 'enable_honeypot' ) );

        $this->excluded_form_ids = $excluded_form_ids;
        $this->honeypot_action = $honeypot_action;
    }

    public function is_excluded_form( $form ) {
        return in_array( $form['id'], $this->excluded_form_ids, false );
    }

    public function enable_honeypot( $form ) {
        if ( rgar( $form, 'enableHoneypot' ) ) {
            return $form;
        }

        if (
            is_user_logged_in()
            || ! rgar( $form, 'id' )
            || is_admin()
            || $this->is_excluded_form( $form )
        ) {
            return $form;
        }

        $form['enableHoneypot'] = true;
        $form['honeypotAction'] = $this->honeypot_action;

        return $form;
    }
}

// Initialize the global honeypot with default settings (no form exclusions, action set to 'spam').
new GWiz_Global_Honeypot();

// Advanced usage:
// Excludes specific form IDs and sets the honeypot action to 'abort'.
// new GWiz_Global_Honeypot( array( 1, 2, 3 ), 'abort' );
