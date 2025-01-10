/**
 * Gravity Perks // GP Advanced Calculations // Recalculate on checkbox change
 * https://gravitywiz.com/documentation/gravity-forms-advanced-calculations
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
 */
$( '#gform_GFFORMID input:checkbox' ).on( 'change', function () {
	var _GFCalc = rgars( window, 'gf_global/gfcalc/{0}'.gformFormat( GFFORMID ) );
	_GFCalc.runCalcs( formId, _GFCalc.formulaFields );
} );
