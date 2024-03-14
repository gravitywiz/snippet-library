<?php
/**
 * Gravity Wiz // Gravity Forms // Cache Buster
 * https://gravitywiz.com/cache-busting-with-gravity-forms/
 *
 * Bypass your website cache when loading a Gravity Forms form.
 *
 * Plugin Name: Gravity Forms Cache Buster
 * Plugin URI:  https://gravitywiz.com/cache-busting-with-gravity-forms/
 * Description: Bypass your website cache when loading a Gravity Forms form.
 * Author:      Gravity Wiz
 * Version:     0.6
 * Author URI:  https://gravitywiz.com
 */
class GW_Cache_Buster {

	private $args = array();

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
		add_filter( 'gform_pre_replace_merge_tags', array( $this, 'replace_embed_url' ), 10, 4 );
		add_action( 'gform_after_submission', array( $this, 'entry_source_url' ), 10, 2 );

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

		$is_enabled = rgar( $atts, 'cachebuster' ) || gf_apply_filters( array( 'gfcb_enable_cache_buster', $form_id ), false, $form_id );
		if ( ! $is_enabled ) {
			return $markup;
		}

		add_filter( "gform_footer_init_scripts_filter_{$form_id}", array( $this, 'suppress_default_post_render_event' ), 10, 3 );

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
		// Include original query parameters (with some exclusions) in the AJAX call to preserve dynamic population via query string.
		$exclude_params = array( 'action', 'form_id', 'atts' );
		$ajax_url       = remove_query_arg( $exclude_params, add_query_arg( $_GET, admin_url( 'admin-ajax.php' ) ) );

		// Still needed for the AJAX submission.
		$ajax_url = add_query_arg(
			array(
				'action'  => 'gfcb_get_form',
				'form_id' => $form_id,
			),
			$ajax_url
		);

