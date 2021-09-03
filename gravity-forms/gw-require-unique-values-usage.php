<?php
/**
 * Gravity Wiz // Gravity Forms // Require Unique Values Between Fields
 * http://gravitywiz.com/gravity-forms-require-unique-values-for-different-fields/
 */
 
# Set Custom Validation Message

new GW_Require_Unique_Values( array(
	'form_id' => 12,
	'field_ids' => array( 14, 15 ),
	'validation_message' => 'My custom validation message!'
) );

# Create Multiple Unique "Groups" on the Same Form

new GW_Require_Unique_Values( array(
	'form_id' => 2,
	'field_ids' => array( 4, 5 )
) );

new GW_Require_Unique_Values( array(
	'form_id' => 2,
	'field_ids' => array( 7, 8 )
) );

# Unique Field Compared to ALL Form Fields

new GW_Require_Unique_Values( array(
	'form_id' => 2,
	'field_ids' => array( 7 )
) );
