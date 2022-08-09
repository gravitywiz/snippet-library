/**
 * Gravity Perks // GP Advanced Calculations // Recalculate on checkbox change
 * https://gravitywiz.com/documentation/gravity-forms-advanced-calculations
 *
 * Instructions:
 *
 * 1. Install this snippet with our free Custom JavaScript plugin.
 *    https://gravitywiz.com/gravity-forms-custom-javascript/
 */
$( '#gform_GFFORMID input:checkbox' ).on( 'change', function () {
	var _GFCalc = rgars( window, 'gf_global/gfcalc/{0}'.format( GFFORMID ) );
	_GFCalc.runCalcs( formId, _GFCalc.formulaFields );
} );
