/**
 * Gravity Perks // Multi-page Navigation // Enable "Back to Last Page" Button for Progress Bar Navigation
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 *
 * Instruction Video: https://www.loom.com/share/877d576a3e3d46329d132dd634c1ecca
 *
 * This snippet is designed to be used with the Gravity Forms Custom Javascript plugin.
 * https://gravitywiz.com/gravity-forms-code-chest/
 *
 * The "Back to Last Page" button relies on the "Steps" Progress Indicator. Use this snippet to enable it for
 * the "Progress Bar" or "None" Progress Indicator.
 */
var myGPMPN = window['gpmpn_GFFORMID'];

if ( myGPMPN.getCurrentPage() < myGPMPN.getPageProgression() ) {
	myGPMPN.addBackToLastPageButton( myGPMPN.getPageProgression() );
}
