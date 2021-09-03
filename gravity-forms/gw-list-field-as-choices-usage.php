<?php
/**
 * Gravity Wiz // Gravity Forms // Use List Field as Choices for Gravity Forms
 * https://gravitywiz.com/use-list-field-choices-gravity-forms/
 */

# Basic Usage
new GW_List_Field_As_Choices( array(
    'form_id' => 1,
    'list_field_id' => 2,
    'choice_field_ids' => 3
) );

# Enable Sorting of Choices Alphanumerically
new GW_List_Field_As_Choices( array(
    'form_id' => 1,
    'list_field_id' => 2,
    'choice_field_ids' => 3,
    'sort' => true
) );

# Populating Multiple Choice Fields
new GW_List_Field_As_Choices( array(
    'form_id' => 384,
    'list_field_id' => 3,
    'choice_field_ids' => array( 6, 7 )
) );

# Customizing the Choice Label and Value
new GW_List_Field_As_Choices( array(
    'form_id' => 384,
    'list_field_id' => 2,
    'choice_field_ids' => array( 4, 5 ),
    'label_template' => '{Name} <span style="color:#999;font-style:italic;">({Age})</span>',
    'value_template' => '{Name}'
) );
