<?php
/**
 * Gravity Wiz // Gravity Forms // Multi-File Merge Tag for Post Content Templates
 * https://gravitywiz.com/customizing-multi-file-merge-tag/
 *
 * Enhance the merge tag for multi-file upload fields by adding support for outputting markup that corresponds to the
 * uploaded file. Example: image files will be wrapped in an <img> tag. Out of the box, this snippet only supports
 * images and is limited to the 'jpg', 'png', and 'gif'.
 *
 * The default merge tag for the multi-file upload field will output the URL for each of the files.
 *
 * Plugin Name:  GF Multi-file Merge Tag Markup
 * Plugin URI:   https://gravitywiz.com/customizing-multi-file-merge-tag/
 * Description:  Enhance the merge tag for multi-file upload fields by adding support for outputting markup that corresponds to the uploaded file.
 * Author:       Gravity Wiz
 * Version:      1.7.2
 * Author URI:   https://gravitywiz.com
 */
class GW_Multi_File_Merge_Tag {

	private static $instance = null;

	/**
	 * Temporarily stores the values of the 'gform_merge_tag_filter' filter for use in the 'gform_replace_merge_tags' filter.
	 *
	 * @var array
	 */
	private $_merge_tag_args = array();
	private $_settings       = array();

	private function __construct() {
		add_filter( 'gform_pre_replace_merge_tags', array( $this, 'replace_merge_tag' ), 10, 7 );
		add_filter( 'gform_advancedpostcreation_post', array( $this, 'modify_apc_post_content' ), 10, 4 );
		add_filter( 'gform_merge_tag_filter', array( $this, 'process_all_fields_merge_tag' ), 10, 6 );
	}

