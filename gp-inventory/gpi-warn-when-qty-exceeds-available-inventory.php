<?php
/**
 * Gravity Perks // Inventory // Warn When Quantity Exceeds Available Inventory
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Show a warning as soon as the quantity entered is greater than the inventory remaining for the selected
 * choice rather than waiting until the form is submitted.
 *
 * Instructions:
 *    1. Install using instructions here: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *
 *    2. Update the configuration at the top of the snippet accordingly.
 */
add_action( 'gform_register_init_scripts', function( $form ) {

	/**
	 * Configuration
	 */

	// The ID of your form and of the field with choice-based inventory.
	$form_id  = 123;
	$field_id = 4;

	// Supports {requested}, {available} and pluralization such as {item|items}, based on what is available.
	$message = 'You requested {requested} but there {is|are} only {available} {item|items} left.';

	// Also disable the submit button/PayPal Checkout buttons, if the form has them - while the warning is showing.
	$disable_submit = false;

	if ( (int) $form['id'] !== $form_id || ! function_exists( 'gp_inventory_type_choices' ) ) {
		return;
	}

	$field = GFAPI::get_field( $form, $field_id );

	if ( ! $field || ! gp_inventory_type_choices()->is_applicable_field( $field ) ) {
		return;
	}

	$quantity_input_ids = gp_inventory_type_choices()->get_quantity_input_ids( $field );
	$choice_counts      = gp_inventory_type_choices()->get_choice_counts( $form_id, $field );
	$inventory          = array();

	foreach ( $field->choices as $choice ) {
		$limit = gp_inventory_type_choices()->get_choice_inventory_limit( $choice, $field, $form );

		if ( rgblank( $limit ) ) {
			continue;
		}

		$claimed = (int) rgar( $choice_counts, $field->sanitize_entry_value( $choice['value'], $form_id ) );

		$inventory[ (string) $choice['value'] ] = max( (int) $limit - $claimed, 0 );
	}

	// Without a quantity, only one item can be claimed at a time and there is nothing to warn about.
	if ( empty( $quantity_input_ids ) || empty( $inventory ) ) {
		return;
	}

	$args = array(
		'formId'          => $form_id,
		'fieldId'         => (string) $field_id,
		'quantityInputId' => str_replace( '.', '_', $quantity_input_ids[0] ),
		'inventory'       => $inventory,
		'message'         => $message,
		'disableSubmit'   => (bool) $disable_submit,
	);

	ob_start();
	?>

	( function( $, args ) {

		var $field    = $( '#field_' + args.formId + '_' + args.fieldId );
		var $qty      = $( '#input_' + args.formId + '_' + args.quantityInputId );
		var $submit   = $( '#gform_submit_button_' + args.formId + ', #gform_wrapper_' + args.formId + ' .gform_next_button' );
		var $paypal   = $( '#gform_ppcp_smart_payment_buttons_' + args.formId );
		var warningId = 'gpi-warning-' + args.formId + '-' + args.fieldId;
		var namespace = '.gpiWarning' + args.fieldId;

		$( '#' + warningId ).remove();

		var $warning = $( '<div/>', {
			id: warningId,
			'class': 'gpi-inventory-warning gfield_description validation_message gfield_validation_message',
			role: 'alert',
			css: { 'margin-top': '5px' }
		} ).appendTo( $qty.closest( '.gfield' ) );

		function check() {

			var quantity = parseFloat( $qty.val() ) || 0;

			// The lowest inventory amongst the selected choices. Product fields append the price to the
			// choice value (e.g. "Small|10.00"), hence the split.
			var available = Math.min.apply( null, $field.find( 'select, input:checked' ).map( function() {
				var value = String( $( this ).val() );

				return args.inventory[ value ] !== undefined ? args.inventory[ value ] : args.inventory[ value.split( '|' )[ 0 ] ];
			} ).get() );

			var exceeded = quantity > available;

			$warning.html(
				args.message.replace( '{requested}', quantity )
					.replace( '{available}', available )
					.replace( /{(\w+)\|(\w+)}/g, available === 1 ? '$1' : '$2' )
			).toggle( exceeded );

			if ( args.disableSubmit ) {
				$submit.prop( 'disabled', exceeded );

				// PayPal Checkout renders its buttons in an iframe, which cannot be disabled, so block
				// interaction with them instead.
				$paypal.css( { 'pointer-events': exceeded ? 'none' : '', opacity: exceeded ? 0.5 : '' } );
			}
		}

		// Delegated so the warning keeps working after GP Inventory refreshes the field via AJAX.
		$( '#gform_' + args.formId )
			.off( namespace )
			.on( 'change' + namespace + ' input' + namespace, 'select, input', check );

		check();

	} )( jQuery, <?php echo wp_json_encode( $args ); ?> );

	<?php
	GFFormDisplay::add_init_script( $form_id, 'gpi_quantity_warning_' . $field_id, GFFormDisplay::ON_PAGE_RENDER, ob_get_clean() );
}, 5 );
