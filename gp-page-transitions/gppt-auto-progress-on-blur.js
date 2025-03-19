/**
 * Gravity Perks // Page Transitions // Auto Progress on Blur
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 *
 * Add support for auto-progressing to the next page on blur. Useful for enabling auto-progression
 * on Single Line Text and Name fields after these fields lose focus.
 *
 * Warning! This is generally considered to be a bad UX. Use at your own discretion.
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 *
 * 2. Add the `gppt-auto-progress-on-blur` class to the desired fields' CSS Class Name field setting.
 */
$( '.gppt-auto-progress-on-blur' ).each( function() {
	$( this ).find( ':input:last' ).on( 'blur', function() {
		$( this ).trigger( 'gpptAutoProgress' );
	} );
} );
