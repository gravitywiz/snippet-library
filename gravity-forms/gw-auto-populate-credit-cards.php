<?php
/**
 * Gravity Wiz // Gravity Forms // Auto-populate Credit Card Fields with Test Data
 *
 * Frustrated with entering sandbox credit card data into your Gravity Forms credit card fields? This snippet intelligently
 * populates gateway-specific testing credit card data into your credit fields. The payment gateway is determined by the first
 * payment gateway feed found configured for the current form.
 *
 * @version 1.2
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    http://gravitywiz.com/auto-populate-gravity-form-credit-card-fields-for-testing/
 */
class GWPopulateCreditCardField {

    var $cards = array(
        'GFAuthorizeNet' => array(
            'card' => array(
                'card_number'   => '4012888818888',
                'security_code' => '900'
            )
        ),
        'GFPayPalPaymentsPro' => array(
            'card' => array(
                'card_number' => '4012888888881881'
            )
        )
    );
    var $card = array();
    var $value_filter_prefix = 'gform_field_value_gwpcc_';

    function __construct() {

        // add default card in constructor to support dynamic exp year
        $this->cards['default'] = array(
            'card' => array(
                'card_number'     => '4242424242424242',
                'security_code'   => '123',
                'expiration_date' => array( 1, date( 'Y', strtotime( '+1 year' ) ) ),
                'cardholder_name' => 'Bilbo Baggins'
            )
        );

        add_filter( 'gform_pre_render', array( $this, 'add_dynamic_population_parameters' ) );

        foreach( $this->get_card_input_map() as $input_type ) {
            add_filter( $this->value_filter_prefix . $input_type, array( $this, 'populate_card_data' ), 10, 2 );
        }

    }

    function add_dynamic_population_parameters( $form ) {

        $cc_fields = GFCommon::get_fields_by_type( $form, array( 'creditcard' ) );
        if( empty( $cc_fields ) )
            return $form;

        $this->card[$form['id']] = $this->get_card( $form['id'] );

        foreach( $form['fields'] as &$field ) {

            if( $field['type'] != 'creditcard' )
                continue;

            $field['allowsPrepopulate'] = true;

            $inputs = is_object( $field ) ? $field->inputs : $field['inputs'];
            foreach( $inputs as &$input ) {
                $input['name'] = 'gwpcc_' . $this->get_dynamic_parameter( $input['id'] );
            }

            if( is_object( $field ) ) {
                $field->inputs = $inputs;
            } else {
                $field['inputs'] = $inputs;
            }

        }

        return $form;
    }

    function get_card( $form ) {

        foreach( $this->cards as $class => $card ) {

            if( ! is_callable( array( $class, 'get_instance' ) ) ) {
                continue;
            }

            $instance = $class::get_instance();
            if( ! $instance ) {
                continue;
            }

            $has_feed = $instance->get_single_submission_feed_by_form( $form, array() );

            if( $has_feed ) {
                return array_merge( $this->cards['default']['card'], $card['card'] );
            }

        }

        return $this->cards['default']['card'];
    }

    function get_dynamic_parameter( $input_id ) {
        list( $field_id, $input_id ) = array_pad( explode( '.', $input_id ), 2, null );
        return rgar( $this->get_card_input_map(), $input_id );
    }

    function get_card_input_map() {
        return array(
            '1'       => 'card_number',
            '2'       => 'expiration_date',
            '2_month' => 'expiration_date',
            '2_year'  => 'expiration_date',
            '3'       => 'security_code',
            '5'       => 'cardholder_name'
        );
    }

    function populate_card_data( $value, $field ) {

        $form_id = rgar( $field, 'formId' );
        $current_filter = current_filter();
        $input_type = str_replace( $this->value_filter_prefix, '', $current_filter );

        return rgar( $this->card[$form_id], $input_type );
    }

}

new GWPopulateCreditCardField();