<?php
/**
 * Gravity Perks // Bookings // Limit Quantity to Spots Left
 * https://gravitywiz.com/documentation/gravity-forms-bookings/
 *
 * Once a time slot is chosen, disable the Quantity choices that are larger than the
 * spots left in that slot. For example, with choices 1 to 4 and 3 spots left, 4 is
 * disabled. A Number quantity input gets a matching maximum instead.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the steps here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
class GPB_Limit_Quantity_To_Spots_Left {

	public function __construct() {
		add_action( 'gform_register_init_scripts', array( $this, 'register_init_script' ) );
	}

	public function register_init_script( $form ) {
		$fields = array();

		foreach ( GFAPI::get_fields_by_type( $form, 'gpb_booking_time' ) as $field ) {
			if ( empty( $field->gpbBookingField ) ) {
				continue;
			}

			$quantity_fields = GFCommon::get_product_fields_by_type( $form, array( 'quantity' ), $field->gpbBookingField );
			if ( $quantity_fields ) {
				$fields[ (int) $field->id ] = (int) $quantity_fields[0]->id;
			}
		}

		if ( empty( $fields ) ) {
			return;
		}

		$args = array(
			'formId'  => (int) $form['id'],
			'fields'  => $fields,
			'restUrl' => rest_url( 'gp-bookings/v1/availability/range' ),
		);

		$script = '( ' . $this->get_script() . ' )( ' . wp_json_encode( $args ) . ' );';

		GFFormDisplay::add_init_script( $form['id'], 'gpb_limit_quantity_to_spots_left', GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	private function get_script() {
		return <<<'JS'
function ( args ) {
	var $ = window.jQuery;

	$.each( args.fields, function ( timeFieldId, quantityFieldId ) {
		var booking   = window.gpBookings && window.gpBookings[ args.formId + '_' + timeFieldId ];
		var $time     = $( '#input_' + args.formId + '_' + timeFieldId );
		var $quantity = $( '#input_' + args.formId + '_' + quantityFieldId ).not( '[type="hidden"]' );
		var lastKey   = null;

		if ( ! booking || ! booking.store ) {
			return;
		}

		var ownMax = $quantity.data( 'gpbOwnMax' );
		if ( ownMax === undefined ) {
			ownMax = parseFloat( $quantity.attr( 'max' ) ) || Infinity;
			$quantity.data( 'gpbOwnMax', ownMax );
		}

		function update() {
			var state = booking.store.getState();
			var data  = {
				serviceId: state.currentServiceId,
				start: $time.val(),
				end: $time.val(),
				resources: state.selectedResourceIds,
				resourceMode: state.resourceMode
			};

			if ( booking.excludeBookingId ) {
				data.excludeBookingId    = booking.excludeBookingId;
				data.excludeBookingToken = booking.excludeBookingToken;
			}

			var key = JSON.stringify( data );
			if ( key === lastKey ) {
				return;
			}
			lastKey = key;

			if ( ! data.start || ! data.serviceId ) {
				limitQuantity( -1 );
				return;
			}

			$.getJSON( args.restUrl, data ).done( function ( reply ) {
				if ( key === lastKey ) {
					limitQuantity( reply.remainingSlots );
				}
			} );
		}

		function limitQuantity( remaining ) {
			var max = remaining < 0 ? Infinity : remaining;

			if ( $quantity.is( 'select' ) ) {
				$quantity.find( 'option' ).prop( 'disabled', function () {
					return parseInt( this.value, 10 ) > max;
				} );

				if ( $quantity.find( 'option:selected' ).prop( 'disabled' ) ) {
					$quantity.val( $quantity.find( 'option:enabled' ).last().val() ).trigger( 'change' );
				}

				$quantity.trigger( 'chosen:updated' );
				return;
			}

			var limit = Math.min( ownMax, max );

			$quantity.attr( 'max', limit === Infinity ? null : limit );

			if ( parseFloat( $quantity.val() ) > limit ) {
				$quantity.val( limit ).trigger( 'change' );
			}
		}

		$time.off( '.gpbLimitQty' ).on( 'change.gpbLimitQty', update );
		booking.store.subscribe( update );
		update();
	} );
}
JS;
	}
}

new GPB_Limit_Quantity_To_Spots_Left();
