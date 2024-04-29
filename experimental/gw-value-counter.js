/**
 * Gravity Wiz // Gravity Forms // Value Counter
 * https://gravitywiz.com/
 *
 * Count the number of times a given value has been selected in a group of fields and populate that number into a Number field.
 * This snippet is designed to target a Number field and count selected values in Checkbox and Radio Button fields.
 *
 * This snippet works best with our free [GF Custom Javascript](https://gravitywiz.com/gravity-forms-code-chest/) plugin.
 */
// Replace the "1", "2" and "3" with field IDs of fields that should have their selected values counted. If you are using the
var $radios    = jQuery( '#field_GFFORMID_1, #field_GFFORMID_2, #field_GFFORMID_3' );
// Replace "4" with the ID of the Number field in which the count should be populated.
var $target    = jQuery( '#field_GFFORMID_4' );
// Replace "a" with the value you wish to count if selected.
var countValue = 'a';

function gwRecountValues() {
	$target.find( 'input' ).val( $radios.find( 'input:checked[value="' + countValue + '"]' ).length );
}

$radios.on( 'change', function() {
	gwRecountValues();
} );

gwRecountValues();
