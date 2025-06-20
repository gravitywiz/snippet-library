<?php
/**
 * Gravity Wiz // Gravity Forms // Product Quantity Conditional
 *
 * Instruction Video: https://www.loom.com/share/38547a28853f4706b10cfdb42eaa45ca
 *
 * Adds quantity field options to conditional logic for Single Product fields.
 *
 * Plugin Name:  GF Product Quantity Conditional
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Adds quantity field options to conditional logic for Single Product fields that don't have dedicated quantity fields
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
class GW_Product_Quantity_Conditional {

	private static $_instance = null;

	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self;
		}
		return self::$_instance;
	}

	public function __construct() {

		add_action( 'admin_init', array( $this, 'init' ) );

	}

	public function init() {

		add_action( 'admin_print_scripts', array( $this, 'enqueue_admin_scripts' ) );

	}

	public function enqueue_admin_scripts() {

		if ( ! $this->is_gravity_forms_page() ) {
			return;
		}

		?>
		<script type="text/javascript">
			window.gwProductQuantityConditional = {
				
				// Check if product has dedicated quantity field
				productHasQuantityField: function(fieldId, form) {
					for(var i = 0; i < form.fields.length; i++) {
						var field = form.fields[i];
						if(field.type == 'quantity' && field.productId == fieldId) {
							return true;
						}
					}
					return false;
				},
				
				// Check if field is a custom quantity field
				isCustomQtyField: function(fieldId) {
					return typeof fieldId === 'string' && fieldId.indexOf('quantity_') === 0;
				},
				
				// Get the product field ID from a quantity field ID
				getCustomQtyFieldId: function(fieldId) {
					return fieldId.replace('quantity_', '');
				},

				isQtyField: function(fieldId) {
					var field = GetFieldById(fieldId);
					return field && field.type == 'quantity';
				}
				
			};

			gform.addFilter('gform_conditional_logic_fields', function(options, form, selectedFieldId) {
				for(var i = 0; i < form.fields.length; i++) {
					var field = form.fields[i];
					
					if(field.type != 'product' || 
					   field.inputType != 'singleproduct' || 
					   gwProductQuantityConditional.productHasQuantityField(field.id, form) || 
					   field.disableQuantity) {
						continue;
					}

					options.push({
						label: (field.adminLabel ? field.adminLabel : field.label) + ' (Quantity)',
						value: field.id,
					});
				}
				return options;
			});
		</script>
		<?php
	}

	public function is_gravity_forms_page() {
		return method_exists( 'GFForms', 'is_gravity_page' ) && GFForms::is_gravity_page();
	}

}

new GW_Product_Quantity_Conditional();