	public static function get_instance() {

		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function get_default_args() {
		return array(
			'form_id'            => false,
			'field_ids'          => array(),
			'exclude_forms'      => array(),
			'default_markup'     => '<li><a href="{url}">{filename}.{ext}</a></li>',
			'formats'            => array( 'html' ),
			'markup'         => array(
				array(
					'file_types' => array( 'jpg', 'png', 'gif' ),
					'markup'     => '<img src="{url}" width="33%" />',
				),
				array(
					'file_types' => array( 'mp4', 'ogg', 'webm' ),
					'markup'     => '<video width="320" height="240" controls>
                                    <source src="{url}" type="video/{ext}">
                                    Your browser does not support the video tag.
                                 </video>',
				),
				array(
					'file_types' => array( 'ogv' ),
					'markup'     => '<video width="320" height="240" controls>
                                    <source src="{url}" type="video/ogg">
                                    Your browser does not support the video tag.
                                 </video>',
				),
			),
		);
	}

	public function register_settings( $args = array() ) {

		$args = wp_parse_args( $args, $this->get_default_args() );

		if ( ! $args['form_id'] ) {
			$this->_settings['global'] = $args;
		} else {
			$this->_settings[ $args['form_id'] ] = $args;
		}

	}

	public function process_all_fields_merge_tag( $field_value, $merge_tag, $modifiers, $field, $raw_value, $format ) {
		if ( $merge_tag === 'all_fields' && ! rgblank( $raw_value ) && $this->is_applicable_field( $field ) ) {
			$files = empty( $raw_value ) ? array() : json_decode( $raw_value, true );
			$value = '';
			if ( $files ) {
				foreach ( $files as &$file ) {
					$value .= $this->get_file_markup( $file, $field['formId'] );
					$value  = str_replace( $file, $field->get_download_url( $file, false ), $value );
				}
			}
			$field_value = $value;
		}
		return $field_value;
	}

	public function replace_merge_tag( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

		preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $text, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {

			$input_id = $match[1];
			$field    = GFFormsModel::get_field( $form, $input_id );

			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			$formats_setting = $this->get_formats( $form['id'] );
			// Check if the format is valid before parsing the merge tags.
			if ( ! in_array( $format, $formats_setting ) ) {

				$value = $this->_merge_tag_args['value'];

			} else {

				if ( $entry['id'] === null && is_callable( array( 'GWPreviewConfirmation', 'preview_image_value' ) ) ) {
					$files = GWPreviewConfirmation::preview_image_value( 'input_' . $field->id, $field, $form, $entry );
				} else {
					$value = GFFormsModel::get_lead_field_value( $entry, $field );
					$files = empty( $value ) ? array() : json_decode( $value, true );
				}

				$modifiers = $this->parse_modifiers( rgar( $match, 4 ) );
				$index     = rgar( $modifiers, 'index' );

				if ( ! rgblank( $index ) ) {

					if ( ! is_array( $index ) ) {
						$index = array( $index );
					}

					list( $offset, $length ) = array_pad( $index, 2, null );

					if ( $offset === null ) {
						$offset = 0;
					}

					if ( $length === null ) {
						$length = 1;
					}

					$files = array_slice( $files, $offset, $length );

				}

				$value = '';
				if ( $files ) {
					foreach ( $files as &$file ) {
						$value .= $this->get_file_markup( $file, $form['id'] );
						$value  = str_replace( $file, $field->get_download_url( $file, false ), $value );
					}
				}
			}

			$has_value = ! empty( $value );

			// Replace each instance of our merge tag individually so we can check if it is part of a [gf conditional]
			// shortcode; if so, replace the value with 1 so it can correctly evaluate as having a value.
			do {
				$pos = strpos( $text, $match[0] );
				if ( $pos !== false ) {
					$replace = substr( $text, $pos - 11, 10 ) === 'merge_tag=' ? $has_value : $value;
					$text    = substr_replace( $text, $replace, $pos, strlen( $match[0] ) );
				}
			} while ( $pos !== false );

		}

		return $text;
	}

	public function modify_apc_post_content( $post, $feed, $entry, $form ) {

		// GF_Advanced_Post_Creation::prepare_post_content() only accepts a feed so we must modify the content directly
		// in the feed and then pass the modified feed so it can do the rest of its work.
		$feed['meta']['postContent'] = $this->replace_merge_tag( $feed['meta']['postContent'], $form, $entry, false, false, true, 'html' );

		$post['post_content'] = gf_advancedpostcreation()->prepare_post_content( $feed, $entry, $form );

		return $post;
	}

	public function get_file_markup( $file, $form_id ) {

		$value     = str_replace( ' ', '%20', $file );
		$file_info = pathinfo( $value );

		extract( $file_info ); // gives us $dirname, $basename, $extension, $filename

		if ( ! $extension ) {
			return $value;
		}

		$markup_settings = $this->get_markup_settings( $form_id );
		if ( empty( $markup_settings ) ) {
			return $value;
		}

		$markup_found = false;

		foreach ( $markup_settings as $file_type_markup ) {

			$file_types = array_map( 'strtolower', $file_type_markup['file_types'] );
			if ( ! in_array( strtolower( $extension ), $file_types, true ) ) {
				continue;
			}

			$markup_found = true;
			$markup       = $file_type_markup['markup'];

			$tags = array(
				'{url}'      => $file,
				'{filename}' => $filename,
				'{basename}' => $basename,
				'{ext}'      => $extension,
			);

			foreach ( $tags as $tag => $tag_value ) {
				$markup = str_replace( $tag, $tag_value, $markup );
			}

			$value = $markup;
			break;
		}

		if ( ! $markup_found && $default_markup = $this->get_default_markup( $form_id ) ) {

			$tags = array(
				'{url}'      => $file,
				'{filename}' => $filename,
				'{basename}' => $basename,
				'{ext}'      => $extension,
			);

			foreach ( $tags as $tag => $tag_value ) {
				$default_markup = str_replace( $tag, $tag_value, $default_markup );
			}

			$value = $default_markup;

		}

		return $value;
	}

	public function get_markup_settings( $form_id ) {

		$form_markup_settings   = rgars( $this->_settings, "$form_id/markup" ) ? rgars( $this->_settings, "$form_id/markup" ) : array();
		$global_markup_settings = rgars( $this->_settings, 'global/markup' ) ? rgars( $this->_settings, 'global/markup' ) : array();

		return array_merge( $form_markup_settings, $global_markup_settings );
	}

	public function get_formats( $form_id ) {

		$formats = rgars( $this->_settings, "$form_id/formats" );
		if ( ! $formats ) {
			$formats = rgars( $this->_settings, 'global/formats' );
		}

		return $formats;
	}

	public function get_default_markup( $form_id ) {

		$default_markup = rgars( $this->_settings, "$form_id/default_markup" );
		if ( ! $default_markup ) {
			$default_markup = rgars( $this->_settings, 'global/default_markup' );
		}

		return $default_markup;
	}

	public function is_excluded_form( $form_id ) {

		$has_global_settings = isset( $this->_settings['global'] );
		$excluded_forms      = (array) rgars( $this->_settings, 'global/exclude_forms' );

		$explicity_excluded = $has_global_settings && in_array( $form_id, $excluded_forms );
		$passively_excluded = ! $has_global_settings && ! isset( $this->_settings[ $form_id ] );

		return $explicity_excluded || $passively_excluded;
	}

	public function is_applicable_field( $field ) {

		$field_ids = rgars( $this->_settings, "{$field->formId}/field_ids" );

		$is_valid_form        = ! $this->is_excluded_form( $field['formId'] );
		$is_matching_field_id = empty( $field_ids ) || in_array( $field->id, $field_ids );
		$is_file_upload_filed = GFFormsModel::get_input_type( $field ) === 'fileupload';
		$is_multi             = rgar( $field, 'multipleFiles' );

		return $is_valid_form && $is_matching_field_id && $is_file_upload_filed && $is_multi;
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

function gw_multi_file_merge_tag() {
	return GW_Multi_File_Merge_Tag::get_instance();
}

# Usage

gw_multi_file_merge_tag()->register_settings();

# Global
//gw_multi_file_merge_tag()->register_settings( array(
//	'markup' => array(
//		array(
//			'file_types' => array( 'pdf', 'txt', 'doc', 'docx', 'ppt', 'eps', 'zip' ),
//			'markup' => '<div class="gw-file gw-text gw-{ext}"><a href="{url}"><span>{filename}</span></a><a href="{url}">{filename}</a></div>'
//		)
//	)
//) );

# Global w/ exclusions
//gw_multi_file_merge_tag()->register_settings( array(
//	'exclude_forms' => 378
//) );

# Specific form
//gw_multi_file_merge_tag()->register_settings( array(
//    'form_id' => 402,
//    'markup' => array(
//        array(
//            'file_types' => array( 'jpg', 'jpeg' ),
//            'markup' => '<div class="gw-image"><a href="{url}" class="gw-image-link"><img src="{url}" width="100%" /></a><span>{filename}</span></div>'
//        )
//    )
//) );

//gw_multi_file_merge_tag()->register_settings( array(
//    'form_id' => 402,
//    'markup' => array(
//        array(
//            'file_types' => array( 'jpg', 'jpeg' ),
//            'markup' => '<li><a style="background-image: url({url});" class="formimages" rel="lightbox" href="{url}"></a></li>'
//        )
//    )
//) );

//gw_multi_file_merge_tag()->register_settings( array(
//	'form_id' => 391,
//	'markup' => array(
//		array(
//			'file_types' => array( 'jpg', 'jpeg', 'png', 'gif' ),
//			'markup' => '<div class="gw-file gw-image gw-{ext}"><a href="{url}"><img src="{url}"></a><a href="{url}">{filename}</a></div>'
//		),
//		array(
//			'file_types' => array( 'pdf', 'txt', 'doc', 'docx', 'ppt', 'eps', 'zip' ),
//			'markup' => '<div class="gw-file gw-text gw-{ext}"><a href="{url}"><span>{filename}</span></a><a href="{url}">{filename}</a></div>'
//		),
//		array(
//			'file_types' => array( 'mp4', 'ogg', 'webm' ),
//			'markup' => '<div class="gw-file gw-video gw-{ext}"><video><source src="{url}" type="video/{ext}" />Your browser does not support the video tag.</video><a href="{url}">{filename}</a></div>'
//		),
//		array(
//			'file_types' => array( 'ogv' ),
//			'markup' => '<div class="gw-file gw-video gw-ogv"><video><source src="{url}" type="video/ogg" />Your browser does not support the video tag.</video><a href="{url}">{filename}</a></div>'
//		),
//		array(
//			'file_types' => array( 'mp3' ),
//			'markup' => '<div class="gw-file gw-audio gw-{ext}"><audio controls><source src="{url}" type="audio/mpeg">Your browser does not support the audio tag.</audio><a href="{url}">{filename}</a></div>'
//		),
//		array(
//			'file_types' => array( 'wmv' ),
//			'markup' => '<div class="gw-file gw-video gw-{ext}"><object type="video/x-ms-wmv" data="{url}" width="100%" height="120" >
//					<param name="src" value="{url}" />
//					<param name="autostart" value="true" />
//					<param name="controller" value="true" />
//					<param name="qtsrcdontusebrowser" value="true" />
//					<param name="enablejavascript" value="true" />
//					<a href="{url}">Movie of a Fish Store in Barcelona</a></object><a href="{url}">{filename}</a>
//			</div>'
//		)
//	)
//) );
