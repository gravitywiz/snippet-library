<?php
/**
 * Gravity Wiz // Gravity Forms // Give First Validation Error Focus
 * http://gravitywiz.com/
 *
 * Plugin Name:  Gravity Forms First Error Focus
 * Plugin URI:   https://gravitywiz.com/make-gravity-forms-validation-errors-mobile-friendlyer/
 * Description:  Automatically focus (and scroll) to the first field with a validation error.
 * Author:       Gravity Wiz
 * Version:      1.4
 * Author URI:   http://gravitywiz.com/
 */
add_filter( 'gform_pre_render', function ( $form ) {
	add_filter( 'gform_confirmation_anchor_' . $form['id'], '__return_false' );

	if ( ! has_action( 'wp_footer', 'gw_first_error_focus_script' ) ) {
		add_action( 'wp_footer', 'gw_first_error_focus_script' );
		add_action( 'gform_preview_footer', 'gw_first_error_focus_script' );
	}

	return $form;
} );

function gw_first_error_focus_script() {
	?>
	<script type="text/javascript">
		if (window['jQuery']) {
			(function ($) {
				$(document).on('gform_post_render', function () {
					// AJAX-enabled forms will call gform_post_render again when rendering new pages or validation errors.
					// We need to reset our flag so that we can still do our focus action when the form conditional logic
					// has been re-evaluated.
					window['gwfef'] = false;
					var y = 0;
					// For a multiple page form after page 1, we need to render on the gform wrapper
					if ($('.gf_step_current_page') && $('.gf_step_current_page').text() > 1) {
						y = $('.gform_wrapper').offset().top;
					}
					var hasError = gwFirstErrorFocus().length;

					if (!hasError) {
						requestAnimationFrame(function() {
							window.scrollTo(0, y);
						});
					}
				});
				$(document).on('gform_post_conditional_logic', function (event, formId, fields, isInit) {
					if (!window['gwfef'] && fields === null && isInit === true) {
						gwFirstErrorFocus();
						window['gwfef'] = true;
					}
				});

				function gwFirstErrorFocus() {
					var $firstError = $('.gfield.gfield_error:visible:first');
					if ($firstError.length > 0) {
						// Without setTimeout or requestAnimationFrame, window.scroll/window.scrollTo are not taking
						// effect on iOS and Android.
						requestAnimationFrame(function () {
							window.scrollTo(0, $firstError.offset().top);
							$firstError.find('input, select, textarea').eq(0).focus();
						});
					}

					return $firstError;
				}
			})(jQuery);
		}
	</script>
	<?php
}
