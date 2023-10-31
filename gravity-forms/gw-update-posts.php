<?php
/**
 * Gravity Wiz // Gravity Forms // Update Posts
 *
 * Update existing post title, content, author and custom fields with values from Gravity Forms.
 *
 * @version 0.4.3
 * @author  Scott Ryer <scott@gravitywiz.com>
 * @license GPL-2.0+
 * @link    http://gravitywiz.com
 */
class GW_Update_Posts {

	public function __construct( $args = array() ) {

		// Set our default arguments, parse against the provided arguments, and store for use throughout the class.
		$this->_args = wp_parse_args(
			$args,
			array(
				'form_id'         => false,
				'post_id'         => false,
				'title'           => false,
				'content'         => false,
				'author'          => false,
				'status'          => false,
				'slug'            => false,
				'terms'           => array(),
				'meta'            => array(),
				'featured_image'  => false,
				'post_date'       => array(
					'date' => false,
					'time' => false,
				),
				// If property is mapped but no entry value is submitted, delete the property.
				// Currently only works with 'featured_image' and custom fields specified in 'meta'.
				'delete_if_empty' => false,
			)
		);

		// Do version check in the init to make sure if GF is going to be loaded, it is already loaded.
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		// Make sure we're running the required minimum version of Gravity Forms.
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		if ( ! empty( $this->_args['form_id'] ) ) {
			add_action( "gform_after_submission_{$this->_args['form_id']}", array( $this, 'update_post_by_entry' ), 10, 2 );
			add_filter( 'gppa_process_template', array( $this, 'return_ids_instead_of_names' ), 9, 8 );
			// Update posts after Gravity View updates an entry
			add_action( 'gravityview/edit_entry/after_update', array( $this, 'gv_entry_after_update' ), 10, 4 );
		}
	}

	public function gv_entry_after_update( $form, $entry_id, $gv_object, $gv_data ) {
		if ( $form['id'] == $this->_args['form_id'] ) {
			$entry = GFAPI::get_entry( $entry_id );
			$this->update_post_by_entry( $entry, $form );
		}
	}

	public function update_post_by_entry( $entry, $form ) {

		// Get the post and, if the current user has capabilities, update post with new content.
		$post = get_post( rgar( $entry, $this->_args['post_id'] ) );
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		if ( $this->_args['title'] ) {
			$post->post_title = rgar( $entry, $this->_args['title'] );
		}

		if ( $this->_args['content'] ) {
			$post->post_content = rgar( $entry, $this->_args['content'] );
		}

		if ( $this->_args['author'] ) {
			$post->post_author = (int) rgar( $entry, $this->_args['author'] );
		}

		if ( $this->_args['status'] ) {
			$stati  = get_post_stati();
			$status = rgar( $entry, $this->_args['status'] );
			if ( in_array( $status, $stati, true ) ) {
				$post->post_status = $status;
			}
		}

		if ( $this->_args['slug'] ) {
			$post->post_name = rgar( $entry, $this->_args['slug'] );
		}

		if ( $this->_args['post_date'] ) {
			$new_date_time       = $this->get_post_date( $entry, $form );
			$post->post_date     = $new_date_time;
			$post->post_date_gmt = get_gmt_from_date( $new_date_time );
		}

		if ( $this->_args['featured_image'] && is_callable( 'gp_media_library' ) ) {
			if ( rgar( $entry, $this->_args['featured_image'] ) ) {
				$image_id = gp_media_library()->get_file_ids( $entry['id'], $this->_args['featured_image'], 0 );
				if ( $image_id ) {
					set_post_thumbnail( $post, $image_id );
				}
			} elseif ( $this->_args['delete_if_empty'] ) {
				delete_post_meta( $post->ID, '_thumbnail_id' );
			}
		}

		if ( $this->_args['terms'] ) {

			// Assign custom taxonomies.
			$term_fields = is_array( $this->_args['terms'] ) ? $this->_args['terms'] : array( $this->_args['terms'] );
			foreach ( $term_fields as $field ) {
				$term_field = GFAPI::get_field( $form, $field );
				$terms      = array_map( 'intval', explode( ',', is_object( $term_field ) ? $term_field->get_value_export( $entry ) : '' ) );
				$taxonomy   = is_object( $term_field ) ? $term_field['choices'][0]['object']->taxonomy : '';

				wp_set_post_terms( $post->ID, $terms, $taxonomy );
			}
		}

		if ( $this->_args['meta'] ) {

			$meta_input = array();

			// Assign custom fields.
			foreach ( $this->_args['meta'] as $key => $value ) {

				$meta_value = rgar( $entry, $value );

				$field = GFAPI::get_field( $form, $value );

				// Support mapping all checkboxes of a Checkbox field to a custom field.
				if ( $field->get_input_type() === 'checkbox' ) {
					$meta_value = $field->get_value_export( $entry );
					if ( is_callable( 'acf_get_field' ) ) {
						$acf_field = acf_get_field( $key );
						if ( $acf_field ) {
							$meta_value = array_map( 'trim', explode( ',', $meta_value ) );
						}
					}
				}

				// Check for ACF image-like custom fields. Integration powered by GP Media Library.
				$acf_field = is_callable( 'gp_media_library' ) && is_callable( 'acf_get_field' ) ? acf_get_field( $key ) : false;
				if ( $acf_field && in_array( $acf_field['type'], array( 'image', 'file', 'gallery' ), true ) ) {
					gp_media_library()->acf_update_field( $post->ID, $key, GFAPI::get_field( $form, $value ), $entry );
				} else {
					// Map all other custom fields generically.
					if ( ! rgblank( $meta_value ) ) {
						$meta_input[ $key ] = $meta_value;
					} elseif ( $this->_args['delete_if_empty'] ) {
						delete_post_meta( $post->ID, $key );
					}
				}
			}

			$post->meta_input = $meta_input;

		}

		wp_update_post( $post, false, false );

	}

