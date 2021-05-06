<?php
/**
 * Gravity Wiz // Gravity Forms // Update Posts
 *
 * Update existing post title, content, author and custom fields with values from Gravity Forms.
 *
 * @version 0.4
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
				'form_id' => false,
				'post_id' => false,
				'title'   => false,
				'content' => false,
				'author'  => false,
				'terms'   => array(),
				'meta'    => array(),
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

		if ( $this->_args['terms'] ) {

			// Assign custom taxonomies.
			$term_fields = $this->_args['terms'];
			foreach ( $term_fields as $field ) {
				$term_field = GFAPI::get_field( $form, $field );
				$terms      = array_map( 'intval', explode( ',', is_object( $term_field ) ? $term_field->get_value_export( $entry ) : '' ) );
				$taxonomy   = is_object( $term_field ) ? $term_field['choices'][0]['object']->taxonomy : '';

				wp_set_post_terms( $post->ID, $terms, $taxonomy );
			}


		}

		if ( $this->_args['meta'] ) {

			// Assign custom fields.
			foreach ( $this->_args['meta'] as $key => $value ) {
				$meta_input[ "$key" ] = rgar( $entry, $value );
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
