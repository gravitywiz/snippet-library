<?php
/**
 * Gravity Perks // Bookings // Auto-select the Only Available Time Slot
 *
 * When a date is chosen and exactly ONE time slot is available, select it automatically so users
 * don't miss the extra click. If several slots are available, nothing is auto-selected.
 *
 * The default selector is '.gpb-booking-time-picker__slot' (the GP Bookings time slot element).
 *
 * Instructions:
 * 1. Install this snippet: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 * 2. Set 'form_id' in the configuration at the bottom. 'slot_selector' only needs changing if your markup differs.
 *
 * If the snippet doesn't select anything:
 *   Pick a date so slots appear, right-click a time slot > Inspect, and confirm the slot element has the class
 *   'gpb-booking-time-picker__slot'. If it uses a different class, set 'slot_selector' to that class.
 */
class GPB_Auto_Select_Only_Time_Slot {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'       => 0,
			'slot_selector' => '',
		) );

		add_filter( 'gform_pre_render', array( $this, 'add_init_script' ), 10, 1 );
	}

	public function add_init_script( $form ) {

		if ( (int) $form['id'] !== (int) $this->_args['form_id'] || empty( $this->_args['slot_selector'] ) ) {
			return $form;
		}

		$args = array(
			'formId'       => (int) $this->_args['form_id'],
			'slotSelector' => $this->_args['slot_selector'],
		);

		$script = 'new GPBAutoSelectOnlyTimeSlot( ' . wp_json_encode( $args ) . ' );';
		$slug   = 'gpb_auto_select_only_time_slot_' . $args['formId'];

		if ( class_exists( 'GFFormDisplay' ) ) {
			GFFormDisplay::add_init_script( $args['formId'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
		}

		if ( ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>
		<script type="text/javascript">
			window.GPBAutoSelectOnlyTimeSlot = function ( args ) {

				var wrapper = document.getElementById( 'gform_wrapper_' + args.formId );
				if ( ! wrapper || wrapper.getAttribute( 'data-gpb-autoselect' ) ) {
					return;
				}
				wrapper.setAttribute( 'data-gpb-autoselect', '1' );

				function isAvailable( el ) {
					if ( el.disabled || el.getAttribute( 'aria-disabled' ) === 'true' ) {
						return false;
					}
					if ( /\b(disabled|unavailable|booked|full)\b/i.test( el.className ) ) {
						return false;
					}
					// Visible on screen.
					return !! ( el.offsetWidth || el.offsetHeight || el.getClientRects().length );
				}

				function isSelected( el ) {
					return el.checked === true
						|| el.getAttribute( 'aria-pressed' ) === 'true'
						|| el.getAttribute( 'aria-checked' ) === 'true'
						|| el.getAttribute( 'aria-selected' ) === 'true'
						|| /\b(selected|active|checked)\b/i.test( el.className );
				}

				function run() {
					var slots = Array.prototype.slice.call( wrapper.querySelectorAll( args.slotSelector ) );
					var available = slots.filter( isAvailable );

					if ( available.length !== 1 ) {
						return;
					}

					var slot = available[ 0 ];

					// Already selected, or already auto-selected once (do not re-click if the user changes it).
					if ( isSelected( slot ) || slot.getAttribute( 'data-gpb-autoselected' ) ) {
						return;
					}

					slot.setAttribute( 'data-gpb-autoselected', '1' );
					slot.click();
				}

				// Slots are rendered after a date is picked, so watch for changes and debounce.
				var timer;
				new MutationObserver( function () {
					clearTimeout( timer );
					timer = setTimeout( run, 200 );
				} ).observe( wrapper, { childList: true, subtree: true } );

				run();
			};
		</script>
		<?php
	}
}

# Configuration

new GPB_Auto_Select_Only_Time_Slot( array(
	'form_id'       => 123,
	'slot_selector' => '.gpb-booking-time-picker__slot',
) );
