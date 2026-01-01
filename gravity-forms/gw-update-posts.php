<?php
/**
 * Gravity Wiz // Gravity Forms // Update Posts
 * https://gravitywiz.com/how-to-update-posts-with-gravity-forms/
 *
 * Update existing post title, content, author and custom fields with values from Gravity Forms.
 *
 * @version 0.7
 * @author  Scott Ryer <scott@gravitywiz.com>
 * @license GPL-2.0+
 * @link    http://gravitywiz.com
 */
class GW_Update_Posts {

	protected $_args;

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
				'excerpt'         => false,
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
			// Update posts after Gravity Flow User Input/Approval Workflow step.
			add_action( 'gravityflow_step_complete', array( $this, 'update_entry_after_workflow' ), 10, 4 );
		}
	}

	public function gv_entry_after_update( $form, $entry_id, $gv_object, $gv_data ) {
		if ( $form['id'] == $this->_args['form_id'] ) {
			$entry = GFAPI::get_entry( $entry_id );
			$this->update_post_by_entry( $entry, $form );
		}
	}

	public function update_entry_after_workflow( $step_id, $entry_id, $form_id, $status ) {
		if ( $form_id == $this->_args['form_id'] ) {
			$form  = GFAPI::get_form( $form_id );
			$entry = GFAPI::get_entry( $entry_id );
			$step  = gravity_flow()->get_step( $step_id, $entry );
			if ( $step && in_array( $step->get_type(), array( 'user_input', 'approval' ), true ) ) {
				$this->update_post_by_entry( $entry, $form );
			}
		}
	}

	public function update_post_by_entry( $entry, $form ) {

		$post_id = rgar( $entry, $this->_args['post_id'] );
		// If post not selected or post not available, return.
		if ( empty( $post_id ) ) {
			return;
		}
		// Get the post and, if the current user has capabilities, update post with new content.
		$post = get_post( $post_id );
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

		if ( $this->_args['excerpt'] ) {
			$post->post_excerpt = rgar( $entry, $this->_args['excerpt'] );
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

		if (
			( ! is_array( $this->_args['post_date'] ) && ! empty( $this->_args['post_date'] ) ) ||
				rgars( $this->_args, 'post_date/date' )
		) {
			$new_date_time = $this->get_post_date( $entry, $form );
			if ( $new_date_time ) {
				$post->post_date     = $new_date_time;
				$post->post_date_gmt = get_gmt_from_date( $new_date_time );
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

				$terms = explode( ',', is_object( $term_field ) ? trim( $term_field->get_value_export( $entry ) ) : '' );

				if ( ! empty( $terms ) && rgar( $term_field, 'gppa-choices-enabled' ) ) {
					$taxonomy = '';
					// Get the taxonomy from the field settings or the first choice object based on the field type.
					if ( $term_field instanceof GF_Field_Text ) {
						$taxonomy = str_replace( 'taxonomy_', '', rgars( $term_field, 'gppa-values-templates/value', '' ) );
					} elseif ( is_object( $term_field ) && ! empty( $term_field['choices'][0]['object']->taxonomy ) ) {
						$taxonomy = $term_field['choices'][0]['object']->taxonomy;
					} elseif ( is_object( $term_field ) && rgars( $term_field, 'gppa-choices-filter-groups/0/0/property' ) === 'taxonomy' ) {
						// When GravityView is activated, need to get the taxonomy from the filter groups.
						$taxonomy = $term_field['gppa-choices-filter-groups'][0][0]['value'];
					}

					// Taxonomy not found, skip.
					if ( empty( $taxonomy ) ) {
						continue;
					}

					foreach ( $terms as $key => $term ) {
						// Check if `$term` is a term name or id. If term name, get the term id.
						if ( ! is_numeric( $term ) ) {
							$term = term_exists( $term, $taxonomy );

							// If the term doesn't exist, remove it from the array.
							if ( ! $term ) {
								unset( $terms[ $key ] );
								continue;
							}

							$terms[ $key ] = $term['term_id'];
						}
					}

					// If the taxonomy is not hierarchical, we need to get the term names from the term ids.
					if( ! $this->gw_is_taxonomy_hierarchical( $taxonomy ) ) {
						$terms = $this->get_term_names_by_ids( $terms, $taxonomy );
					}

					wp_set_post_terms( $post->ID, $terms, $taxonomy );
				}
			}
		}

		if ( $this->_args['meta'] ) {
			$post->meta_input = $this->prepare_meta_input( $this->_args['meta'], $post->ID, $entry, $form );
		}

		// Ensure the fires after hooks is set to false, so that doesn't override some of the normal rendering - GF confirmation for instance.
		wp_update_post( $post, false, false );

	}

	/**
	 * Check if a taxonomy is hierarchical.
	 *
	 * @param $taxonomy
	 *
	 * @return bool
	 */
	function gw_is_taxonomy_hierarchical( $taxonomy ) {
		$taxonomy_object = get_taxonomy( $taxonomy );

		if ( ! $taxonomy_object ) {
			return false;
		}

		return $taxonomy_object->hierarchical;
	}

	/**
	 * Get term names by term IDs.
	 *
	 * @param $tag_ids
	 * @param string $taxonomy
	 *
	 * @return array
	 */
	function get_term_names_by_ids( $tag_ids, $taxonomy = 'post_tag' ) {
		$tag_ids = is_array( $tag_ids ) ? $tag_ids : array( $tag_ids );

		$tag_names = [];
		foreach ( $tag_ids as $tag_id ) {
			$tag = get_term( $tag_id, $taxonomy );
			if ( ! is_wp_error( $tag ) && $tag ) {
				$tag_names[] = $tag->name;
			}
		}

		return $tag_names;
	}


	/**
	 * @param $meta
	 * @param $post_id
	 * @param $entry
	 * @param $form
	 * @param $meta_input
	 * @param $group string|null Used to handle populating ACF fields within a group.
	 *
	 * @return array|mixed
	 */
	public function prepare_meta_input( $meta, $post_id, $entry, $form, $meta_input = array(), $group = null ) {

		foreach ( $meta as $key => $value ) {

			$append = false;

			if ( is_array( $value ) ) {
				if ( ! isset( $value['field_id'] ) ) {
					$meta_input = $this->prepare_meta_input( $value, $post_id, $entry, $form, $meta_input, $key );
					continue;
				} else {
					$append = rgar( $value, 'append', false );
					$value  = $value['field_id'];
				}
			}

			$field = GFAPI::get_field( $form, $value );
			if ( ! $field ) {
				continue;
			}

			$field_type = $field->get_input_type();
			$meta_value = rgar( $entry, $value );
			// Address input
			if ( $field_type == 'address' ) {
				$meta_value = $field->get_value_export( $entry, $value );
			}

			// Support mapping all checkboxes of a Checkbox field to a custom field.
			if ( $field_type === 'checkbox' ) {
				$meta_value = $field->get_value_export( $entry );
				if ( is_callable( 'acf_get_field' ) ) {
					$acf_field = acf_get_field( $key );
					if ( $acf_field ) {
						$meta_value = array_map( 'trim', explode( ',', $meta_value ) );
					}
				}
			}

			// Check for ACF image-like custom fields. Integration powered by GP Media Library. We use `acf_maybe_get_field()`
			// here which supports fetching fields within a group by combined key (e.g. "group_name_field_name" );
			$acf_field = is_callable( 'gp_media_library' ) ? $this->acf_get_field_object_by_name( $key, $group ) : false;
			if ( $acf_field && in_array( $acf_field['type'], array( 'image', 'file', 'gallery' ), true ) ) {
				$is_gallery = $acf_field['type'] === 'gallery';
				$meta_value = gp_media_library()->acf_get_field_value( 'id', $entry, $field, $is_gallery );
				if ( $meta_value && $is_gallery && $append ) {
					$current_value = get_field( $acf_field['key'], $post_id, false );
					if ( is_array( $current_value ) ) {
						$meta_value = array_unique( array_merge( $meta_value, $current_value ) );
					}
				}
			}

			if ( $group ) {
				$key = sprintf( '%s_%s', $group, $key );
			}

			if ( ! rgblank( $meta_value ) ) {
				$acf_field = $this->acf_get_field_object_by_name( $key, $group );
				if ( $acf_field ) {
					if (
						( $acf_field['type'] === 'relationship' || ( $acf_field['type'] === 'post_object' && ! empty( $acf_field['multiple'] ) ) )
						&& ! is_array( $meta_value )
					) {
						$meta_value = array_filter( array_map( 'intval', array_map( 'trim', explode( ',', (string) $meta_value ) ) ) );
					}
					$meta_value = $acf_field['type'] == 'google_map' && $field_type == 'address' ? array(
						'address' => $meta_value,
						'lat'     => rgar( $entry, "gpaa_lat_{$field->id}" ),
						'lng'     => rgar( $entry, "gpaa_lng_{$field->id}" ),
					) : $meta_value;
					update_field( $key, $meta_value, $post_id );
				} else {
					$meta_input[ $key ] = $meta_value;
				}
			} elseif ( $this->_args['delete_if_empty'] ) {
				delete_post_meta( $post_id, $key );
			}
		}

		return $meta_input;
	}

	function acf_get_field_object_by_name( $field_name, $group_name = false ) {

		if ( ! is_callable( 'acf_get_field' ) ) {
			return null;
		}

		if ( ! $group_name ) {
			return acf_get_field( $field_name );
		}

		$group = acf_get_field( $group_name );

		return acf_get_field( $field_name, $group['ID'] );
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
		// Check if this is for the specific form we want.
		if ( rgar( $field, 'formId' ) != $this->_args['form_id'] ) {
			return $value;
		}

		// Don't want to return IDs for post objects used in the populates field dynamically using GPPA.
		if ( rgar( $field, 'gppa-values-enabled' ) === true  && rgar( $field, 'gppa-values-object-type' ) === 'post' ) {
			return $value;
		}

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
			$post_date['time'] = rgar( $this->_args['post_date'], 'time' );
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
