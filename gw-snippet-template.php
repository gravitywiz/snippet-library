<?php
/**
 * Gravity Wiz // Gravity Forms // Snippet Name
 * http://gravitywiz.com/path/to/snippet/
 *
 * A brief description about this snippet and the functionality it provides. Might also include basic usage instructions if applicable.
 *
 * Plugin Name:  Snippet Name
 * Plugin URI:   http://gravitywiz.com/...
 * Description:  A brief description about this snippet and the functionality it provides. Might also include basic usage instructions if applicable.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GW_Snippet_Template {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// carry on

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Configuration

new GW_Snippet_Template();





/**
 * JS-powered Snippets
 */
class GW_JS_Snippet_Template {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// time for hooks
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

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

				window.<?php echo __CLASS__; ?> = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.isApplicableField = function(fieldId) {
						if ( typeof fieldId !== 'number' ) {
							fieldId = parseInt( fieldId );
						}

						if ( ! self.fieldId ) {
							return true;
						}

						if ( typeof self.fieldId !== 'object' ) {
							self.fieldId = [ self.fieldId ];
						}

						// Ensure fieldIds are all numbers
						self.fieldId = self.fieldId.map( function( fieldId ) {
							if ( typeof fieldId === 'string' ) {
								fieldId = parseInt( fieldId );
							}

							return fieldId;
						} );

						return self.fieldId.indexOf( fieldId ) !== -1;
					}

					self.init = function() {

						// if ( ! self.isApplicableField( fieldId ) ) {
						// 	return;
						// }

						console.log( 'doing the magic!' );

					};

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
			'formId'  => $this->_args['form_id'],
			'fieldId' => $this->_args['field_id'],
		);

		$script = 'new ' . __CLASS__ . '( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( strtolower( __CLASS__ ), $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

}

# Configuration

new GW_JS_Snippet_Template();

//new GW_JS_Snippet_Template( array(
//	'form_id'  => 1,
//) );
//
//new GW_JS_Snippet_Template( array(
//	'form_id'  => 1,
//	'field_id' => 6,
//) );
//
//new GW_JS_Snippet_Template( array(
//	'form_id'  => 1,
//	'field_id' => array( 5, 6 ),
//) );
