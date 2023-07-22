<?php
/**
 * Gravity Wiz // Gravity Forms // All Fields Template
 *
 * Modify the {all_fields} merge tag output via a template file.
 *
 * Plugin Name:  Gravity Forms All Fields Template
 * Plugin URI:   https://gravitywiz.com/gravity-forms-all-fields-template/
 * Description:  Modify the {all_fields} merge tag output via a template file.
 * Author:       Gravity Wiz
 * Version:      0.10
 * Author URI:   http://gravitywiz.com
 *
 * Usage:
 *
 * {all_fields}
 *
 *     By default, enabling this plugin will look for this template:
 *     <theme>/gravity-forms/all-fields.php
 *
 *     Override the {all_fields} template for a specific form by specifying the form ID:
 *     <theme>/gravity-forms/all-fields-<formId>.php
 *
 * {all_fields:template[custom]}
 *
 *     Specify a custom template suffix. This is useful for allowing specific forms to use the same template.
 *     <theme>/gravity-forms/all-fields-custom.php
 *
 * {all_fields:notemplate}
 *
 *     Will always load the default Gravity Forms {all_fields} markup.
 *
 * {all_fields:nopricingfields}
 *
 *     Hide all pricing fields (i.e. order summary).
 *
 * Filtering Usage:
 *
 * :filter
 *
 *     Filtering will only include the specified fields and exclude all others. It cannot be combined with the include
 *     exclude filters.
 *
 *     {all_fields:filter[1]}
 *     {all_fields:filter[1,2]}
 *
 * :include
 *
 *     Including will include fields with types that are typically not supported by the {all_fields} merge tag
 *     (e.g., HTML fields).
 *
 *     {all_fields:include[3]}
 *     {all_fields:include[3,4]}
 *     {all_fields:include[3,4],exclude[5]}
 *
 * :exclude
 *
 *     Excluding will exclude specific fields from being included in the {all_fields} output.
 *
 *     {all_fields:exclude[5]}
 *     {all_fields:exclude[5,6]}
 *     {all_fields:exclude[5],include[3,4]}
 *
 */
class GW_All_Fields_Template {

	private static $instance = null;

