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
				'terms'   => false,
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
			add_action( "gform_after_submission_{$this->_args['form_id']}", array( $this, 'set_post_content' ), 10, 2 );

		}
	}

	public function set_post_content( $entry, $form ) {

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
			$term_field = GFAPI::get_field( $form, $this->_args['terms'] );
			$terms      = array_map( 'intval', explode( ',', is_object( $term_field ) ? $term_field->get_value_export( $entry ) : '' ) );
			$taxonomy   = is_object( $term_field ) ? $term_field['choices'][0]['object']->taxonomy : '';

			wp_set_post_terms( $post->ID, $terms, $taxonomy );

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

}
