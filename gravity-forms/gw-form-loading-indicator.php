<?php
/**
 * Gravity Wiz // Gravity Forms // Form Loading Indicator
 * https://gravitywiz.com/
 */
// Update "123" to your form ID or remove "_123" to apply to all forms.
add_action( 'gform_get_form_filter_123', function( $form_string, $form ) {
	ob_start();
	?>
	<style>
		.loader,
		.loader:before,
		.loader:after {
			border-radius: 50%;
			width: 2.5em;
			height: 2.5em;
			-webkit-animation-fill-mode: both;
			animation-fill-mode: both;
			-webkit-animation: load7 1.8s infinite ease-in-out;
			animation: load7 1.8s infinite ease-in-out;
		}
		.loader {
			color: #000000;
			font-size: 10px;
			margin: 80px auto;
			position: relative;
			text-indent: -9999em;
			-webkit-transform: translateZ(0);
			-ms-transform: translateZ(0);
			transform: translateZ(0);
			-webkit-animation-delay: -0.16s;
			animation-delay: -0.16s;
		}
		.loader:before,
		.loader:after {
			content: '';
			position: absolute;
			top: 0;
		}
		.loader:before {
			left: -3.5em;
			-webkit-animation-delay: -0.32s;
			animation-delay: -0.32s;
		}
		.loader:after {
			left: 3.5em;
		}
		@-webkit-keyframes load7 {
			0%,
			80%,
			100% {
				box-shadow: 0 2.5em 0 -1.3em;
			}
			40% {
				box-shadow: 0 2.5em 0 0;
			}
		}
		@keyframes load7 {
			0%,
			80%,
			100% {
				box-shadow: 0 2.5em 0 -1.3em;
			}
			40% {
				box-shadow: 0 2.5em 0 0;
			}
		}
	</style>
	<div class="loader gf-loader-<?php echo $form['id']; ?>">Loading...</div>
	<script>
		gform.initializeOnLoaded( function() {
			jQuery( document ).on( 'gform_post_render', function( event, formId, currentPage ) {
				if ( formId == <?php echo $form['id']; ?> ) {
					jQuery('.gf-loader-<?php echo $form['id']; ?>').remove();
				}
			} );
		} );
	</script>
	<?php
	return ob_get_clean() . $form_string;
}, 10, 2 );
