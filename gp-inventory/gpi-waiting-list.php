<?php
/**
 * Gravity Perks // Inventory // Waiting List for Exhausted Choices
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Instruction Video: https://www.loom.com/share/7a5e57ec14404b9080c5e9b9878e2ecc
 *
 * Replace the default available inventory message with a waiting list message when a product's (or choice's) inventory
 * is exhausted.
 *
 * Choices on the waiting list will remain selectable and submittable. Non-choice product fields will remain available
 * rather than being removed
 */
class GPI_Waiting_List {
	public $waitlist_message = '(waiting list)';

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
		) );

		add_action( 'init', array( $this, 'add_hooks' ), 20 ); // Wait for GPI to be instantiated
	}

	public function add_hooks() {
		if ( ! function_exists( 'gp_inventory_type_advanced' ) || ! function_exists( 'gp_inventory_type_simple' ) ) {
			return;
		}

		add_filter( 'gform_entry_post_save', array( $this, 'add_entry_meta' ), 10, 2 );

		/*
		 * Choice-based hooks
		 */
		add_filter( 'gpi_disable_choices', '__return_false' );
		add_filter( 'gpi_remove_choices', '__return_false' );

		add_filter( 'gpi_pre_render_choice', array( $this, 'pre_render_choice' ), 10, 5 );

		// Add support for showing waiting list message in confirmations and notifications.
		add_filter( 'gform_pre_submission_filter', array( $this, 'add_waitlist_message_to_choices_on_submission' ), 10, 2 );

		add_filter( 'gform_entries_field_value', array( $this, 'entries_field_value_with_waitlist_message' ), 10, 4 );
		add_filter( 'gform_entry_field_value', array( $this, 'add_waitlist_message_to_entry_value' ), 10, 4 );

		/**
		 * Single products
		 */
		// Mark item as waitlisted during submission for other processes such as saving meta
		add_filter( 'gform_pre_submission_filter', array( $this, 'add_waiting_list_to_single_product' ) );

		// Prevent validation of inventory
		remove_filter( 'gform_validation', array( gp_inventory_type_advanced(), 'validation' ) );
		remove_filter( 'gform_validation', array( gp_inventory_type_simple(), 'validation' ) );

		// Remove locking out single products.
		add_filter( 'gform_field_input', array( $this, 'remove_gform_field_input_filters' ), 15, 2 );

		add_filter( 'gform_pre_render', array( $this, 'add_waiting_list_to_single_product' ) );
	}

	public function is_applicable_form( $form ) {
		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

	public function is_applicable_field( $field ) {
		$field_id = isset( $field['id'] ) ? $field['id'] : $field;

		return empty( $this->_args['field_id'] ) || $this->_args['field_id'] == $field_id;
	}

	public function remove_gform_field_input_filters( $return, $field ) {
		if ( $this->is_applicable_form( $field->formId ) && $this->is_applicable_field( $field ) ) {
			remove_filter( "gform_field_input_{$field->formId}_{$field->id}", array(
				gp_inventory_type_simple(),
				'hide_field',
			) );

			remove_filter( "gform_field_input_{$field->formId}_{$field->id}", array(
				gp_inventory_type_advanced(),
				'hide_field',
			) );
		}

		return $return;
	}

	public function entries_field_value_with_waitlist_message( $value, $form_id, $field_id, $entry ) {
		$form  = GFAPI::get_form( $form_id );
		$field = GFAPI::get_field( $form, $field_id );

		if ( $this->is_applicable_form( $form ) && $this->is_applicable_field( $field ) ) {
			$value = $this->add_waitlist_message_to_entry_value( $value, $field, $entry, $form );
		}

		return $value;
	}

	public function pre_render_choice( $choice, $exceeded_limit, $field, $form, $count ) {
		if ( $this->is_applicable_form( $form ) && $this->is_applicable_field( $field ) ) {
			$limit         = (int) rgar( $choice, 'inventory_limit' );
			$how_many_left = max( $limit - $count, 0 );

			if ( $how_many_left <= 0 ) {
				$choice                 = $this->apply_waitlist_message_to_choice( $choice, $field, $form, $how_many_left );
				$choice['isWaitlisted'] = true;
			}
		}
		return $choice;
	}

	public function apply_waitlist_message_to_choice( $choice, $field, $form, $how_many_left = false ) {
		if ( $this->is_applicable_form( $form ) && $this->is_applicable_field( $field ) ) {
			$message         = $this->waitlist_message;
			$default_message = gp_inventory_type_choices()->replace_choice_available_inventory_merge_tags( gp_inventory_type_choices()->get_inventory_available_message( $field ), $field, $form, $choice, $how_many_left );
			if ( strpos( $choice['text'], $default_message ) === false ) {
				$choice['text'] .= ' ' . $message;
			} else {
				$choice['text'] = str_replace( $default_message, $message, $choice['text'] );
			}
		}

		return $choice;
	}

	public function add_waitlist_message_to_entry_value( $value, $field, $entry, $form ) {
		if ( ! $this->is_applicable_form( $form ) || ! $this->is_applicable_field( $field ) ) {
			return $value;
		}

		if ( gp_inventory_type_choices()->is_applicable_field( $field ) ) {
			foreach ( $field->choices as $choice ) {
				if ( $choice['text'] != $value ) {
					continue;
				}

				$is_waitlisted = gform_get_meta( $entry['id'], sprintf( 'gpi_is_waitlisted_%d_%s', $field->id, sanitize_title( $choice['value'] ) ) );

				if ( $is_waitlisted ) {
					$choice = $this->apply_waitlist_message_to_choice( $choice, $field, $form );
					$value  = $choice['text'];
				}
			}
		}

		if ( gp_inventory_type_simple()->is_applicable_field( $field ) || gp_inventory_type_advanced()->is_applicable_field( $field ) ) {
			$is_waitlisted = gform_get_meta( $entry['id'], sprintf( 'gpi_is_waitlisted_%d', $field->id ) );

			if ( $is_waitlisted ) {
				$value .= ' ' . $this->waitlist_message;
			}
		}

		return $value;
	}

	public function add_entry_meta( $entry, $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $entry;
		}

		foreach ( $form['fields'] as $field ) {
			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			// Checks all inventory types
			if ( ! gp_inventory_conditional_logic()->is_applicable_field( $field ) ) {
				continue;
			}

			if ( gp_inventory_type_choices()->is_applicable_field( $field ) ) {
				foreach ( $field->choices as $choice ) {
					if ( rgar( $choice, 'isWaitlisted' ) ) {
						gform_add_meta( $entry['id'], sprintf( 'gpi_is_waitlisted_%d_%s', $field->id, sanitize_title( $choice['value'] ) ), true );
					}
				}
			}

			if ( gp_inventory_type_simple()->is_applicable_field( $field ) || gp_inventory_type_advanced()->is_applicable_field( $field ) ) {
				if ( rgar( $field, 'isWaitlisted' ) ) {
					gform_add_meta( $entry['id'], sprintf( 'gpi_is_waitlisted_%d', $field->id ), true );
				}
			}
		}

		return $entry;
	}

	public function add_waitlist_message_to_choices_on_submission( $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			// Checks all inventory types
			if ( ! gp_inventory_conditional_logic()->is_applicable_field( $field ) ) {
				continue;
			}

			if ( gp_inventory_type_choices()->is_applicable_field( $field ) ) {
				$choice_counts = gp_inventory_type_choices()->get_choice_counts( $form['id'], $field );
				$choices       = $field['choices'];

				foreach ( $choices as &$choice ) {
					$value        = $field->sanitize_entry_value( $choice['value'], $form['id'] );
					$choice_count = intval( rgar( $choice_counts, $value ) );
					$choice       = gf_apply_filters( array(
						'gpi_pre_render_choice',
						$form['id'],
						$field->id,
					), $choice, null, $field, $form, $choice_count );
				}

				$field['choices'] = $choices;
			}
		}

		return $form;
	}

	public function add_waiting_list_to_single_product( $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $form;
		}

		foreach ( $form['fields'] as &$field ) {
			if ( ! $this->is_applicable_field( $field ) ) {
				continue;
			}

			if ( ! gp_inventory_type_simple()->is_applicable_field( $field ) && ! gp_inventory_type_advanced()->is_applicable_field( $field ) ) {
				continue;
			}

			$gpi_instance = gp_inventory_type_simple()::$type === rgar( $field, 'gpiInventory' ) ? gp_inventory_type_simple() : gp_inventory_type_advanced();

			$available = (int) $gpi_instance->get_available_stock( $field );

			if ( $available <= 0 ) {
				$message             = $this->waitlist_message;
				$field->description  = '<div class="gpi-available-inventory-message" style="padding-bottom: 13px;">' . $message . '</div>' . $field->description;
				$field->isWaitlisted = true;
			}
		}

		return $form;
	}

}

new GPI_Waiting_List();

/**
 * @todo Add support for searching for entries that contain items from any waitlist or a product/choice-specific waitlist.
 */
//add_filter( 'gform_field_filters', function( $field_filters, $form ) {
//	$field_filters[] = array(
//		'text' => 'Waiting List',
//		'operators' => array( 'is', 'isnot' ),
//		'key' => 'gpi_waiting_list',
//		'preventMultiple' => false,
//		'values' => array(
//			array(
//				'text' => 'Any Waitlist',
//				'value' => 'NOTNULL',
//			),
//		)
//	);
//	return $field_filters;
//}, 10, 2 );
