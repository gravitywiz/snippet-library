/**
 * Gravity Perks // GP eCommerce Fields // Conditional Logic Performance Enhancer
 * http://gravitywiz.com/documentation/gravity-forms-ecommerce-fields
 *
 * Instructions:
 *     1. Install our free Custom Javascript for Gravity Forms plugin.
 *        Download the plugin here: https://gravitywiz.com/gravity-forms-custom-javascript/
 *     2. Copy and paste the snippet into the editor of the Custom Javascript for Gravity Forms plugin.
 */

/* Debounce Credit: https://davidwalsh.name/javascript-debounce-function */
function gwizDebounce(func, wait, immediate) {
    var timeout;
    return function () {
        var context = this, args = arguments;
        var later = function () {
            timeout = null;
            if (!immediate) func.apply(context, args);
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func.apply(context, args);
    };
}

$(document).off("gform_post_conditional_logic.gfCalc_{0}".format(GFFORMID))

$(document).on("gform_post_conditional_logic.gfCalc_{0}".format(GFFORMID), gwizDebounce(function () {
    var _GFCalc = rgars(window, 'gf_global/gfcalc/{0}'.format(GFFORMID));
    _GFCalc.runCalcs(formId, _GFCalc.formulaFields);
}, 15));
