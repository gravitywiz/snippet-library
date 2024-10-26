<?php
/**
 * Gravity Perks // Inventory // Dynamic Scopes for Combo Inventories
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Instruction Video: https://www.loom.com/share/5e43a48fded04182859569a5b5874422
 *
 * Imagine a form with two Drop Down fields with available time slots. One Time Slot field is for a Standard package.
 * This field has inventory enabled and is scoped to a Date field and a Room field (a Drop Down field with available
 * rooms). If a Standard package is selected, a Room must also be selected.
 *
 * The other Time Slot field is for an Ultimate package. This field is configured identically to the Standard package;
 * however, Ultimate packages should claim inventory for both rooms for a given Date and Time Slot. When an Ultimate
 * package is selected, the Room field is hidden via conditional logic.
 *
 * The goal of this snippet is to ensure that the Ultimate field's claimed inventory applies to the Standard field's
 * inventory such that if the 2pm time slot is claimed for Room A (or Room B) in the Standard field, the 2pm time slot
 * in the Ultimate field is also claimed. Similarly, if the 2pm time slot is claimed in the Ultimate field, the 2pm time
 * slot should be claimed for both Room A and Room B in the Standard field (since the Ultimate package reserves both).
 *
 * This snippet achieves this by altering the query to remove the second scope (in this case, the Room field). The rules
 * for dictating when the Room scope is removed are explained in comments in the snippet below.
 *
 * Instructions:
 *
 * 1. Install the snippet.
 *    https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 * 
 * 2. Update the inline variables with your form and field IDs.
 */
add_filter( 'gpi_query', function( $query, $field ) {

	$form_id           = 1133;
	$standard_field_id = 4;
	$super_field_id    = 6;
	$ultimate_field_id = 9;

	/**
	 * We're querying for the Ultimate field's (an inventory-enabled Radio Button field with time slots) inventory. Lets
	 * ensure that the Standard and Super fields' claimed inventory is included in the Ultimate field's. We do this below
	 * by altering the query to remove the second scope (which is a Drop Down field with available rooms).
	 */
	if ( $field->id === $ultimate_field_id ) {
		$fields_to_alter = array( $standard_field_id, $super_field_id, $ultimate_field_id );
	}
	/**
	 * We're querying for the Standard or Super field's (inventory-enabled Drop Down fields with available rooms) inventory.
	 * Let's ensure that the Ultimate field's claimed inventory is included in the Standard and Super fields'.
	 */
	else if ( in_array( $field->id, array( $standard_field_id, $super_field_id ) ) ) {
		$fields_to_alter = array( $ultimate_field_id );
	} else {
		return $query;
	}

	foreach( $fields_to_alter as $current_field_id ) {
		$query['where'] = preg_replace(
			"/\(e.form_id = {$form_id} AND em.form_id = {$form_id} AND em.meta_key = '{$current_field_id}'\) AND ([a-z0-9_]+)\.meta_value = '(.*?)' AND [a-z0-9_]+\.meta_value = '(.*?)'/",
			"(e.form_id = {$form_id} AND em.form_id = {$form_id} AND em.meta_key = '{$current_field_id}') AND \${1}.meta_value = '\${2}'",
			$query['where']
		);
	}

	return $query;
}, 10, 2 );
