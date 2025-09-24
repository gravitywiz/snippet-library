<?php
/**
 * Gravity Perks // Page Transitions // Disable Auto Height
 * https://gravitywiz.com/documentation/gravity-forms-page-transitions/
 *
 * This snippet disables Swiper's auto height and forces all slides to match the height of the tallest slide.
 */
// Update "123" to your form ID
$target_form_id = 123;

add_filter( "gppt_script_args_{$target_form_id}", function( $args, $form ) {
	$args['transitionSettings']['autoHeight'] = false;
	return $args;
}, 10, 2 );

add_filter( "gform_pre_render_{$target_form_id}", function( $form ) {
	add_action( 'wp_head', 'disable_auto_height_styles' );
	return $form;
});

add_action( 'gform_preview_footer', function( $form_id ) use ( $target_form_id ) {
	if ( $form_id == $target_form_id ) {
		disable_auto_height_styles();
	}
});

function disable_auto_height_styles() {
	?>
	<style>
	.gppt-has-page-transitions .swiper-slide {
		height: auto;
		align-self: stretch;
	}
	</style>
	<?php
}
