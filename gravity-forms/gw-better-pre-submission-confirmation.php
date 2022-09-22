<?php
/**
 * Gravity Wiz // Gravity Forms // Better Pre-submission Confirmation
 * https://gravitywiz.com/better-pre-submission-confirmation/
 *
 * Add pre-submission confirmation to your forms so users can confirm the information they’ve entered is correct.
 */
class GWPreviewConfirmation {

	private static $lead;

	public static function init() {
		add_filter( 'gform_pre_render', array( __class__, 'replace_merge_tags' ) );
	}

	public static function replace_merge_tags( $form ) {

		if ( ! class_exists( 'GFFormDisplay' ) ) {
			return $form;
		}

		$current_page = isset( GFFormDisplay::$submission[ $form['id'] ] ) ? GFFormDisplay::$submission[ $form['id'] ]['page_number'] : 1;
		$fields       = array();

		// get all HTML fields on the current page
		foreach ( $form['fields'] as &$field ) {

			// skip all fields on the first page
			if ( rgar( $field, 'pageNumber' ) <= 1 ) {
				continue;
			}

			$default_value = rgar( $field, 'defaultValue' );
			preg_match_all( '/{.+}/', $default_value, $matches, PREG_SET_ORDER );
			if ( ! empty( $matches ) ) {
				// if default value needs to be replaced but is not on current page, wait until on the current page to replace it
				if ( rgar( $field, 'pageNumber' ) != $current_page ) {
					$field['defaultValue'] = '';
				} else {
					$field['defaultValue'] = self::preview_replace_variables( $default_value, $form );
				}
			}

			// only run 'content' filter for fields on the current page
			if ( rgar( $field, 'pageNumber' ) != $current_page ) {
				continue;
			}

			$html_content = rgar( $field, 'content' );
			preg_match_all( '/{.+}/', $html_content, $matches, PREG_SET_ORDER );
			if ( ! empty( $matches ) ) {
				$field['content'] = self::preview_replace_variables( $html_content, $form );
			}
		}

		return $form;
	}

	/**
	 * Adds special support for file upload, post image and multi input merge tags.
	 */
	public static function preview_special_merge_tags( $value, $input_id, $merge_tag, $field ) {

		// added to prevent overriding :noadmin filter (and other filters that remove fields)
		if ( ! $value ) {
			return $value;
		}

		$input_type = RGFormsModel::get_input_type( $field );

		$is_upload_field = in_array( $input_type, array( 'post_image', 'fileupload' ) );
		$is_multi_input  = is_array( rgar( $field, 'inputs' ) );
		$is_input        = intval( $input_id ) !== $input_id;

		if ( ! $is_upload_field && ! $is_multi_input ) {
			return $value;
		}

		// if is individual input of multi-input field, return just that input value
		if ( $is_input ) {
			return $value;
		}

		$form     = RGFormsModel::get_form_meta( $field['formId'] );
		$lead     = self::create_lead( $form );
		$currency = GFCommon::get_currency();

		if ( is_array( rgar( $field, 'inputs' ) ) ) {
			$value = RGFormsModel::get_lead_field_value( $lead, $field );
			return GFCommon::get_lead_field_display( $field, $value, $currency );
		}

		switch ( $input_type ) {
			case 'fileupload':
				$value = self::preview_image_value( "input_{$field['id']}", $field, $form, $lead );
				$value = self::preview_image_display( $field, $form, $value );
				break;
			default:
				$value = self::preview_image_value( "input_{$field['id']}", $field, $form, $lead );
				$value = GFCommon::get_lead_field_display( $field, $value, $currency );
				break;
		}

		return $value;
	}

	public static function preview_image_value( $input_name, $field, $form, $lead ) {

		$field_id  = $field['id'];
		$file_info = RGFormsModel::get_temp_filename( $form['id'], $input_name );
		$source    = RGFormsModel::get_upload_url( $form['id'] ) . '/tmp/' . $file_info['temp_filename'];

		if ( ! $file_info ) {
			return '';
		}

		switch ( RGFormsModel::get_input_type( $field ) ) {

			case 'post_image':
				list( ,$image_title, $image_caption, $image_description ) = explode( '|:|', $lead[ $field['id'] ] );
				$value = ! empty( $source ) ? $source . '|:|' . $image_title . '|:|' . $image_caption . '|:|' . $image_description : '';
				break;

			case 'fileupload':
				$value = $source;
				break;

		}

		return $value;
	}

	public static function preview_image_display( $field, $form, $value ) {

		// need to get the tmp $file_info to retrieve real uploaded filename, otherwise will display ugly tmp name
		$input_name = 'input_' . str_replace( '.', '_', $field['id'] );
		$file_info  = RGFormsModel::get_temp_filename( $form['id'], $input_name );

		$file_path = $value;
		if ( ! empty( $file_path ) ) {
			$file_path = esc_attr( str_replace( ' ', '%20', $file_path ) );
			$value     = "<a href='$file_path' target='_blank' title='" . __( 'Click to view', 'gravityforms' ) . "'>" . $file_info['uploaded_filename'] . '</a>';
		}
		return $value;

	}

	/**
	 * Retrieves $lead object from class if it has already been created; otherwise creates a new $lead object.
	 */
	public static function create_lead( $form ) {

		if ( empty( self::$lead ) ) {
			self::$lead = GFFormsModel::create_lead( $form );
			self::clear_field_value_cache( $form );
		}

		return self::$lead;
	}

	public static function preview_replace_variables( $content, $form ) {

		$lead = self::create_lead( $form );

		// add filter that will handle getting temporary URLs for file uploads and post image fields (removed below)
		// beware, the RGFormsModel::create_lead() function also triggers the gform_merge_tag_filter at some point and will
		// result in an infinite loop if not called first above
		add_filter( 'gform_merge_tag_filter', array( 'GWPreviewConfirmation', 'preview_special_merge_tags' ), 10, 4 );

		$content = GFCommon::replace_variables( $content, $form, $lead, false, false, false );

		// remove filter so this function is not applied after preview functionality is complete
		remove_filter( 'gform_merge_tag_filter', array( 'GWPreviewConfirmation', 'preview_special_merge_tags' ) );

		return $content;
	}

	public static function clear_field_value_cache( $form ) {

		if ( ! class_exists( 'GFCache' ) ) {
			return;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( GFFormsModel::get_input_type( $field ) == 'total' ) {
				GFCache::delete( 'GFFormsModel::get_lead_field_value__' . $field['id'] );
			}
		}

	}

}

GWPreviewConfirmation::init();
