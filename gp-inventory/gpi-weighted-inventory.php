<?php
/**
 * Gravity Perks // Inventory // Weighted Inventory
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Apply a custom inventory weight to a field such that it consumes additional inventory per requested item.
 *
 * For example, Product A is for single tickets. Product B is for a group of 8 tickets. Both share the same Tickets
 * resource. Any quantity of Product B will subtract 8 from the inventory instead of 1.
 *
 * Known Limitations
 *
 * - Only works with Shared Inventory resources.
 * - Can only be applied to a single field in a resource.
 * - Does not work with the available inventory message.
 *
 * Plugin Name:  GP Inventory — Weighted Inventory
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-inventory/
 * Description:  Apply a custom inventory weight to a field such that it consumes additional inventory per requested item.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class GPI_Weighted_Inventory {

	public function __construct( $args = array() ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'  => false,
			'field_id' => false,
			'weight'   => false,
		) );

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		add_filter( 'gpi_claimed_inventory', array( $this, 'apply_weights_to_claimed_inventory' ), 10, 2 );
		add_filter( 'gpi_requested_quantity', array( $this, 'apply_weights_requested_quantity' ), 10, 3 );
		add_filter( 'gpi_is_in_stock', array( $this, 'is_in_stock' ), 10, 3 );

	}

	public function apply_weights_to_claimed_inventory( $claimed, $field ) {
		global $wpdb;

		if ( ! $this->is_applicable_form( $field->formId ) ) {
			return $claimed;
		}

		$resource_id     = rgar( $field, 'gpiResource' );
		$resource_fields = gp_inventory_type_advanced()->get_resource_fields( $resource_id );

		if ( empty( $resource_fields ) || ! $this->has_weighted_field( $resource_fields ) ) {
			return $claimed;
		}

		$claimed = 0;

		foreach ( $resource_fields as $resource_field ) {
			$weight = 1;
			if ( $this->is_applicable_field( $resource_field ) ) {
				$weight = $this->_args['weight'];
			}
			$field_sum = (int) $wpdb->get_var( implode( "\n", gp_inventory_type_advanced()->get_claimed_inventory_query( $resource_field ) ) );
			$claimed  += $field_sum * $weight;
		}

		return $claimed;
	}

	public function apply_weights_requested_quantity( $quantity, $field, $form ) {
		if ( $this->is_applicable_field( $field ) && (int) $quantity > 0 ) {
			$quantity *= $this->_args['weight'];
		}
		return $quantity;
	}

	public function is_in_stock( $is_in_stock, $field, $available_stock ) {
		if ( $this->is_applicable_field( $field ) && $available_stock < $this->_args['weight'] ) {
			$is_in_stock = false;
		}
		return $is_in_stock;
	}

	public function has_weighted_field( $resource_fields ) {
		foreach ( $resource_fields as $resource_field ) {
			if ( $this->is_applicable_field( $resource_field ) ) {
				return true;
			}
		}
		return false;
	}

	public function is_applicable_field( $field ) {
		return $this->is_applicable_form( $field->formId ) && $field->id === (int) $this->_args['field_id'];
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Configuration

new GPI_Weighted_Inventory( array(
	'form_id'  => 123,
	'field_id' => 4,
	'weight'   => 5,
) );
