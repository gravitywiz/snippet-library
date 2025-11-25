<?php
/**
 * Gravity Wiz // Gravity Forms // Advanced Merge Tags
 *
 * Adds support for advanced Gravity Forms merge tags and field modifiers.
 *
 * Advanced merge tags:
 *   + post:id=xx&prop=xxx
 *       Retrieve the desired property of the specified post (by ID).
 *   + post_meta:id=xx&meta_key=xxx
 *     custom_field:id=xx&meta_key=xxx
 *       Retrieve the desired post meta value from the specified post and meta key.
 *   + source_post:xxx
 *       Retrieve a property from the source post that displayed the form (when save_source_post_id is enabled).
 *   + entry:id=xx&prop=xxx
 *       Retrieve a core entry property (e.g. id, date_created, payment_status).
 *   + entry_meta:id=xx&meta_key=xxx
 *       Retrieve an entry meta value for the specified entry and meta key.
 *
 * Value helpers:
 *   + get() modifier
 *       Retrieve a value from the query string ($_GET).
 *       Example: post_meta:id=get(pid)&meta_key=xxx
 *   + post() modifier
 *       Retrieve a value from the current POST payload ($_POST).
 *       Example: post_meta:id=post(pid)&meta_key=xxx
 *
 * GET merge tags:
 *   + {get:foo}
 *       Retrieve the "foo" query string parameter.
 *   + {get:foo[whitelist=one,two,three]}
 *       Only output the value when it matches the whitelist.
 *
 * Dynamic population:
 *   + Supports using advanced merge tags in "Allow field to be populated dynamically" parameter names.
 *     Example: a field with parameter name "{post:id=get(pid)&prop=post_title}" will be pre-populated accordingly.
 *
 * HTML fields:
 *   + {HTML:3}
 *       Output the content of HTML field ID 3.
 *   + {all_fields:allowHtmlFields}
 *       Include HTML field content when outputting {all_fields}.
 *
 * Field modifiers (used as :modifier on merge tags/fields):
 *   + :wordcount
 *       Return the word count for the field value.
 *   + :urlencode
 *       URL-encode the value.
 *   + :rawurlencode
 *       Raw URL-encode the value.
 *   + :uppercase
 *       Convert the value to uppercase.
 *   + :lowercase
 *       Convert the value to lowercase.
 *   + :capitalize
 *       Capitalize each word in the value.
 *   + :mask
 *       Mask the value, preserving only the first and last character. Special handling for email addresses.
 *   + :abbr
 *       For Address fields, return the two-letter country code of the selected country.
 *   + :selected[0]
 *       For Checkbox and Multi Select fields, return the selected choice at the given zero-based index.
 *   + :gravatar[format=url,size=64,default=...]
 *       For Email fields, output a Gravatar URL or image tag based on the email address.
 *   + :base64
 *       Return the base64-encoded value.
 *
 * Example use case:
 *
 *   You have multiple realtors, each represented by their own WordPress page. On each page is a
 *   "Contact this Realtor" link that passes the realtor page ID as "pid" in the query string. A
 *   single contact form can then display:
 *
 *     "You are contacting realtor {post:id=get(pid)&prop=post_title}."
 *
 *   This retrieves the post_title (e.g. "Bob Smith") of the realtor page the visitor came from.
 *
 * Plugin Name: Gravity Forms Advanced Merge Tags
 * Plugin URI: https://gravitywiz.com
 * Description: Provides a host of new ways to work with Gravity Forms merge tags and field modifiers.
 * Version: 1.6
 * Author: Gravity Wiz
 * Author URI: https://gravitywiz.com/
 */
class GW_Advanced_Merge_Tags {

	/**
	 * @TODO:
	 *   - add support for validating based on the merge tag (to prevent values from being changed)
	 *   - add support for merge tags in dynamic population parameters
	 *   - add merge tag builder
	 */

	private $_args = null;

	public static $instance = null;

	public static function get_instance( $args ) {

		if ( null == self::$instance ) {
			self::$instance = new self( $args );
		}

		return self::$instance;
	}

