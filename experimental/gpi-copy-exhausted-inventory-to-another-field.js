/**
 * Gravity Perks // Inventory // Copy Exhausted Choices to Another Field
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 * 2. Configure the snippet per the inline instructions.
 */
// Update "1" to the ID of your Inventory-enabled field.
var $disabled = $( '#input_GFFORMID_1 option:disabled' );

// Update "2" to the ID of the field to which exhausted choices should be copied.
$( '#input_GFFORMID_2' ).html( $disabled.clone().prop( 'disabled', false ) );

$disabled.remove();
