<?php
/**
* Gravity Wiz // Gravity Forms // Disable Submit Button Until Required Fields are Filled Out
*
* Disable submit buttones until all required fields have been filled out. Currently only supports single-page forms.
*
* @version   1.2
* @author    David Smith <david@gravitywiz.com>
* @license   GPL-2.0+
* @link      http://gravitywiz.com/...
* @copyright 2013 Gravity Wiz
*/
class GW_Disable_Submit {

	public static $script_output = false;

	public $form_id;

	public function __construct( $form_id ) {

		$this->form_id = $form_id;

		add_action( 'gform_pre_render', array( $this, 'maybe_output_script' ) );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );

	}

	public function maybe_output_script( $form ) {

		if ( $form['id'] != $this->form_id ) {
			return $form;
		}

		if ( ! self::$script_output ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
			self::$script_output = true;
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			var GWDisableSubmit;

			(function($){

				GWDisableSubmit = function( args ) {

					var self = this;

					// copy all args to current object: formId, fieldId
					for( prop in args ) {
						if( args.hasOwnProperty( prop ) )
							self[prop] = args[prop];
					}

					self.init = function() {

						$( args.inputHtmlIds.join( ', ' ) ).change( function() {
							self.runCheck();
						} );

						// Check for newly uploaded files
						args.inputHtmlIds.forEach(function (inputHtmlId){
							let searchTerm = "#gform_preview";
							let index	   = inputHtmlId.indexOf(searchTerm);
							if ( index !== -1 ) {
								let fieldId = inputHtmlId.substring(index + searchTerm.length).trim();
								window.gfMultiFileUploader.uploaders['gform_multifile_upload' + fieldId].bind('StateChanged', function() {
									if ( window.gfMultiFileUploader.uploaders['gform_multifile_upload' + fieldId].state == 1 ) {
										self.runCheck();
									}
								});
							}
						});

						// Check if field becomes empty on deleting file(s)
						$(document).on( 'click', '.gform_delete_file', function() {
							self.runCheck();
						});

						// Check when fields are repopulated because of GPPA
						$(document).on( 'gppa_updated_batch_fields', function() {
							self.runCheck();
						});
						// GPPA logic may enable submit button, disable that logic.
						gform.addFilter( 'gppa_disable_form_navigation_toggling', function() { 
							return true; 
						});

						self.runCheck();

					}

					self.runCheck = function() {

						var $form        = $( '#gform_' + self.formId );
						var submitButton = $form.find( 'input[type="submit"], input[type="button"], button[type="submit"]' );
						if( self.areRequiredPopulated() ) {
							submitButton.attr( 'disabled', false ).removeClass( 'gwds-disabled' );
						} else {
							submitButton.attr( 'disabled', true ).addClass( 'gwds-disabled' );
						}

					}

					self.areRequiredPopulated = function() {

						var inputs     = $( args.inputHtmlIds.join( ', ' ) ),
							inputCount = inputs.length,
							fullCount  = 0;

						$( inputs ).each( function() {

							var input   = $( this ),
								fieldId = input.attr( 'id' ).split( '_' )[2];

							// don't count fields hidden via conditional logic towards the inputCount
							if( window['gf_check_field_rule'] && gf_check_field_rule( self.formId, fieldId, null, null ) == 'hide' ) {
								inputCount -= 1;
								return;
							}

							if( $.trim( $( this ).val() ) ||
								$( this ).find( 'input:checked' ).length ||
								$( this ).find( '.ginput_preview' ).length
								) {
								fullCount += 1;
							}

						} );

						return fullCount == inputCount;
					}

					self.init();

				}

			})(jQuery);

		</script>

		<?php
	}

	public function add_init_script( $form ) {

		if ( $form['id'] != $this->form_id ) {
			return $form;
		}

		$args = array(
			'formId'       => $form['id'],
			'inputHtmlIds' => $this->get_required_input_html_ids( $form ),
		);

		$script = '; new GWDisableSubmit( ' . json_encode( $args ) . ' );';
		$slug   = "gw_disable_submit_{$form['id']}";

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function get_required_input_html_ids( $form ) {

		$html_ids = array();

		foreach ( $form['fields'] as &$field ) {

			if ( ! $field['isRequired'] ) {
				continue;
			}

			$input_ids = array();

			switch ( GFFormsModel::get_input_type( $field ) ) {

				case 'address':
					$input_ids = array( 1, 3, 4, 5, 6 );
					break;

				case 'name':
					$input_ids = array( 1, 2, 3, 4, 5, 6 );
					break;

				case 'fileupload':
					// Only multiple files enabled File Upload field has to be handled differently.
					if ( rgar( $field, 'multipleFiles' ) ) {
						$html_ids[] = "#gform_preview_{$form['id']}_{$field['id']}";
						break;
					}

				default:
					$html_ids[] = "#input_{$form['id']}_{$field['id']}";
					break;

			}

			if ( ! $input_ids ) {
				continue;
			}

			foreach ( $input_ids as $input_id ) {
				$html_ids[] = "#input_{$form['id']}_{$field['id']}_{$input_id}";
			}
		}

		return $html_ids;
	}

}

# Configuration

new GW_Disable_Submit( 534 );