	private function __construct( $args ) {
		$this->_args = wp_parse_args( $args, array(
			'save_source_post_id' => false,
		) );

		add_action( 'init', array( $this, 'add_hooks' ) );
	}

	public function add_hooks() {
		if ( ! class_exists( 'GFForms' ) ) {
			return;
		}

		add_action( 'gform_pre_render', array( $this, 'support_dynamic_population_merge_tags' ) );

		add_action( 'gform_merge_tag_filter', array( $this, 'support_html_field_merge_tags' ), 10, 4 );
		add_action( 'gform_replace_merge_tags', array( $this, 'replace_merge_tags' ), 12, 3 );

		/**
		 * `gform_pre_replace_merge_tags` is only called if GFCommon::replace_variables() is called whereas
		 * `gform_replace_merge_tags` is called if GFCommon::replace_variables() is called or if
		 * GFCommon::replace_variables_prepopulate() is called independently. Ideally, we want to replace {get} merge
		 * tags as early as possible so we need to bind to both functions.
		 */

		add_action( 'gform_pre_replace_merge_tags', array( $this, 'replace_get_variables' ), 10, 5 );
		add_action( 'gform_replace_merge_tags', array( $this, 'replace_get_variables' ), 10, 5 );

		add_action( 'gform_merge_tag_filter', array( $this, 'handle_field_modifiers' ), 10, 6 );

		if ( $this->_args['save_source_post_id'] ) {
			add_filter( 'gform_entry_created', array( $this, 'save_source_post_id' ), 10, 2 );
		}
	}

	public function support_dynamic_population_merge_tags( $form ) {

		$filter_names = array();

		foreach ( $form['fields'] as &$field ) {

			if ( ! rgar( $field, 'allowsPrepopulate' ) ) {
				continue;
			}

			// complex fields store inputName in the "name" property of the inputs array
			if ( is_array( rgar( $field, 'inputs' ) ) && $field['type'] != 'checkbox' ) {
				foreach ( $field['inputs'] as $input ) {
					if ( rgar( $input, 'name' ) ) {
						$filter_names[] = array(
							'type' => $field['type'],
							'name' => rgar( $input, 'name' ),
						);
					}
				}
			} else {
				$filter_names[] = array(
					'type' => $field['type'],
					'name' => rgar( $field, 'inputName' ),
				);
			}
		}

		foreach ( $filter_names as $filter_name ) {

			// do standard GF prepop replace first...
			$filtered_name = GFCommon::replace_variables_prepopulate( $filter_name['name'] );

			// if default prepop doesn't find anything, do our advanced replace
			if ( $filter_name['name'] == $filtered_name ) {
				$filtered_name = $this->replace_merge_tags( $filter_name['name'], $form, null );
			}

			if ( $filter_name['name'] == $filtered_name ) {
				continue;
			}

			add_filter( "gform_field_value_{$filter_name['name']}", function() use ( $filtered_name ) {
				return (string) $filtered_name;
			} );
		}

		return $form;
	}

	public function replace_merge_tags( $text, $form, $entry ) {

		// at some point GF started passing a pre-submission generated entry, it will have a null ID
		if ( rgar( $entry, 'id' ) == null ) {
			$entry = null;
		}

		// matches {Label:#fieldId#}
		//         {Label:#fieldId#:#options#}
		//         {Custom:#options#}
		preg_match_all( '/{(\w+)(:([\w&,=)(\-]+)){1,2}}/mi', $text, $matches, PREG_SET_ORDER );

		foreach ( $matches as $match ) {

			list( $tag, $type, $args_match, $args_str ) = array_pad( $match, 4, false );
			parse_str( $args_str, $args );

			$args  = array_map( array( $this, 'check_for_value_modifiers' ), $args );
			$value = '';

			switch ( $type ) {
				case 'post':
					$value = $this->get_post_merge_tag_value( $args );
					break;
				case 'post_meta':
				case 'custom_field':
					$value = $this->get_post_meta_merge_tag_value( $args );
					break;
				case 'source_post':
					if ( empty( $entry ) || ! rgar( $entry, 'id' ) ) {
						break;
					}
					$source_post_id = gform_get_meta( $entry['id'], 'source_post_id' );
					if ( ! $source_post_id ) {
						break;
					}
					$args['id']   = $source_post_id;
					$args['prop'] = $args_str;
					$value        = $this->get_post_merge_tag_value( $args );
					break;
				case 'entry':
					$args['entry'] = $entry;
					$value         = $this->get_entry_merge_tag_value( $args );
					break;
				case 'entry_meta':
					$args['entry'] = $entry;
					$value         = $this->get_entry_meta_merge_tag_value( $args );
					break;
					// @todo: Add a whitelist here that the user can provide when they initialize the class.
					//                  case 'callback':
					//                      $args['callback'] = array_shift( array_keys( $args ) );
					//                      unset( $args[ $args['callback'] ] );
					//                      $args['entry'] = $entry;
					//                      $value         = $this->get_callback_merge_tag_value( $args );
					//                      break;
				default:
					continue 2;
			}

			// @todo: figure out if/how to support values that are not strings
			if ( is_array( $value ) || is_object( $value ) ) {
				$value = '';
			}

			$text = str_replace( $tag, $value, $text );

		}

		return $text;
	}

