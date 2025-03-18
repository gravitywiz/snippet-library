<?php

/**
 * Gravity Perks // File Upload Pro // Update Filename Markup
 * http://gravitywiz.com/documentation/gravity-forms-file-upload-pro
 *
 * This snippet allows you to update the filename markup to include the file URL. This snippet also works with the
 * GP Easy Passthrough plugin when multiple files are uploaded using GP File Upload Pro.
 *
 * Installation instructions:
 *   1. https://gravitywiz.com/documentation/managing-snippets/#where-do-i-put-snippets
 *   2. See usage instructions at the bottom of the file
 */
class GPFUP_Update_Filename_Markup {

	private $_args = array();

	public function __construct( $args = array() ) {
		// Set our default arguments, parse against the provided arguments, and store for use throughout the class.
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		// Do not proceed if Gravity Forms is not installed & activated.
		if ( ! class_exists( 'GFCommon' ) ) {
			return;
		}

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		$form_id  = $this->_args['form_id'];
		$field_id = $this->_args['field_id'];

		// Time for hooks.
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );

		$args = array( 'gpeb_post_file_population' );

		// If a form ID is provided, add it to the args.
		if ( $form_id ) {
			$args[] = $form_id;

			// If a field ID is provided, add it to the args.
			if ( $field_id ) {
				$args[] = $field_id;
			}
		}

		add_action( implode( '_', $args ), array(
			$this,
			'populate_file_data',
		), 10, 3 );
	}

	/**
	 * Populate file data.
	 *
	 * @param $file_upload_data
	 * @param $form
	 * @param $field
	 *
	 * @return void
	 */
	public function populate_file_data( $file_upload_data, $form, $field ) {
		add_action( 'wp_print_footer_scripts', function () use ( $file_upload_data, $form, $field ) {
			$form_id  = rgar( $form, 'id' );
			$field_id = rgar( $field, 'id' );

			echo '<script>
                jQuery(document).ready(function($) {
                    var fileData = ' . wp_json_encode( $file_upload_data ) . ';
                    var formId = ' . absint( $form_id ) . ';
                    var fieldId = ' . absint( $field_id ) . ";
                    
                    sessionStorage.setItem('gpep_filedata_' + formId + '_' + fieldId, JSON.stringify(fileData));
                });
            </script>";
		} );
	}

	public function load_form_script( $form, $is_ajax_enabled ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		if ( ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
		}

		if ( ! has_action( 'gform_preview_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			(function ($) {

				window.<?php echo __CLASS__; ?> = function (args) {
					self.init = function () {
						/**
						 * Filter the file name markup to include the file URL.
						 */
						window.gform.addFilter('gpfup_filename_markup', function (fileName, formId, fieldId, file) {
							var fileUrl = file.url || null;
							var fileData = JSON.parse(sessionStorage.getItem('gpep_filedata_' + formId + '_' + fieldId)) || [];

							if (!fileUrl) {
								if (typeof file.getNative === 'function') {
									var nativeFile = file.getNative();
									if (nativeFile instanceof File) {
										fileUrl = URL.createObjectURL(nativeFile);
									}
								} else if (Array.isArray(fileData)) { // Find the file URL from `fileData` based on the uploaded file name.
									var matchedFile = fileData.find(item => item.uploaded_filename === file.name);
									if (matchedFile) {
										fileUrl = matchedFile.url;
									}
								}
							}

							const sanitizedFileUrl = encodeURI(fileUrl || '');
							const sanitizedFileName = fileName.replace(/[<>&"']/g, (c) => {
								const escapes = {'<': '&lt;', '>': '&gt;', '&': '&amp;', '"': '&quot;', "'": '&#39;'};
								return escapes[c];
							});

							return fileUrl
								? `<a href="${sanitizedFileUrl}" target="_blank" rel="noopener noreferrer">${sanitizedFileName}</a>`
								: sanitizedFileName;
						});
					};

					self.init();
				}

			})(jQuery);

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

		$script = 'new ' . __CLASS__ . '( ' . wp_json_encode( $args ) . ' );';
		$slug   = implode( '_', array( strtolower( __CLASS__ ), $this->_args['form_id'], $this->_args['field_id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	public function is_applicable_form( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}
}

// Usage instructions.

/*
 * Example Usage (for demonstration purposes only):
 *
 * - Update 'form_id' and 'field_id' to match your specific requirements.
 */
new GPFUP_Update_Filename_Markup(
	array(
		'form_id'  => 33,
		'field_id' => 4, // Update to your file upload field ID.
	)
);
