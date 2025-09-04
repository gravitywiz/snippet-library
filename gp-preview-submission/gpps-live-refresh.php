<?php
/**
 * Gravity Perks // Preview Submission // Live Refresh
 * https://gravitywiz.com/documentation/gravity-forms-preview-submission/
 *
 * Live refresh the target field whenever a field changes.
 *
 * Plugin Name:  GP Preview Submission - Live Refresh
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-preview-submission/
 * Description:  Live refresh the target field whenever a field changes.
 * Author:       Gravity Wiz
 * Version:      0.8
 * Author URI:   https://gravitywiz.com/
 */
class GPPS_Live_Refresh {

	private $_args = array();

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'         => false,
			'target_field_id' => false,
		) );

		add_action( 'wp_ajax_gpps_refresh_field', array( $this, 'ajax_refresh' ) );
		add_action( 'wp_ajax_nopriv_gpps_refresh_field', array( $this, 'ajax_refresh' ) );

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_filter( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		if ( rgpost( 'action' ) == 'gpps_refresh_field' ) {
			remove_action( 'wp', array( 'GFForms', 'maybe_process_form' ), 9 );
			remove_action( 'admin_init', array( 'GFForms', 'maybe_process_form' ), 9 );
		}

	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			wp_enqueue_script( 'gform_gravityforms' );
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.GPPSLiveRefresh = function( args ) {

					var self = this;

					// copy all args to current object: (list expected props)
					for( prop in args ) {
						if( args.hasOwnProperty( prop ) )
							self[prop] = args[prop];
					}

					self.init = function() {

						self.$form        = $( '#gform_wrapper_{0}'.gformFormat( self.formId ) );
						self.$targetField = $( '#field_{0}_{1}'.gformFormat( self.formId, self.targetFieldId ) );

						self.$form.find( 'input, select, textarea' ).on( 'change', function() {
							self.refresh();
						} );

						self.refresh();

					};

					self.refresh = function() {

						if( ! self.$targetField.is( ':visible' ) ) {
							return;
						}

						var data = {
							action: 'gpps_refresh_field'
						};

						self.$form.find( 'input, select, textarea' ).each( function() {
							var $input = $( this ),
								name   = $input.attr( 'name' );

							if ( $input.is(':radio') ) {
								if ( $input.is(':checked') ) {
									data[name] = $input.val();
								}
							} else {
								data[name] = $input.val();
							}
						} );

						$.post( self.ajaxUrl, data, function( response ) {
							if( response.success ) {
								self.$targetField.html( response.data );
							}
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
			'formId'        => $this->_args['form_id'],
			'targetFieldId' => $this->_args['target_field_id'],
			'ajaxUrl'       => admin_url( 'admin-ajax.php' ),
		);

		$script = 'new GPPSLiveRefresh( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( 'gpps_live_refresh', $this->_args['form_id'], $this->_args['target_field_id'] ) );

		GFFormDisplay::add_init_script( $this->_args['form_id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

	public function ajax_refresh() {

		$entry = GFFormsModel::get_current_lead();
		if ( ! $entry ) {
			wp_send_json_error();
		}

		$form  = gf_apply_filters( array( 'gform_pre_render', $entry['form_id'] ), GFAPI::get_form( $entry['form_id'] ), false, array() );
		$field = GFFormsModel::get_field( $form, $this->_args['target_field_id'] );

		if ( $field->get_input_type() == 'html' ) {
			$field->content = GWPreviewConfirmation::preview_replace_variables( $field->content, $form );
			$content        = $field->get_field_input( $form, '', $entry );
		} else {
			$value   = rgpost( 'input_' . $field->id );
			$content = $field->get_field_content( $value, true, $form );
			$content = str_replace( '{FIELD}', $field->get_field_input( $form, $value, $entry ), $content );
		}

		wp_send_json_success( $content );
	}

}

# Configuration

new GPPS_Live_Refresh( array(
	'form_id'         => 123,
	'target_field_id' => 4,
) );
