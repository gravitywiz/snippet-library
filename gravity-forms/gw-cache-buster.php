<?php
/**
 * Gravity Wiz // Gravity Forms // Cache Buster
 *
 * Bypass your website cache when loading a Gravity Forms form.
 *
 * Plugin Name: Gravity Forms Cache Buster
 * Plugin URI:  https://gravitywiz.com/
 * Description: Bypass your website cache when loading a Gravity Forms form.
 * Author:      Gravity Wiz
 * Version:     0.13
 * Author URI:  https://gravitywiz.com
 */
class GW_Cache_Buster {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args(
			$args,
			array(
				'form_id'  => false,
				'field_id' => false,
			)
		);

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		add_filter( 'gform_shortcode_form', array( $this, 'shortcode' ), 10, 3 );
		add_filter( 'gform_save_and_continue_resume_url', array( $this, 'filter_resume_link' ), 15, 4 );

		add_action( 'wp_ajax_nopriv_gfcb_get_form', array( $this, 'ajax_get_form' ) );
		add_action( 'wp_ajax_gfcb_get_form', array( $this, 'ajax_get_form' ) );

	}

	public function shortcode( $markup, $attributes, $content ) {

		$atts = shortcode_atts(
			array(
				'id'          => 0,
				'cachebuster' => false,
			),
			$attributes
		);

		$form_id = $atts['id'];

		add_filter( "gform_footer_init_scripts_filter_{$form_id}", array( $this, 'suppress_default_post_render_event' ), 10, 3 );

		if ( ! $this->is_cache_busting_applicable() ) {
			return $markup;
		}

		$is_enabled = rgar( $atts, 'cachebuster' ) || gf_apply_filters( array( 'gfcb_enable_cache_buster', $form_id ), false );
		if ( ! $is_enabled ) {
			return $markup;
		}

		ob_start();
		?>

		<div id="gf-cache-buster-form-container-<?php echo $form_id; ?>" class="gf-cache-buster-form-container">
			<div class="loader"></div>
			<style type="text/css">
				.gf-cache-buster-form-container { }
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
					color: rgba( 0, 0, 0, 0.5 );
					font-size: 10px;
					margin: 0 auto 80px;
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
		</div>
		<?php
		// Store current URL parameters and include them in AJAX call
		// This preserves dynamic form population
		$params         = array();
		$exclude_params = array( 'action', 'form_id', 'atts' ); // Exclude parameters that may clash
		foreach ( $_GET as $k => $v ) {
			if ( ! in_array( $k, $exclude_params, true ) ) {
				$params[ $k ] = sprintf( '%s=%s', $k, $_GET[ $k ] );
			}
		}
		$params = ( count( $params ) > 0 ) ? '&' . join( '&', $params ) : '';
		if ( class_exists( 'Gravity_Forms_Multilingual' ) ) {
			global $sitepress;
			$lang = $sitepress->get_current_language();
		}
		?>
		<script type="text/javascript">
			( function ( $ ) {
				var formId = '<?php echo $form_id; ?>';
				$.post( '<?php echo admin_url( 'admin-ajax.php' ); ?>?action=gfcb_get_form&form_id=<?php echo $form_id, $params; ?>', {
					action: 'gfcb_get_form',
					form_id: '<?php echo $form_id; ?>',
					atts: '<?php echo json_encode( $attributes ); ?>',
					lang: '<?php echo $lang; ?>'
				}, function( response ) {
					$( '#gf-cache-buster-form-container-<?php echo $form_id; ?>' ).html( response ).fadeIn();
					if( window['gformInitDatepicker'] ) {
						gformInitDatepicker();
					}
					// Initialize GPPA
					// @todo Since we are not triggering the `gform_post_render` below, I'm not certain that we need this.
					if( response.indexOf('GPPA') > -1 ) {
						window.gform.doAction('gppa_register_form', formId);
					}
					// We probably don't need this since everything else should already be loaded by this point but since
					// GF is using it as their standard for triggering the `gform_post_render` event, I figured we should follow suit.
					gform.initializeOnLoaded( function() {
						// Form has been rendered. Trigger post render to initialize scripts.
						jQuery( document ).trigger( 'gform_post_render', [ formId, 1 ] );
					} );
				} );
			} ( jQuery ) );
		</script>

		<?php
		return ob_get_clean();
	}

	public function is_cache_busting_applicable() {
		// POSTED and LOGGED-IN requests are not typically cached
		return empty( $_POST ) || ! is_user_logged_in();
	}

	public function suppress_default_post_render_event( $form_string, $form, $current_page ) {

		$footer_script_body = "gform.initializeOnLoaded( function() { jQuery(document).trigger('gform_post_render', [{$form['id']}, {$current_page}]) } );";
		$search             = GFCommon::get_inline_script_tag( $footer_script_body );

		$form_string = str_replace( $search, '', $form_string );

		return $form_string;
	}

	public function ajax_get_form() {

		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		if ( ! $form_id ) {
			$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		}

		/**
		 * Init scripts are output to the footer by default so they are not needed in the AJAX response. Some plugins
		 * deliberately output inline scripts alongside the form (see Nested Forms' `gpnf_preload_form` filter) but I
		 * haven't encountered a scenario where this is ideal when fetching the form via an AJAX request like we do here.
		 *
		 * Additionally, to support this change, we manually trigger the `gform_post_render` event after we've loaded
		 * the form markup from this AJAX response.
		 *
		 * Priority of this filter is set aggressively high to ensure it will take priority.
		 */
		add_filter( 'gform_init_scripts_footer', '__return_true', 987 );

		$atts = json_decode( rgpost( 'atts' ), true );

		// GF expects an associative array for field values. Parse them before passing it on.
		$field_values = wp_parse_args( rgar( $atts, 'field_values' ) );

		// If `$_POST` is not an empty array GF 2.5 fails to select default values for checkbox fields. See HS#26188
		$_POST = array();

		gravity_form( $form_id, filter_var( rgar( $atts, 'title', true ), FILTER_VALIDATE_BOOLEAN ), filter_var( rgar( $atts, 'description', true ), FILTER_VALIDATE_BOOLEAN ), false, $field_values, true /* default to true; add support for non-ajax in the future */, rgar( $atts, 'tabindex' ) );

		die();
	}

	/**
	 * Since the form is loading admin-ajax.php, GFFormsModel::get_current_page_url() will return the wrong URL when
	 * saving and continuing.
	 *
	 * We need to replace the resume link with the page loading the AJAX form.
	 *
	 * @param string $resume_url The URL to be used to resume the partial entry.
	 * @param array $form The Form Object.
	 * @param string $resume_token The token that is used within the URL.
	 * @param string $email The email address associated with the partial entry.
	 */
	public function filter_resume_link( $resume_url, $form, $resume_token, $email ) {
		if ( rgar( $_REQUEST, 'action' ) !== 'gfcb_get_form' ) {
			return $resume_url;
		}

		$referer = rgar( $_SERVER, 'HTTP_REFERER' );

		if ( ! $referer ) {
			return $resume_url;
		}

		return add_query_arg( array( 'gf_token' => $resume_token ), $referer );
	}

}

# Configuration

new GW_Cache_Buster();

//add_filter( 'gfcb_enable_cache_buster', '__return_true' );