		$lang = null;
		if ( class_exists( 'Gravity_Forms_Multilingual' ) ) {
			global $sitepress;
			$lang = $sitepress->get_current_language();
		}
		?>
		<script type="text/javascript">
			( function ( $ ) {
				var formId = '<?php echo $form_id; ?>';
				$.post( '<?php echo $ajax_url; ?>', {
					action: 'gfcb_get_form',
					form_id: '<?php echo $form_id; ?>',
					atts: <?php echo wp_json_encode( $attributes ); ?>,
					form_request_origin: '<?php echo esc_js( GFCommon::openssl_encrypt( GFFormsModel::get_current_page_url() ) ); ?>',
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
						<?php
							echo sprintf(
								'gform.initializeOnLoaded(function() {%s});',
								GFFormDisplay::post_render_script(
									$form_id,
									GFFormDisplay::get_current_page( $form_id )
							) );
						?>
					} );
				} );
			} )( jQuery );
		</script>

		<?php
		return ob_get_clean();
	}

	public function is_cache_busting_applicable() {
		// POSTED and LOGGED-IN requests are not typically cached
		return empty( $_POST ) || ! is_user_logged_in();
	}

	public function suppress_default_post_render_event( $form_string, $form, $current_page ) {
		$searches = array(
			"gform.initializeOnLoaded( function() { jQuery(document).trigger('gform_post_render', [{$form['id']}, {$current_page}]) } );",
			"gform.initializeOnLoaded( function() {jQuery(document).trigger('gform_post_render', [{$form['id']}, {$current_page}]);gform.utils.trigger({ event: 'gform/postRender', native: false, data: { formId: {$form['id']}, currentPage: {$current_page} } });} );",
		);

		foreach ( $searches as $search ) {
			$search      = GFCommon::get_inline_script_tag( $search );
			$form_string = str_replace( $search, '', $form_string );
		}

		if ( is_callable( 'GFFormDisplay::post_render_script' ) ) {
			$post_render_script = GFFormDisplay::post_render_script( $form['id'], $current_page );
			$post_render_script = preg_quote( $post_render_script, '/' ); // Escape special characters
			$pattern            = '/<script>\s*gform\.initializeOnLoaded\(\s*function\(\)\s*\{\s*(' . $post_render_script . ')\s*\}\s*\);\s*<\/script>/';
			$form_string        = preg_replace( $pattern, '', $form_string );
		}

		return $form_string;
	}

	public function ajax_get_form() {

		$form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
		if ( ! $form_id ) {
			$form_id = isset( $_GET['form_id'] ) ? absint( $_GET['form_id'] ) : 0;
		}

		add_filter( 'gpasc_new_draft_form_path', function( $form_path, $form ) {
			return rgpost( 'form_request_origin' )
				? remove_query_arg( 'gf_token', GFCommon::openssl_decrypt( rgpost( 'form_request_origin' ) ) )
				: $form_path;
		}, 10, 2 );

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
		add_filter( 'gform_form_tag_' . $form_id, array( $this, 'add_hidden_inputs' ), 10, 2 );
		add_filter( 'gform_pre_render_' . $form_id, array( $this, 'replace_embed_url_for_field_default_values' ) );

		$atts = rgpost( 'atts' );

		// GF expects an associative array for field values. Parse them before passing it on.
		$field_values = wp_parse_args( rgar( $atts, 'field_values' ) );

		// If `$_POST` is not an empty array GF 2.5 fails to select default values for checkbox fields. See HS#26188
		$GLOBALS['GWCB_POST'] = $_POST;
		$_POST                = array();

		gravity_form( $form_id, filter_var( rgar( $atts, 'title', true ), FILTER_VALIDATE_BOOLEAN ), filter_var( rgar( $atts, 'description', true ), FILTER_VALIDATE_BOOLEAN ), false, $field_values, true /* default to true; add support for non-ajax in the future */, rgar( $atts, 'tabindex' ) );

		remove_filter( 'gform_form_tag_' . $form_id, array( $this, 'add_hidden_inputs' ) );
		remove_filter( 'gform_pre_render_' . $form_id, array( $this, 'replace_embed_url_for_field_default_values' ) );

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

	/**
	 * Replace `{embed_url}` merge tag for notifications, confirmations, etc.
	 *
	 * @param string $text The current text with merge tags.
	 * @param array $form The current form.
	 * @param array $entry The current entry.
	 * @param boolean $url_encode The URLs need to be encoded or not.
	 *
	 * @return string
	 */
	public function replace_embed_url( $text, $form, $entry, $url_encode ) {
		// Check if the text contains the {embed_url} merge tag
		if ( strpos( $text, '{embed_url}' ) !== false ) {
			$origin = $this->get_form_request_origin();

			if ( ! $origin ) {
				return $text;
			}

			// Replace the {embed_url} merge tag with the original URL
			$text = str_replace( '{embed_url}', $origin, $text );
		}

		return $text;
	}

	/**
	 * Append hidden inputs to store the entry url.
	 *
	 * @param array $entry The current entry.
	 * @param array $form The current form.
	 */
	function entry_source_url( $entry, $form ) {
		$origin = $this->get_form_request_origin();

		if ( ! $origin ) {
			return;
		}

		GFAPI::update_entry_property( $entry['id'], 'source_url', $origin );
	}

	/**
	 * Replace {embed_url} merge tag for field default values. We have to do this using gform_pre_render as
	 * gform_pre_replace_merge_tags is not called for field default values.
	 *
	 * @param array $form The current form.
	 */
	public function replace_embed_url_for_field_default_values( $form ) {
		foreach ( $form['fields'] as &$field ) {
			if ( strpos( $field->defaultValue, '{embed_url}' ) !== false ) {
				$origin = $this->get_form_request_origin();

				if ( ! $origin ) {
					continue;
				}

				$field->defaultValue = str_replace( '{embed_url}', $origin, $field->defaultValue );
			}
		}

		return $form;
	}

	/**
	 * Helper method to get the form request origin.
	 */
	public function get_form_request_origin() {
		static $form_request_origin;

		if ( $form_request_origin ) {
			return $form_request_origin;
		}

		$origin = rgpost( 'gwcb_form_request_origin' );
		$origin = $origin ?: rgpost( 'form_request_origin' );
		$origin = $origin ?: rgars( $GLOBALS, 'GWCB_POST/form_request_origin' );

		if ( $origin ) {
			$form_request_origin = GFCommon::openssl_decrypt( $origin );
		} else {
			$form_request_origin = null;
		}

		return $form_request_origin;
	}

	/**
	 * Append hidden inputs to store the entry url.
	 *
	 * @param string $form_tag The form opening tag.
	 * @param array $form The current form.
	 *
	 * @return string
	 */
	public function add_hidden_inputs( $form_tag, $form ) {
		if ( strpos( $form_tag, 'gwcb_form_request_origin' ) !== false ) {
			return $form_tag;
		}

		$form_tag .= '<input type="hidden" value="' . esc_attr( rgars( $GLOBALS, 'GWCB_POST/form_request_origin' ) ) . '" name="gwcb_form_request_origin" />';
		return $form_tag;
	}
}

# Configuration

new GW_Cache_Buster();

# Enable Cache Buster on all forms.
//add_filter( 'gfcb_enable_cache_buster', '__return_true' );

# Enable Cache Buster on all forms with exceptions.
//add_filter( 'gfcb_enable_cache_buster', function( $should_enable, $form_id ) {
//	if ( ! in_array( $form_id, array( 493, 124, 125 ) ) ) {
//		$should_enable = true;
//	}
//	return $should_enable;
//}, 10, 2 );