	public function save_source_post_id( $entry, $form ) {

		if ( is_singular() && ! rgget( 'gf_page' ) ) {
			$post_id = get_queried_object_id();
			gform_update_meta( $entry['id'], 'source_post_id', $post_id );
		}

	}

	public function check_for_value_modifiers( $text ) {

		// modifier regex (i.e. "get(value)")
		preg_match_all( '/([a-z]+)\(([a-z_\-]+)\)/mi', $text, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) ) {
			return $text;
		}

		foreach ( $matches as $match ) {

			list( $tag, $type, $arg ) = array_pad( $match, 3, false );
			$value                    = '';

			switch ( $type ) {
				case 'get':
					$value = rgget( $arg );
					break;
				case 'post':
					$value = rgpost( $arg );
					break;
			}

			$text = str_replace( $tag, $value, $text );

		}

		return $text;
	}

	public function get_post_merge_tag_value( $args ) {

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( wp_parse_args( $args, array(
			'id'   => false,
			'prop' => false,
		) ) );

		if ( ! $id || ! $prop ) {
			return '';
		}

		$post = get_post( $id );
		if ( ! $post ) {
			return '';
		}

		return isset( $post->$prop ) ? $post->$prop : '';
	}

	public function get_post_meta_merge_tag_value( $args ) {

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( wp_parse_args( $args, array(
			'id'       => false,
			'meta_key' => false,
		) ) );

		if ( ! $id || ! $meta_key ) {
			return '';
		}

		$value = get_post_meta( $id, $meta_key, true );

		return $value;
	}

	public function get_entry_merge_tag_value( $args ) {

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( wp_parse_args( $args, array(
			'id'    => false,
			'prop'  => false,
			'entry' => false,
		) ) );

		if ( ! $entry ) {

			if ( ! $id ) {
				$id = rgget( 'eid' );
			}

			if ( is_callable( 'gw_post_content_merge_tags' ) ) {
				$id = gw_post_content_merge_tags()->maybe_decrypt_entry_id( $id );
			}

			$entry = GFAPI::get_entry( $id );

		}

		if ( ! $prop ) {
			$prop = key( $args );
		}

		if ( ! $entry || is_wp_error( $entry ) || ! $prop ) {
			return '';
		}

		$value = rgar( $entry, $prop );

		return $value;
	}

	public function get_entry_meta_merge_tag_value( $args ) {

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( wp_parse_args( $args, array(
			'id'       => false,
			'meta_key' => false,
			'entry'    => false,
		) ) );

		if ( ! $id ) {
			if ( rgget( 'eid' ) ) {
				$id = rgget( 'eid' );
			} elseif ( isset( $entry['id'] ) ) {
				$id = $entry['id'];
			}
		}

		if ( ! $meta_key ) {
			$meta_key = key( $args );
		}

		if ( ! $id || ! $meta_key ) {
			return '';
		}

		if ( is_callable( 'gw_post_content_merge_tags' ) ) {
			$id = gw_post_content_merge_tags()->maybe_decrypt_entry_id( $id );
		}

		$value = gform_get_meta( $id, $meta_key );

		return $value;
	}

	public function get_callback_merge_tag_value( $args ) {

		$callback = $args['callback'];
		unset( $args['callback'] );

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( wp_parse_args( $args, array(
			'entry' => false,
		) ) );

		if ( ! is_callable( $callback ) ) {
			return '';
		}

		return call_user_func( $callback, $args );
	}

	/**
	 * Replace {get:xxx} merge tags. Thanks, Gravity View!
	 *
	 * @param       $text
	 * @param array $form
	 * @param array $entry
	 * @param bool $url_encode
	 *
	 * @return mixed
	 */
	public function replace_get_variables( $text, $form, $entry, $url_encode, $esc_html, $get = null ) {

		if ( $get === null ) {
			$get = $_GET;
		}

		preg_match_all( '/{get:(.*?)}/ism', $text, $matches, PREG_SET_ORDER );
		if ( empty( $matches ) ) {
			return $text;
		}

		foreach ( $matches as $match ) {

			list( $search, $modifiers ) = $match;

			$modifiers = $this->parse_modifiers( $modifiers );
			$property  = array_shift( $modifiers );

			$value = stripslashes_deep( rgget( $property, $get ) );

			$whitelist = rgar( $modifiers, 'whitelist', array() );
			if ( $whitelist && ! in_array( $value, $whitelist ) ) {
				$value = null;
			}

			$glue  = gf_apply_filters( array( 'gpamt_get_glue', $property ), ', ', $property );
			$value = is_array( $value ) ? implode( $glue, $value ) : $value;
			$value = $url_encode ? urlencode( $value ) : $value;

			$esc_html = gf_apply_filters( array( 'gpamt_get_esc_html', $property ), $esc_html );
			$value    = $esc_html ? esc_html( $value ) : $value;

			$value = gf_apply_filters( array( 'gpamt_get_value', $property ), $value, $text, $form, $entry );

			$text = str_replace( $search, $value, $text );
		}

		return $text;
	}

	public function support_html_field_merge_tags( $value, $tag, $modifiers, $field ) {
		if ( $field->type == 'html' && ( $tag != 'all_fields' || in_array( 'allowHtmlFields', explode( ',', $modifiers ) ) ) ) {
			$value = $field->content;
		}

		return $value;
	}

	/**
	 * @param $value
	 * @param $input_id
	 * @param $modifier
	 * @param \GF_Field $field
	 * @param $raw_value
	 * @param $format
	 *
	 * @return mixed|void
	 */
	public function handle_field_modifiers( $value, $input_id, $modifier, $field, $raw_value, $format ) {

		$modifiers = $this->parse_modifiers( $modifier );

		if ( empty( $modifiers ) ) {
			return $value;
		}

		foreach ( $modifiers as $modifier => $modifier_options ) {
			switch ( $modifier ) {
				case 'wordcount':
					// Note: str_word_count() is not a great solution as it does not support characters with accents reliably.
					// Updated to use the same method we use in GP Pay Per Word.
					return count( array_filter( preg_split( '/[ \n\r]+/', trim( $value ) ) ) );
				case 'urlencode':
					return urlencode( $value );
				case 'rawurlencode':
					return rawurlencode( $value );
				case 'uppercase':
					return strtoupper( $value );
				case 'lowercase':
					return strtolower( $value );
				case 'capitalize':
					return ucwords( strtolower( $value ) );
				case 'mask':
					if ( GFCommon::is_valid_email( $value ) ) {
						list( $name, $domain ) = explode( '@', $value );
						$frags                 = explode( '.', $domain );
						$base                  = $this->mask_value( array_shift( $frags ) );
						$name                  = $this->mask_value( $name );
						// Example: "one.two.three@domain.gov.uk" → "o***********e@d****n.gov.uk".
						return sprintf( '%s@%s.%s', $name, $base, implode( '.', $frags ) );
					} else {
						// Example: "hello my old friend" → "h*****************d".
						return $this->mask_value( $value );
					}
				case 'abbr':
					// When used on address field returns two letter code of the selected country.
					// Example {My Address Field:1.6:abbr}
					$default_countries = array_flip( GF_Fields::get( 'address' )->get_default_countries() );
					return rgar( $default_countries, $value );
				case 'selected':
					// 'selected' can be used over 'Checkbox' or 'Multi Select' field to target the selected checkbox/multiselect choice by its zero-based index.
					if ( $field->type == 'checkbox' || $field->type == 'multiselect' ) {
						$index = $modifier_options;
						if ( $index !== 'selected' && is_numeric( $index ) ) {
							$index = intval( $index );
						} else {
							break;
						}

						$value_array = explode( ',', $value );
						return rgar( $value_array, $index );
					}
					break;
				case 'gravatar':
					if ( $field->type !== 'email' ) {
						break;
					}

					return $this->generate_gravatar($value, $modifiers);
				case 'base64':
					if ( $field->get_input_type() === 'fileupload' ) {
						// Handle file upload fields by encoding the actual file contents
						if ( ! empty( $raw_value ) ) {
							// Handle multiple files (array) or single file (string)
							$files = is_array( $raw_value ) ? $raw_value : json_decode( $raw_value, true );

							if ( ! is_array( $files ) ) {
								$files = array( $raw_value );
							}

							$encoded_files = array();

							foreach ( $files as $file_url ) {
								$file_path = $this->get_file_path_from_url( $file_url );

								if ( $file_path && file_exists( $file_path ) ) {
									$file_contents = file_get_contents( $file_path );
									if ( $file_contents !== false ) {
										$encoded_files[] = base64_encode( $file_contents );
									}
								}
							}

							// Return first file if single file, or JSON array if multiple
							return count( $encoded_files ) === 1 ? $encoded_files[0] : json_encode( $encoded_files );
						}
					}
					return base64_encode( $value );
			}
		}

		return $value;
	}

	public function mask_value( $value ) {
		$chars = str_split( $value );
		$first = array( array_shift( $chars ) );
		$last  = array( array_pop( $chars ) );
		return implode( '', array_merge( $first, array_pad( array(), count( $chars ), '*' ), $last ) );
	}

	/**
	 * Convert a file URL to a local file path.
	 *
	 * @param string $url File URL
	 *
	 * @return string|false File path on success, false on failure
	 */
	public function get_file_path_from_url( $url ) {
		// Remove query string if present
		$url = strtok( $url, '?' );

		// Get upload directory info
		$upload_dir = wp_upload_dir();

		// Check if URL is from the uploads directory
		if ( strpos( $url, $upload_dir['baseurl'] ) === 0 ) {
			// Replace the base URL with the base path
			$file_path = str_replace( $upload_dir['baseurl'], $upload_dir['basedir'], $url );
			return $file_path;
		}

		return false;
	}

	public function parse_modifiers( $modifiers_str ) {

		preg_match_all( '/([a-z0-9_]+)(?:(?:\[(.+?)\])|,?)/i', $modifiers_str, $modifiers, PREG_SET_ORDER );
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

	/**
	 * Generate a Gravatar image URL or image tag.
	 *
	 * @param $email
	 * @param $modifiers
	 *
	 * @return string
	 */
	public function generate_gravatar( $email, $modifiers ) {
		$format  = rgar( $modifiers, 'format' );
		$size    = rgar( $modifiers, 'size', 64 );
		$default = rgar( $modifiers, 'default' );

		$params = array();

		if ( $default ) {
			$params['d'] = htmlentities( $default );
		}

		if ( $size ) {
			$params['s'] = htmlentities($size);
		}

		$base_url = 'https://www.gravatar.com/avatar';
		$hash     = hash( 'sha256', strtolower( trim( $email ) ) );
		$query    = http_build_query( $params );

		$gravatar_url = sprintf( '%s/%s?%s', $base_url, $hash, $query );

		if ( $format === 'url' ) {
			return $gravatar_url;
		}

		return "<img src='{$gravatar_url}' alt='Gravatar Image'/>";
	}
}

function gw_advanced_merge_tags( $args = array() ) {
	return GW_Advanced_Merge_Tags::get_instance( $args );
}

gw_advanced_merge_tags( array(
	'save_source_post_id' => false,
) );
