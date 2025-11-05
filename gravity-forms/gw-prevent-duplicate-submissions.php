<?php
/**
 * Gravity Wiz // Gravity Forms // Prevent Duplicate Submissions from Double Clicks
 * https://gravitywiz.com/
 *
 * Disable the submit button after the initial click to prevent double clicks.
 *
 * Plugin Name:  Gravity Forms - Prevent Duplicate Submissions from Double Clicks
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Disable the submit button after the initial click to prevent double clicks.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   https://gravitywiz.com/
 */
add_filter( 'gform_pre_render', 'gw_disable_submit' );
function gw_disable_submit( $form ) {

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return $form;
	}

	?>

	<script type="text/javascript">

		document.addEventListener('DOMContentLoaded', function() {
			if (typeof jQuery !== 'undefined') {
				jQuery( document ).ready( function( $ ) {

				var formId = '<?php echo $form['id']; ?>';

				$( '#gform_submit_button_' + formId ).on( 'click', function( event ) {

					if( hasPendingFileUploads( formId ) ) {
						return;
					}

					var $submitCopy = $( this ).clone();

					$submitCopy
						.attr( 'id', '' )
						.prop( 'disabled', true )
						.attr( 'value', 'Processing...' )
						.insertBefore( $( this ) );

					$( this ).css( { visibility: 'hidden', position: 'absolute', transition: 'all 0s ease 0s' } );

				} );

				function hasPendingFileUploads() {

					if( ! window[ 'gfMultiFileUploader' ] ) {
						return false;
					}

					var pendingUploads = false;

					$.each( gfMultiFileUploader.uploaders, function( i, uploader ) {
						if( uploader.total.queued > 0 ) {
							pendingUploads = true;
							return false;
						}
					} );

					return pendingUploads;
				}

				} );
			}
		});

	</script>

	<?php

	return $form;
}
