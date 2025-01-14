/**
 * Gravity Perks // Gravity Forms //  Recalculate on checkbox change
 * https://gravitywiz.com/
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
$( '#gform_GFFORMID input:checkbox, #gform_GFFORMID input:radio' ).on( 'change', function (event) {
	var $target = $( event.target );
	if ( !$target.closest( '.gfield-image-choice-wrapper-inner' ).length ) {
		return;
	}

	var _GFCalc = rgars( window, 'gf_global/gfcalc/{0}'.gformFormat( GFFORMID ) );
	_GFCalc.runCalcs( GFFORMID, _GFCalc.formulaFields );
});