	/**
	 * When populating selected terms back into a field using a Field Value Object, the terms are returned as names rather
	 * than IDs. This function modifies the returned values to be IDs.
	 *
	 * @param $value
	 * @param $field
	 * @param $template_name
	 * @param $populate
	 * @param $object
	 * @param $object_type
	 * @param $objects
	 * @param $template
	 *
	 * @return mixed
	 */
	public function return_ids_instead_of_names( $value, $field, $template_name, $populate, $object, $object_type, $objects, $template ) {
		if ( strpos( $template, 'taxonomy_' ) === 0 ) {
			$taxonomy = preg_replace( '/^taxonomy_/', '', $template );
			$terms    = wp_get_post_terms( $object->ID, $taxonomy, array( 'fields' => 'ids' ) );
			remove_filter( 'gppa_process_template', array( $this, 'return_ids_instead_of_names' ), 9 );
			$value = gf_apply_filters(
				array(
					'gppa_process_template',
					$template_name,
				),
				$terms,
				$field,
				$template_name,
				$populate,
				$object,
				$object_type,
				$objects,
				$template
			);
			add_filter( 'gppa_process_template', array( $this, 'return_ids_instead_of_names' ), 9, 8 );
		}
		return $value;
	}

	public function get_post_date( $entry, $form ) {

		if ( ! is_array( $this->_args['post_date'] ) ) {
			$post_date_field = GFAPI::get_field( $form, $this->_args['post_date'] );
			if ( $post_date_field->get_input_type() === 'date' ) {
				$post_date['date'] = $this->_args['post_date'];
				$post_date['time'] = '';
			} elseif ( $post_date_field->get_input_type() === 'time' ) {
				$post_date['time'] = $this->_args['post_date'];
				$post_date['date'] = '';
			}
		} else {
			$post_date['date'] = $this->_args['post_date']['date'];
			$post_date['time'] = $this->_args['post_date']['time'];
		}

		$date = rgar( $entry, $post_date['date'], gmdate( 'm/d/Y' ) );
		$time = rgar( $entry, $post_date['time'], '00:00 am' );

		if ( $time ) {
			list( $hour, $min, $am_pm ) = array_pad( preg_split( '/[: ]/', $time ), 3, false );
			if ( strtolower( $am_pm ) == 'pm' ) {
				$hour += 12;
			}
		}

		return gmdate( 'Y-m-d H:i:s', strtotime( sprintf( '%s %s:%s:00', $date, $hour, $min ) ) );
	}

}
