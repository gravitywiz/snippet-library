<?php
/**
 * Gravity Perks // Pay Per Word // Pay Per Line
 * https://gravitywiz.com/documentation/gravity-forms-pay-per-word/
 *
 * Pay per line instead of per word.
 */
class GPPPW_Pay_Per_Line {

	private $_args = array();

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

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		add_filter( 'gpppw_word_count', array( $this, 'get_line_count' ), 10, 3 );

	}

	public function get_line_count( $word_count, $words, $price_field ) {
		if ( $this->is_applicable_form( $price_field->formId ) && $price_field->id == $this->_args['field_id'] ) {
			$word_count = count( explode( "\n", $words ) );
		}
		return $word_count;
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

				window.GPPPWPayPerLine = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( var prop in args ) {
						if( args.hasOwnProperty( prop ) ) {
							self[ prop ] = args[ prop ];
						}
					}

					self.init = function() {

						gform.addFilter( 'gpppw_word_count', function( wordCount, text, gwppw, ppwField, formId ) {
							if ( formId == self.formId && ppwField.price_field == self.fieldId ) {
								wordCount = text.split( "\n" ).length;
							}
							return wordCount;
						} );

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

		$script = 'new GPPPWPayPerLine( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gpppw_pay_per_line', $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id == (int) $this->_args['form_id'];
	}

}

# Configuration

new GPPPW_Pay_Per_Line( array(
	'form_id'  => 123,
	'field_id' => 4,
) );
