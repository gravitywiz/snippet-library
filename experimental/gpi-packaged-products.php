<?php
/**
 * Gravity Perks // Inventory // Packaged Products
 * https://gravitywiz.com/documentation/gravity-forms-inventory/
 *
 * Add dependencies between packaged products and products that are individually in the product. As packages are
 * bought, the individual items available inventory will also decrease by that much. If the available inventory of an
 * individual product is below the inventory of a package, it should use the lowest inventory amount amongst the packaged
 * fields.
 *
 * Known Limitations:
 *    - The package field and fields in the package must be using the Advanced Inventory type and have their own resources.
 *
 * Instructions:
 *    - Install using instructions here: https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *    - Update the configuration at the bottom of the snippet accordingly.
 */
class GP_Inventory_Packaged_Products {
    /** @var array */
    private $_args;

	/** @var int */
	public $form_id;

	/** @var GF_Field */
	public $package_field;

	/** @var int[] */
	public $field_ids_in_package = array();

	/** @var GF_Field[] */
	public $fields_in_package = array();

	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args( $args, array(
			'form_id'              => null,
			'package_field_id'     => null,
			'field_ids_in_package' => array(),
		) );

		$this->form_id              = $this->_args['form_id'];
		$this->package_field        = GFAPI::get_field( $this->form_id, $this->_args['package_field_id'] );
		$this->field_ids_in_package = $this->_args['field_ids_in_package'];

		foreach ( $this->field_ids_in_package as $field_id_in_package ) {
			$this->fields_in_package[] = GFAPI::get_field( $this->form_id, $field_id_in_package );
		}

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gpi_claimed_inventory_' . $this->form_id, array(
			$this,
			'package_field_claimed_inventory'
		), 10, 2 );

		add_filter( 'gpi_claimed_inventory_' . $this->form_id, array(
			$this,
			'add_claimed_package_inventory_to_individual_products'
		), 10, 2 );

		add_filter( 'gpi_requested_quantity_' . $this->form_id, array(
			$this,
			'add_requested_packages_to_individual_products'
		), 10, 2 );
	}

	/**
	 * Add claimed inventory of packaged items to packages if their inventory is below packages.
	 */
	public function package_field_claimed_inventory( $package_claimed_inventory, $field ) {
	    if ( $field->id !== $this->package_field->id ) {
	        return $package_claimed_inventory;
        }

		remove_filter( 'gpi_claimed_inventory_' . $this->form_id, array(
			$this,
			'add_claimed_package_inventory_to_individual_products'
		) );

		$inventory_limit = gp_inventory_type_advanced()->get_stock_quantity( $this->package_field );
	    $package_available_inventory = $inventory_limit - $package_claimed_inventory;
	    $packaged_item_available_amounts = array();

	    foreach ( $this->fields_in_package as $field_in_package ) {
	        $packaged_item_available_amount = gp_inventory_type_advanced()->get_available_stock( $field_in_package ) - $package_claimed_inventory;
		    $packaged_item_available_amounts[] = $packaged_item_available_amount;
        }

	    $lowest_package_item_available_amount = min( $packaged_item_available_amounts );

	    if ( $lowest_package_item_available_amount < $package_available_inventory ) {
		    $difference = $package_available_inventory - $lowest_package_item_available_amount;
		    $package_claimed_inventory = $package_claimed_inventory + $difference;
        }

		add_filter( 'gpi_claimed_inventory_' . $this->form_id, array(
			$this,
			'add_claimed_package_inventory_to_individual_products'
		), 10, 2 );

		return $package_claimed_inventory;
	}

	/**
	 * Add claimed inventory of packages to packaged items.
	 */
	public function add_claimed_package_inventory_to_individual_products( $claimed_inventory, $field ) {
		if ( ! in_array( $field->id, $this->field_ids_in_package ) ) {
			return $claimed_inventory;
		}

		remove_filter( 'gpi_claimed_inventory_' . $this->form_id, array(
			$this,
			'package_field_claimed_inventory'
		) );

		$claimed_packages_inventory = gp_inventory_type_advanced()->get_claimed_inventory( $this->package_field );

		add_filter( 'gpi_claimed_inventory_' . $this->form_id, array(
			$this,
			'package_field_claimed_inventory'
		), 10, 2 );

		return (int) $claimed_inventory + (int) $claimed_packages_inventory;
	}

	/**
	 * Add requested quantity of packages to packaged items.
	 */
	public function add_requested_packages_to_individual_products( $requested_quantity, $field ) {
		if ( ! in_array( $field->id, $this->field_ids_in_package ) ) {
			return $requested_quantity;
		}

		$requested_packages_qty = rgpost( 'input_' . $this->package_field->id . '_3' );

		return (int) $requested_quantity + (int) $requested_packages_qty;
	}

}

/**
 * Configuration
 */
new GP_Inventory_Packaged_Products( array(
	'form_id'              => 2,
	'package_field_id'     => 4,
	'field_ids_in_package' => array( 1, 2 )
) );
