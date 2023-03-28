/**
 * Gravity Perks // Page Transitions // Scroll to top of page instead of top of form
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 *
 * If using Gravity Forms Custom Javascript, you will also need to install
 * the follow snippet: https://github.com/gravitywiz/snippet-library/blob/master/experimental/gfjs-early-init-scripts.php
 */
gform.addFilter('gppt_swiper_options', function (options) {
    options.on.slideChange = function () {
        window.scroll({
            top: 0,
            left: 0,
            behavior: 'smooth'
        });
    }
	
    return options;
});
