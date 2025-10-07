/**
 * Gravity Wiz // Gravity Forms // Auto Add Next List Row
 * https://gravitywiz.com/
 *
 * Automatically adds a new row to a List field when the user types in the last row.
 *
 * Instruction Video: https://www.loom.com/share/0120ed39296b4e4988c7fdd054af070e
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Adjust the `listSelector` if needed to target a specific List field.
 *
 * 3. That's it — typing in the last row will automatically create the next one.
 */

// Selector for the List field container.
// You can refine this selector to target a specific List field, e.g. `.gfield_list_container input[name="input_6[]"]`
var listSelector = '.gfield_list_container';

/**
 * Bind auto-add behavior to the last row input.
 */
function bindAutoAdd() {
	$( listSelector ).each( function() {
		var $container = $( this );
		var $lastRow = $container.find( '.gfield_list_group' ).last();
		var $input = $lastRow.find( 'input[type="text"]' );

		if ( ! $input.length ) {
			return;
		}

		// Remove old handlers inside this container to avoid duplicates.
		$container.find( 'input[type="text"]' ).off( 'input.autoAdd' );

		// Initialize stored previous value (trimmed).
		$input.data( 'lastVal', ( $input.val() || '' ).trim() );

		// Bind only to the current last input.
		$input.on( 'input.autoAdd', function() {
			var $this = $( this );
			var prev = $this.data( 'lastVal' ) || '';
			var cur = ( $this.val() || '' ).trim();

			// Trigger add only when it changed from empty → non-empty.
			if ( prev === '' && cur !== '' ) {
				var $addButton = $this.closest( '.gfield_list_group' ).find( '.add_list_item' );
				if ( $addButton.length ) {
					$addButton.trigger( 'click' );
				}
			}

			// Update stored value.
			$this.data( 'lastVal', cur );
		} );
	} );
}

/**
 * Initial binding.
 */
bindAutoAdd();

/**
 * Rebind after manual "Add" button click.
 */
$( document ).on( 'click', '.add_list_item', function() {
	setTimeout( bindAutoAdd, 50 );
} );
