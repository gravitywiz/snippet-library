<?php
/**
 * Gravity Perks // Populate Anything // Page Modifier
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 *
 * Experimental Snippet 🧪
 *
 * This snippet allows you to use the `:page` modifier to only process Live Merge Tags on a specific page of a
 * multi-page form. This is useful when...
 *
 *  1. You have a large number of Live Merge Tags across multiple pages.
 *  2. You're experiencing slow page loads.
 *  2. Previous pages have no dependency on Live Merge Tags from subsequent pages.
 *
 * As an example, to only process a Live Merge Tag on page 2 of a form, you would use the following:
 *
 * @{My Field:1:page[2]}
 *
 * NOTE: This snippet is very much a proof-of-concept. It has not been tested thoroughly and may not work in all scenarios.
 *
 * Plugin Name:  GPPA Page Modifier
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Adds a `:page` modifier, allowing you to specify on which page a given Live Merge Tag should be processed.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
class GPPA_Page_Modifier {

	private $_args = array();

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array() );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		add_filter( 'gform_pre_replace_merge_tags', array( $this, 'handle_page_modifier' ), 10, 7 );

	}

	public function handle_page_modifier( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

		preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $text, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) ) {
			return $text;
		}

		foreach ( $matches as $match ) {
			$modifiers = $this->parse_modifiers( rgar( $match, 4 ) );
			if ( ! rgar( $modifiers, 'page' ) ) {
				continue;
			}
			if ( rgpost( 'page-number' ) && rgpost( 'page-number' ) < $modifiers['page'] ) {
				$text = str_replace( $match[0], '', $text );
			}
		}

		return $text;
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.GPPAPageModifier = function( args ) {

					var self = this;

					self.init = function() {

						gform.addFilter( 'gppa_batch_field_html_ajax_data', function( data ) {
							data['page-number'] = gf_get_input_id_by_html_id( $( '.gform_page:visible' ).attr( 'id' ) );
							return data;
						} );

					};

					self.init();

				}

			} )( jQuery );

		</script>

		<?php
	}

	public function add_init_script( $form ) {

		$args   = array();
		$script = 'new GPPAPageModifier( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gppa_page_modifier' ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function parse_modifiers( $modifiers_str ) {

		preg_match_all( '/([a-z]+)(?:(?:\[(.+?)\])|,?)/i', $modifiers_str, $modifiers, PREG_SET_ORDER );
		$parsed = array();

		foreach ( $modifiers as $modifier ) {

			list( $match, $modifier, $value ) = array_pad( $modifier, 3, null );
			if ( $value === null ) {
				$value = $modifier;
			}

			// Split '1,2,3' into array( 1, 2, 3 ).
			if ( strpos( $value, ',' ) !== false ) {
				$value = array_map( 'trim', explode( ',', $value ) );
			}

			$parsed[ strtolower( $modifier ) ] = $value;

		}

		return $parsed;
	}

}

# Configuration

new GPPA_Page_Modifier();
