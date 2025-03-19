/**
 * Gravity Perks // Page Transitions // Scroll to top of page instead of top of form
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 *
 * 1. Install this snippet with our free Code Chest plugin.
 *    https://gravitywiz.com/gravity-forms-code-chest/
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