	public static function get_instance() {
		if ( self::$instance == null ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gform_pre_replace_merge_tags', array( $this, 'replace_merge_tags' ), 9, 7 );
		add_filter( 'gform_merge_tag_filter', array( $this, 'all_fields_extra_options' ), 21, 6 );

	}

	/**
	 * to exclude field from notification add 'exclude[ID]' option to {all_fields} tag
	 * 'include[ID]' option includes HTML field / Section Break field description / Signature image in notification
	 * see http://www.gravityhelp.com/documentation/page/Merge_Tags for a list of standard options
	 *
	 * include: Include a field that is not included by default (i.e. HTML fields).
	 * exclude: Exclude a field that is included by default.
	 * filter:  Only include the specified field IDs. This takes precedence over both the 'include' and 'exclude' modifiers.
	 *
	 * example: {all_fields:exclude[2,3]}
	 * example: {all_fields:include[6]}
	 * example: {all_fields:include[6],exclude[2,3]}
	 */
	public function all_fields_extra_options( $value, $merge_tag, $modifiers, $field, $raw_value, $format ) {

		if ( ! is_a( $field, 'GF_Field' ) ) {
			$field       = new GF_Field();
			$field->type = $field;
		}

		if ( $merge_tag !== 'all_fields' && $field->type === 'form' ) {
			return $value;
		}

		$modifiers = $this->parse_modifiers( $modifiers );
		$whitelist = array( 'filter', 'include', 'exclude', 'nopricingfields' );
		$context   = rgar( $modifiers, 'context', false );

		foreach ( $modifiers as $modifier => $mod_values ) {

			if ( ! in_array( $modifier, $whitelist ) ) {
				continue;
			}

			if ( ! is_array( $mod_values ) ) {
				$mod_values = array( $mod_values );
			}

			foreach ( $mod_values as $mod_value ) {
				if ( ! is_numeric( $mod_value ) ) {
					/**
					 * Filter an individual value specified for the given modifier.
					 *
					 * The primary intent is to provide a mechanism for specifying a custom value that will be replaced
					 * with an array of field IDs. For example, if you want to exclude fields with personal information
					 * from some instances of the {all_fields} merge tag, you could specify a custom modifier value like
					 * so:
					 *
					 * ```
					 * {all_fields:exclude[persInfoFields]}
					 * ```
					 *
					 * And then create a custom function to identify the IDs of those fields.
					 *
					 * ```
					 * add_filter( 'gwaft_modifier_value_persInfoFields', function() {
					 *     return array( 1, 2, 3 );
					 * } );
					 * ```
					 *
					 * @since 0.9.9
					 *
					 * @param string    $mod_value The custom modifier value to be replaced.
					 * @param string    $modifier  The current modifier.
					 * @param array     $modifiers All modifiers specified for the current merge tag.
					 * @param \GF_Field $field     The current field.
					 */
					$new_value = gf_apply_filters( array( 'gwaft_modifier_value', $mod_value, $field->formId ), $mod_value, $modifier, $modifiers, $field );
					if ( $new_value !== $mod_value ) {
						array_splice( $mod_values, array_search( $mod_value, $mod_values, true ), 1, $new_value );
					}
				}
			}

			if ( $modifier === 'nopricingfields' && ! has_filter( 'gform_order_summary', array( $this, 'clear_order_summary' ) ) ) {

				add_filter( 'gform_order_summary', array( $this, 'clear_order_summary' ) );

				// Hide "Order Summary" label if `:nopricingfields` is used.
				add_filter( 'gform_display_product_summary', array( $this, 'hide_order_summary_label' ) );

			}

			/**
			 * Integrate w/ GP Nested Forms to allow filtering which fields are displayed for nested entries in the
			 * Nested Form field's value. GPNF will pass custom modifiers (e.g., context[nested],parent[fieldId]).
			 */
			if ( $context == 'nested' ) {

				$nested_form_field_id = rgar( $modifiers, 'parent', false );
				if ( ! $nested_form_field_id ) {
					break;
				}

				$field_ids = array();
				foreach ( $mod_values as $field_id ) {
					if ( intval( $field_id ) == $nested_form_field_id && $field_id !== intval( $field_id ) ) {
						$field_id_bits = explode( '.', $field_id );
						$field_ids[]   = (int) array_pop( $field_id_bits );
					}
				}
			} else {

				$input_ids = $mod_values;
				$field_ids = array_map( 'intval', $mod_values );

			}

			switch ( $modifier ) {
				case 'filter':
					if ( in_array( $field->id, $field_ids ) ) {
						// Check for input-specific filters.
						if ( is_array( $raw_value ) && ! in_array( $field->id, $input_ids ) ) {
							$filtered_values = array();

							foreach ( $input_ids as $input_id ) {
								if ( in_array( $input_id, $input_ids ) ) {
									$filtered_values[ $input_id ] = $raw_value[ $input_id ];
								}
							}

							$value = GFCommon::get_lead_field_display( $field, $filtered_values );
						}

						$value = $this->get_all_fields_field_value( $field, $value );
					} else {
						$value = false;
					}
					break;
				case 'include':
					if ( in_array( $field->id, $field_ids ) ) {
						$value = $this->get_all_fields_field_value( $field, $value );
					}
					break;
				case 'exclude':
					if ( in_array( (int) $field->id, $field_ids, true ) ) {

						$exclude_full_value = true;

						// Check for input-specific exclusions.
						if ( is_array( $raw_value ) ) {
							$values = $raw_value;
							foreach ( $input_ids as $input_id ) {
								$target_field_id = intval( $input_id );
								if ( (string) $target_field_id !== (string) $input_id && $target_field_id === intval( $field->id ) ) {
									$exclude_full_value  = false;
									$values[ $input_id ] = '';
								}
							}
							$value = GFCommon::get_lead_field_display( $field, $values );

							// Consent form requires special treatment to remove the description HTML that's output into {all_fields}
							// phpcs:ignore WordPress.PHP.StrictInArray.MissingTrueStrict
							if ( $field->type === 'consent' && in_array( $field->id . '.3', $input_ids ) ) {
								$revision_id = ! is_array( $value ) || empty( $value[ $input_id ] )
									? GFFormsModel::get_latest_form_revisions_id( $field['formId'] ) : $value[ $input_id ];

								$consent_description = $field->get_field_description_from_revision( $revision_id );
								$description_html    = '<br /><div class="gfield_consent_description">' . nl2br( $consent_description ) . '</div>';
								$value               = str_replace( $description_html, '', $value );
							}
							// Check for exclusions excluding a specific child field from a Nested Form field.
						} elseif ( $field->get_input_type() === 'form' && (int) $mod_value != $mod_value ) {
							// GPNF assumes Nested Form field should be excluded if $value is false. Prevent this and
							// allow GPNF to load the {all_fields} markup for its children.
							$exclude_full_value = false;
						}

						if ( $exclude_full_value ) {
							$value = false;
						}
					}
					break;
			}
		}

		// echo '<pre>';
		// $field_id = $field->id;
		// print_r( compact( 'modifiers', 'field_ids', 'field_id', 'value' ) );
		// echo '<pre>';
		return $value;
	}

	public function replace_merge_tags( $text, $form, $entry, $url_encode, $esc_html, $nl2br, $format ) {

		$matches = array();

		preg_match_all( '/{all_fields(?::(.*?))?}/i', $text, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {

			// Replace each unique merge tag only once.
			if ( strpos( $text, $match[0] ) === false ) {
				continue;
			}

			$modifiers = rgar( $match, 1 );
			$options   = compact( 'url_encode', 'esc_html', 'nl2br', 'format' );
			$content   = $this->parse_template( 'all-fields', $modifiers, $form, $entry, $options );

			if ( $content !== false ) {
				$text = str_replace( $match[0], $content, $text );
			}
		}

		return $text;
	}

	public function parse_template( $template, $modifiers, $form, $entry, $options = array() ) {

		$_modifiers = $modifiers;
		$modifiers  = $this->parse_modifiers( $modifiers );

		if ( in_array( 'notemplate', $modifiers ) ) {
			return false;
		}

		$use_value       = in_array( 'value', $modifiers );
		$display_empty   = in_array( 'empty', $modifiers );
		$use_admin_label = in_array( 'admin', $modifiers );

		$suffixes = array( $form['id'] );
		if ( array_key_exists( 'template', $modifiers ) ) {
			array_unshift( $suffixes, $modifiers['template'] );
		}

		$items = rgar( $options, 'items' );
		if ( empty( $items ) ) {
			$items = $this->get_items( $form, $entry, $display_empty, ! $use_value, $options['format'], $use_admin_label, 'all_fields', $_modifiers );
		}

		$output = $this->load_template( $template, null, array(
			'form'  => $form,
			'entry' => $entry,
			'items' => $items,
		), $suffixes );

		if ( $output === false && rgar( $options, 'fallback' ) ) {
			if ( is_callable( $options['fallback'] ) ) {
				$output = call_user_func( $options['fallback'], $items );
			}
		}

		return $output;
	}

	public function replace_nested_forms_all_fields( $value, $field, $nested_form, $entry, $modifiers ) {

		$output = $this->parse_template( $modifiers, $nested_form, $entry, array( 'format' => 'html' ) );
		if ( $output ) {
			$value = $output;
		}

		return $value;
	}

	public function parse_modifiers( $modifiers_str ) {

		preg_match_all( '/([a-z_]+)(?:(?:\[(.+?)\])|,?)/i', $modifiers_str, $modifiers, PREG_SET_ORDER );
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

	public function get_items( $form, $lead, $display_empty = false, $use_text = false, $format = 'html', $use_admin_label = false, $merge_tag = '', $modifiers = '' ) {
		return $this->get_submitted_fields( $form, $lead, $display_empty, $use_text, $format, $use_admin_label, $merge_tag, $modifiers );
	}

	public function get_submitted_fields( $form, $lead, $display_empty = false, $use_text = false, $format = 'html', $use_admin_label = false, $merge_tag = '', $modifiers = '' ) {

		$items = array();

		//$field_data = '';

		$modifiers_array         = explode( ',', $modifiers );
		$no_admin                = in_array( 'noadmin', $modifiers_array );
		$no_hidden               = in_array( 'nohidden', $modifiers_array );
		$display_product_summary = false;

		foreach ( $form['fields'] as $field ) {

			$field_value = '';
			$field_label = $use_admin_label && ! empty( $field->adminLabel ) ? $field->adminLabel : esc_html( GFCommon::get_label( $field, 0, false, $use_admin_label ) );

			switch ( $field->type ) {
				case 'captcha':
					break;
				case 'section':
					$field_value = false;

					if ( GFFormsModel::is_field_hidden( $form, $field, array(), $lead ) ) {
						break;
					}

					if ( ( ! GFCommon::is_section_empty( $field, $form, $lead ) || $display_empty ) && ! $field->adminOnly ) {

						switch ( $format ) {
							case 'text':
								$field_value = "--------------------------------\n{$field_label}\n\n";
								break;

							default:
								$field_value = '';
								break;
						}
					}

					$field_value = apply_filters( 'gform_merge_tag_filter', $field_value, $merge_tag, $modifiers, $field, $field_label, $format );

					//$field_data .= $field_value;

					if ( $field_value !== false ) {
						$item = array(
							'label' => $field_label,
							'value' => $field_value,
							'field' => $field,
						);
					}

					break;
				case 'password':
					//ignore password fields
					break;

				default:
					if ( GFCommon::is_product_field( $field->type ) ) {

						// ignore product fields as they will be grouped together at the end of the grid
						$display_product_summary = apply_filters( 'gform_display_product_summary', true, $field, $form, $lead );
						if ( $display_product_summary ) {
							break;
						}
					} elseif ( GFFormsModel::is_field_hidden( $form, $field, array(), $lead ) ) {
						// ignore fields hidden by conditional logic
						break;
					}

					$raw_field_value = RGFormsModel::get_lead_field_value( $lead, $field );
					$field_value     = GFCommon::get_lead_field_display( $field, $raw_field_value, rgar( $lead, 'currency' ), $use_text, $format, 'email' );

					$display_field = true;
					//depending on parameters, don't display adminOnly or hidden fields
					if ( $no_admin && $field->adminOnly ) {
						$display_field = false;
					} elseif ( $no_hidden && RGFormsModel::get_input_type( $field ) == 'hidden' ) {
						$display_field = false;
					}

					//if field is not supposed to be displayed, pass false to filter. otherwise, pass field's value
					if ( ! $display_field ) {
						$field_value = false;
					}

					$field_value = apply_filters( 'gform_merge_tag_filter', $field_value, $merge_tag, $modifiers, $field, $raw_field_value, $format );

					if ( $field_value === false ) {
						break;
					}

					if ( ! empty( $field_value ) || strlen( $field_value ) > 0 || $display_empty ) {

						switch ( $format ) {
							case 'text':
								//$field_data .= "{$field_label}: {$field_value}\n\n";
								break;

							default:
								//                              $field_data .= sprintf(
								//                                  '<tr bgcolor="%3$s">
								//                                                            <td colspan="2">
								//                                                                <font style="font-family: sans-serif; font-size:12px;"><strong>%1$s</strong></font>
								//                                                            </td>
								//                                                       </tr>
								//                                                       <tr bgcolor="%4$s">
								//                                                            <td width="20">&nbsp;</td>
								//                                                            <td>
								//                                                                <font style="font-family: sans-serif; font-size:12px;">%2$s</font>
								//                                                            </td>
								//                                                       </tr>
								//                                                       ', $field_label, empty( $field_value ) && strlen( $field_value ) == 0 ? '&nbsp;' : $field_value, apply_filters( 'gform_email_background_color_label', '#EAF2FA', $field, $lead ), apply_filters( 'gform_email_background_color_data', '#FFFFFF', $field, $lead )
								//                              );
								break;
						}

						$item = array(
							'label' => $field_label,
							'value' => $field_value,
							'field' => $field,
						);

					}
			}

			if ( isset( $item ) ) {
				$items[] = $item;
				unset( $item );
			}
		}

		if ( $display_product_summary ) {
			$value = $this->all_fields_extra_options( GFCommon::get_submitted_pricing_fields( $form, $lead, $format, $use_text, $use_admin_label ), $merge_tag, $modifiers, 'order_summary', null, $format );
			if ( $value !== false ) {
				$items[] = array(
					'label' => apply_filters( 'gwaft_order_summary_label', 'Order Summary' ),
					'value' => $value,
				);
			}
		}

		return $items;
	}

	public function get_all_fields_field_value( $field, $value ) {

		switch ( $field->type ) {
			case 'html':
				$value = $field->content;
				break;
			// Sections are included by default; including them manually will append their description.
			case 'section':
				$value .= sprintf( '<tr bgcolor="#FFFFFF">
	                                                        <td width="20">&nbsp;</td>
	                                                        <td>
	                                                            <font style="font-family: sans-serif; font-size:12px;">%s</font>
	                                                        </td>
	                                                   </tr>
	                                                   ', $field->description );
				break;
			case 'signature':
				if ( version_compare( GF_SIGNATURE_VERSION, '4.0', '<' ) ) {
					$url   = is_callable( 'gf_signature' ) ? gf_signature()->get_url( $value ) : $value;
					$value = "<img alt='signature' src='{$url}' />";
				}
				break;
		}

		return $value;
	}

	public function log( $message ) {
		GFCommon::log_debug( $message );
	}

	public function clear_order_summary() {
		remove_filter( 'gform_order_summary', array( $this, 'clear_order_summary' ) );
		return '';
	}

	public function hide_order_summary_label() {
		remove_filter( 'gform_display_product_summary', array( $this, 'hide_order_summary_label' ) );
		return false;
	}

	// ### TEMPLATE SYSTEM (compliments of EDD) ###

	public function load_template( $slug, $name = null, $data = array(), $suffixes = array() ) {
		ob_start();
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $data );
		$template = $this->get_template_part( $slug, $name, false, $suffixes );
		if ( ! empty( $template ) ) {
			include( $template );
		}
		$content = ob_get_clean();
		return ! $template ? false : $content;
	}

	public function get_template_part( $slug, $name = null, $load = true, $suffixes = array() ) {

		// Execute code for this part
		do_action( 'get_template_part_' . $slug, $slug, $name, $suffixes );

		// Setup possible parts
		$templates = array();

		if ( isset( $name ) ) {
			$suffixes[] = $name;
		}

		foreach ( $suffixes as $suffix ) {
			$templates[] = $slug . '-' . $suffix . '.php';
		}

		$templates[] = $slug . '.php';

		// Return the part that is found
		return $this->locate_template( $templates, $load, false );
	}

	public function locate_template( $template_names, $load = false, $require_once = true ) {

		// No file found yet
		$located = false;

		// Try to find a template file
		foreach ( (array) $template_names as $template_name ) {

			// Continue if template is empty
			if ( empty( $template_name ) ) {
				continue;
			}

			// Trim off any slashes from the template name
			$template_name = ltrim( $template_name, '/' );

			// try locating this template file by looping through the template paths
			foreach ( $this->get_theme_template_paths() as $template_path ) {

				if ( file_exists( $template_path . $template_name ) ) {
					$located = $template_path . $template_name;
					break;
				}
			}

			if ( $located ) {
				break;
			}
		}

		if ( ( true == $load ) && ! empty( $located ) ) {
			load_template( $located, $require_once );
		}

		return $located;
	}

	public function get_theme_template_paths() {

		$template_dir = $this->get_theme_template_dir_name();

		/**
		 * Filter the paths that will be checked for a template file.
		 *
		 * @since 0.9.12
		 *
		 * @param array $file_paths An array of file paths. Key is the priority of the path. Value is an absolute path to a directory.
		 */
		$file_paths = apply_filters( 'gwaft_template_paths', array(
			1  => trailingslashit( get_stylesheet_directory() ) . $template_dir,
			10 => trailingslashit( get_template_directory() ) . $template_dir,
		) );

		// sort the file paths based on priority
		ksort( $file_paths, SORT_NUMERIC );

		return array_map( 'trailingslashit', $file_paths );
	}

	public function get_theme_template_dir_name() {
		return trailingslashit( 'gravity-forms' );
	}

}

function gw_all_fields_template() {
	return GW_All_Fields_Template::get_instance();
}

gw_all_fields_template();
