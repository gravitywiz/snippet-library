<?php
/**
 * Gravity Wiz // Gravity Forms // Cache Buster
 *
 * Bypass your website cache when loading a Gravity Forms form.
 *
 * @version 0.5
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    http://gravitywiz.com/
 *
 * Plugin Name: Gravity Forms Cache Buster
 * Plugin URI: http://gravitywiz.com/
 * Description: Bypass your website cache when loading a Gravity Forms form.
 * Author: Gravity Wiz
 * Version: 0.5
 * Author URI: http://gravitywiz.com
 *
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
		?>
		<script type="text/javascript">
			( function ( $ ) {
				$.post( '<?php echo admin_url( 'admin-ajax.php' ); ?>?action=gfcb_get_form&form_id=<?php echo $form_id, $params; ?>', {
					action: 'gfcb_get_form',
					form_id: '<?php echo $form_id; ?>',
					atts: '<?php echo json_encode( $attributes ); ?>'
				}, function( response ) {
					$( '#gf-cache-buster-form-container-<?php echo $form_id; ?>' ).html( response ).fadeIn();
					if( window['gformInitDatepicker'] ) {
						gformInitDatepicker();
					}
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

	public function ajax_get_form() {

		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		if ( ! $form_id ) {
			$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		}

		$atts = json_decode( rgpost( 'atts' ), true );

		gravity_form( $form_id, filter_var( rgar( $atts, 'title', true ), FILTER_VALIDATE_BOOLEAN ), filter_var( rgar( $atts, 'description', true ), FILTER_VALIDATE_BOOLEAN ), false, rgar( $atts, 'field_values' ), true /* default to true; add support for non-ajax in the future */, rgar( $atts, 'tabindex' ) );

		die();
	}

}

# Configuration

new GW_Cache_Buster();

//add_filter( 'gfcb_enable_cache_buster', '__return_true' );
