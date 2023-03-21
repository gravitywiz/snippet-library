<?php
/**
 * Gravity Perks // Entry Blocks // Lightbox
 * https://gravitywiz.com/documentation/gravity-forms-entry-blocks/
 * 
 * A rough draft lightbox implementation for Entry Blocks!
 * 
 * Instructions:
 * 
 * 1. Install the Multi-file Merge Tag snippet.
 *    https://gravitywiz.com/customizing-multi-file-merge-tag/
 * 
 * 2. Copy and paste this snippet into your theme's functions.php or wherever you include custom PHP.
 *
 * 3. That's it. 😉
 */
add_action( 'wp', function() {
	if ( is_callable( 'gw_multi_file_merge_tag' ) && has_block( 'gp-entry-blocks/entries' ) ) {
		gw_multi_file_merge_tag()->register_settings( array(
			'markup' => array(
				array(
					'file_types' => array( 'jpg', 'jpeg' ),
					'markup' => '<div class="gpeb-image"><a href="{url}" class="gpep-image-link"><img src="{url}" width="100%" /></a></div>'
				)
			)
		) );
		wp_enqueue_script( 'magnific', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.min.js', array( 'jquery' ), '1.1.0', true );
		wp_enqueue_style( 'magnific', 'https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css', array(), '1.1.0' );
		add_action( 'wp_footer', function() {
			?>
			<script>
				jQuery( document ).ready( function( $ ) {
					$( '.gpep-image-link' ).magnificPopup({
						type: 'image',
						gallery: {
							enabled: true
						}
					});
				});
			</script>
			<?php
		} );
	}
} );
