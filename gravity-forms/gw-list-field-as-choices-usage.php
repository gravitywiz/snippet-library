<?php
/**
 * Gravity Wiz // Gravity Forms // Use List Field as Choices for Gravity Forms
 * https://gravitywiz.com/use-list-field-choices-gravity-forms/
 */

# Basic Usage
new GW_List_Field_As_Choices( array(
	'form_id'          => 1,
	'list_field_id'    => 2,
	'choice_field_ids' => 3,
) );

# Enable Sorting of Choices Alphanumerically
new GW_List_Field_As_Choices( array(
	'form_id'          => 1,
	'list_field_id'    => 2,
	'choice_field_ids' => 3,
	'sort'             => true,
) );

# Populating Multiple Choice Fields
new GW_List_Field_As_Choices( array(
	'form_id'          => 384,
	'list_field_id'    => 3,
	'choice_field_ids' => array( 6, 7 ),
) );

# Customizing the Choice Label and Value
new GW_List_Field_As_Choices( array(
	'form_id'          => 384,
	'list_field_id'    => 2,
	'choice_field_ids' => array( 4, 5 ),
	'label_template'   => '{Name} <span style="color:#999;font-style:italic;">({Age})</span>',
	'value_template'   => '{Name}',
) );


/** Usage of filters in the snippet */

//Customization values based on a user input step from Gravity Flow - The entry list field has already been populated.
/*
add_filter( 'gplibrary_list_field_choices', 'example_flow_list_choice_populate', 10, 3 );
function example_flow_list_choice_populate( $values, $form, $args) {
    if ( is_array( $values ) ) {
		return $values;
	}

	//Confirm we are within a Gravity Flow Inbox
    if( rgget( 'lid' ) && rgget( 'page') == 'gravityflow-inbox' ) {
        $entry = GFAPI::get_entry( (int)rgget('lid') );
        //Verify the entry list field has previously stored values to use.
        if ( $entry ) {
            $values = unserialize( $entry[ $args['list_field_id'] ] );
            if ( ! is_array( $values ) ) {
                return false;
            } else {
                return $values;
            }
        }
    }
	return false;
}
*/