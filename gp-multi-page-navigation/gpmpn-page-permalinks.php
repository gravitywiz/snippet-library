<?php
/**
 * Gravity Perks // Multi-page Navigation // Page Permalinks
 * https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 *
 * Add permalinks for each page of your multi-page forms.
 *
 * Plugin Name:  GP Multi-page Navigation — Page Permalinks
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-multi-page-navigation/
 * Description:  Add permalinks for each page of your multi-page forms.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
class GPMPN_Page_Permalinks {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'    => false,
			'permalinks' => array(),
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// @todo For some reason after this runs for newly added page permalinks, all post permalinks are broken
		add_filter( 'option_rewrite_rules', array( $this, 'hotload_rewrite_rules' ) );
		add_action( 'gpmpn_default_page', array( $this, 'set_default_form_page' ) );

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		$this->add_query_var();
		$this->add_rewrite_rules();

	}

	public function add_query_var() {
		global $wp;
		$wp->add_query_var( 'gpmpn_page' );
	}

	public function add_rewrite_rules() {
		foreach ( $this->_args['permalinks'] as $page => $permalink ) {
			add_rewrite_rule( "^{$this->_args['pagename']}/{$permalink}?", "index.php?pagename={$this->_args['pagename']}&gpmpn_page={$page}", 'top' );
		}
	}

	public function hotload_rewrite_rules( $rules ) {
		static $did_rewrite_rules;

		if ( ! is_array( $rules ) ) {
			$rules = array();
		}

		$needs_flush = false;

		foreach ( $this->_args['permalinks'] as $page => $permalink ) {
			if ( ! array_key_exists( "^{$this->_args['pagename']}/{$permalink}?", $rules ) ) {
				$rules       = array( "^{$this->_args['pagename']}/{$permalink}?" => "index.php?pagename={$this->_args['pagename']}&gpmpn_page={$page}" ) + $rules;
				$needs_flush = true;
			}
		}

		//      if ( $needs_flush ) {
		//          if ( ! $did_rewrite_rules ) {
		//              $did_rewrite_rules = true;
		//              $this->flush_rewrite_rules();
		//          }
		//      }

		return $rules;
	}

	public function flush_rewrite_rules() {
		global $wp_rewrite;
		$wp_rewrite->flush_rules();
	}

	public function set_default_form_page( $page ) {
		return get_query_var( 'gpmpn_page' );
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.GPMPNPagePermalinks = function( args ) {

					var self = this;

					self.history = [];

					// copy all args to current object: (list expected props)
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						if ( typeof window[ 'GPMPNPagePermalinks_{0}'.format( self.formId ) ] !== 'undefined' ) {
							return;
						}

						window[ 'GPMPNPagePermalinks_{0}'.format( self.formId ) ] = self;

						self.history.push( { gpmpnPage: self.defaultPage } );

						console.log( 'init', self.history );

						$( document ).on( 'gform_page_loaded', function( event, formId, currentPage ) {
							if ( formId == self.formId ) {
								self.setPageState( currentPage );
							}
						} );

						$( document ).on( 'gform_confirmation_loaded', function( event, formId ) {
							if ( formId == self.formId ) {
								self.setPageState( 0 );
							}
						} );

						window.addEventListener( 'popstate', function ( event ) {
							// @todo Update to navigate the form to correct page based on the path.
							// var currentPage  = self.history.slice( -1 )[0].gpmpnPage;
							// var previousPage = self.history.slice( -2 )[0].gpmpnPage;
							// if ( previousPage < currentPage  ) {
							// 	$( '.gform_previous_button:visible' ).click();
							// }
							// Temporary solution: reload first page when user uses browser's back/next buttons.
							window.location = window.location.origin + '/{0}/{1}/'.format( self.pagename, self.permalinks[1] );
						} );

						self.isInit = true;

					};

					self.setPageState = function( currentPage ) {

						self.currentPage = parseInt( currentPage );

						var state = { gpmpnPage: self.currentPage };

						self.history.push( state );
						window.history.pushState( state, null, '/{0}/{1}/'.format( self.pagename, self.permalinks[ currentPage ] ) );

					}

					self.init();

				}

			} )( jQuery );

		</script>

		<?php
	}

	public function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'formId'      => $this->_args['form_id'],
			'pagename'    => $this->_args['pagename'],
			'permalinks'  => $this->_args['permalinks'],
			'defaultPage' => get_query_var( 'gpmpn_page' ),
		);

		$script = 'if ( typeof window.GPMPNPagePermalinks !== \'undefined\' ) { new GPMPNPagePermalinks( ' . json_encode( $args ) . ' ); }';
		$slug   = implode( '_', array( 'gpmpn_page_permalinks', $this->_args['form_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

}

# Configuration

new GPMPN_Page_Permalinks( array(
	'form_id'    => 889,
	'pagename'   => 'permalinks-for-form-pages',
	'permalinks' => array(
		1 => 'page-one',
		2 => 'page-two',
		3 => 'page-three',
		0 => 'confirmation',
	),
) );
