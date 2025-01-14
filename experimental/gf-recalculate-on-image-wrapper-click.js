/**
 * Gravity Perks // Gravity Forms // Trigger calculations on click of image for Image Choice
 * https://gravitywiz.com/
 *
 * This snippet is needed to work around a bug in Gravity Forms 2.9 where calculations are not
 * re-processed when the actual image of an Image Choice is clicked. It works fine if you click on
 * the label or the checkbox/radio itself.
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
