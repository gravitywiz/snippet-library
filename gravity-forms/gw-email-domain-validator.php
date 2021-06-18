<?php
/**
 * Gravity Wiz // Gravity Forms // Email Domain Validator
 * https://gravitywiz.com/banlimit-email-domains-for-gravity-form-email-fields/
 *
 * Allows you to exclude a list of invalid domains or include a list of valid domains for your Gravity Form Email fields.
 *
 * Plugin Name:  Gravity Wiz // Gravity Forms // Email Domain Validator
 * Plugin URI:   https://gravitywiz.com/banlimit-email-domains-for-gravity-form-email-fields/
 * Description:  Exclude a list of invalid domains or include a list of valid domains for your Gravity Form Email fields.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com/
 */
class GW_Email_Domain_Validator {

    private $_args;

    function __construct($args) {

        $this->_args = wp_parse_args( $args, array(
            'form_id' => false,
            'field_id' => false,
            'domains' => false,
            'validation_message' => __( 'Sorry, <strong>%s</strong> email accounts are not eligible for this form.' ),
            'mode' => 'ban' // also accepts "limit"
        ) );

        // convert field ID to an array for consistency, it can be passed as an array or a single ID
        if($this->_args['field_id'] && !is_array($this->_args['field_id']))
            $this->_args['field_id'] = array($this->_args['field_id']);

        $form_filter = $this->_args['form_id'] ? "_{$this->_args['form_id']}" : '';

        add_filter("gform_validation{$form_filter}", array($this, 'validate'));

    }

    function validate($validation_result) {

        $form = $validation_result['form'];

        foreach($form['fields'] as &$field) {

            // if this is not an email field, skip
            if(RGFormsModel::get_input_type($field) != 'email')
                continue;

            // if field ID was passed and current field is not in that array, skip
            if($this->_args['field_id'] && !in_array($field['id'], $this->_args['field_id']))
                continue;

            $page_number = GFFormDisplay::get_source_page( $form['id'] );
            if( $page_number > 0 && $field->pageNumber != $page_number ) {
                continue;
            }

            if( GFFormsModel::is_field_hidden( $form, $field, array() ) ) {
            	continue;
            }

            $domain = $this->get_email_domain($field);

            // if domain is valid OR if the email field is empty, skip
            if($this->is_domain_valid($domain) || empty($domain))
                continue;

            $validation_result['is_valid'] = false;
            $field['failed_validation'] = true;
            $field['validation_message'] = sprintf($this->_args['validation_message'], $domain);

        }

        $validation_result['form'] = $form;
        return $validation_result;
    }

    function get_email_domain( $field ) {
        $email = explode( '@', rgpost( "input_{$field['id']}" ) );
        return trim( rgar( $email, 1 ) );
    }

    function is_domain_valid( $domain ) {

        $mode   = $this->_args['mode'];
	    $domain = strtolower( $domain );

        foreach( $this->_args['domains'] as $_domain ) {

	        $_domain = strtolower( $_domain );

            $full_match   = $domain == $_domain;
            $suffix_match = strpos( $_domain, '.' ) === 0 && $this->str_ends_with( $domain, $_domain );
            $has_match    = $full_match || $suffix_match;

            if( $mode == 'ban' && $has_match ) {
                return false;
            } else if( $mode == 'limit' && $has_match ) {
                return true;
            }

        }

        return $mode == 'limit' ? false : true;
    }

    function str_ends_with( $string, $text ) {

        $length      = strlen( $string );
        $text_length = strlen( $text );

        if( $text_length > $length ) {
            return false;
        }

        return substr_compare( $string, $text, $length - $text_length, $text_length ) === 0;
    }

}

class GWEmailDomainControl extends GW_Email_Domain_Validator { }

# Configuration

new GW_Email_Domain_Validator( array(
    'form_id' => 326,
    'field_id' => 1,
    'domains' => array( 'gmail.com', 'hotmail.com', '.co.uk' ),
    'validation_message' => __( 'Oh no! <strong>%s</strong> email accounts are not eligible for this form.' ),
    'mode' => 'limit'
) );
