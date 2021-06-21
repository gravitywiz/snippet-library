<?php
/**
 * Gravity Wiz // Gravity Forms // Set Number of List Field Rows by Field Value
 * https://gravitywiz.com/set-number-of-list-field-rows-by-field-value/
 *
 * Add/remove list field rows automatically based on the value entered in the specified field. Removes the add/remove
 * that normally buttons next to List field rows.
 *
 * Plugin Name:  Gravity Forms - Set Number of List Field Rows by Field Value
 * Plugin URI:   https://gravitywiz.com/set-number-of-list-field-rows-by-field-value/
 * Description:  This snippet adds/removes list field rows automatically based on the value entered in the specified field.
 * Author:       Gravity Wiz
 * Version:      1.3.1
 * Author URI:   https://gravitywiz.com
 */
class GWAutoListFieldRows {

	private static $_is_script_output;

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'       => false,
			'input_html_id' => false,
			'list_field_id' => false,
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		// time for hooks
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			window.gwalfr;

			(function($){
				gwalfr = function( args ) {

					this.formId      = args.formId,
					this.listFieldId = args.listFieldId,
					this.inputHtmlId = args.inputHtmlId;

					this.init = function() {

						var gwalfr = this,
							triggerInput = $( this.inputHtmlId );

						// update rows on page load
						this.updateListItems( triggerInput, this.listFieldId, this.formId );

						// update rows when field value changes
						triggerInput.change(function(){
							gwalfr.updateListItems( $(this), gwalfr.listFieldId, gwalfr.formId );
						});

						// Hide add/remove buttons
						$("#field_{0}_{1} .gfield_list_icons".format( this.formId, this.listFieldId ) ).css( 'display', 'none' );

					}

					this.updateListItems = function( elem, listFieldId, formId ) {

						var listField = $( '#field_' + formId + '_' + listFieldId ),
							count = parseInt( elem.val() );
						// `gfield_list_group` represents the rows in GF2.4 and 2.5. Use that instead of table markup.
						rowCount = listField.find('.gfield_list_group').length;
							diff = count - rowCount;

						if( diff > 0 ) {
							for( var i = 0; i < diff; i++ ) {
								listField.find( '.add_list_item:last' ).click();
							}
						} else {

							// make sure we never delete all rows
							if( rowCount + diff == 0 )
								diff++;

							for( var i = diff; i < 0; i++ ) {
								listField.find( '.delete_list_item:last' ).click();
							}

						}
					}

					this.init();

				}

			})(jQuery);

		</script>

		<?php
	}

	public function add_init_script( $form ) {

		if( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'formId'      => $this->_args['form_id'],
			'listFieldId' => $this->_args['list_field_id'],
			'inputHtmlId' => $this->_args['input_html_id'],
		);

		$script = 'new gwalfr(' . json_encode( $args ) . ');';
		$key    = implode( '_', $args );

		GFFormDisplay::add_init_script( $form['id'], 'gwalfr_' . $key, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

}

// EXAMPLE #1: Number field for the "input_html_id"
new GWAutoListFieldRows( array(
	'form_id'       => 240,
	'list_field_id' => 3,
	'input_html_id' => '#input_240_4',
) );

// EXAMPLE #2: Single Product Field's Quantity input as the "input_html_id"
// Note: input_html_id has a format of "#ginput_quantity_240_5" if using Gravity Forms <2.5 or if Legacy Markup is enabled
new GWAutoListFieldRows( array(
	'form_id'       => 240,
	'list_field_id' => 6,
	'input_html_id' => '#input_240_5_1'
) );
