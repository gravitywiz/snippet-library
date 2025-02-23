/**
 * Gravity Wiz // Gravity Forms // Conditional Readonly Fields
 * https://gravitywiz.com/
 *
 * This simple snippet will mark a field as readonly if a given source field has a specific value.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
// Update "abc" to the conditional value that should be checked for in the source field.
const value = "abc";

// Update "4" to the field to check for the conditional value.
const $sourceField = document.getElementById("input_GFFORMID_4");

// Update "5" to the field that should be marked as readonly if the conditional value is present.
const $readOnlyField = document.getElementById("input_GFFORMID_5");

// Evaluate once when the form is rendered.
setFieldAsReadonlyConditionally();

// Evaluate again each time our source field receives input.
$sourceField.addEventListener("input", function () {
	setFieldAsReadonlyConditionally();
});

function setFieldAsReadonlyConditionally() {
	$readOnlyField.readOnly = $sourceField.value.toLowerCase() === value;
}
