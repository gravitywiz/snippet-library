<?php
/**
 * Gravity Wiz // Gravity Forms // Update Posts
 *
 * Update existing post title, content, author and custom fields with values from Gravity Forms.
 *
 * @version 0.4.1
 * @author  Scott Buchmann <scott@gravitywiz.com>
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
				'terms'           => array(),
				'meta'            => array(),
				'featured_image'  => false,
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

		wp_update_post( $post );

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

}
