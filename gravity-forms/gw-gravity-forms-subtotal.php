<?php
/**
 * Gravity Wiz // Gravity Forms // Calculation Subtotal Merge Tag
 * http://gravitywiz.com/subtotal-merge-tag-for-calculations/
 * 
 * Adds a {subtotal} merge tag which calculates the subtotal of the form.
 *
 * This merge tag can only be used within the "Formula" setting of Calculation-enabled fields (i.e. Number, Calculated Product).
 *
 * Plugin Name:  Gravity Forms Subtotal Merge Tag
 * Plugin URI:   https://gravitywiz.com/subtotal-merge-tag-for-calculations/
 * Description:  Adds a {subtotal} merge tag which calculates the subtotal of the form.
 * Author:       Gravity Wiz
 * Version:      1.3
 * Author URI:   http://gravitywiz.com
 */
class GWCalcSubtotal {

	public static $merge_tag = '{subtotal}';

	function __construct() {

		// front-end
		add_filter( 'gform_pre_render', array( $this, 'maybe_replace_subtotal_merge_tag' ) );
		add_filter( 'gform_pre_validation', array( $this, 'maybe_replace_subtotal_merge_tag_submission' ) );

		// back-end
		add_filter( 'gform_admin_pre_render', array( $this, 'add_merge_tags' ) );

	}

	/**
	 * Look for {subtotal} merge tag in form fields 'calculationFormula' property. If found, replace with the
	 * aggregated subtotal merge tag string.
	 *
	 * @param mixed $form
	 */
	function maybe_replace_subtotal_merge_tag( $form, $filter_tags = false ) {

		foreach( $form['fields'] as &$field ) {

			if( current_filter() == 'gform_pre_render' && rgar( $field, 'origCalculationFormula' ) )
				$field['calculationFormula'] = $field['origCalculationFormula'];

			if( ! self::has_subtotal_merge_tag( $field ) )
				continue;

			$subtotal_merge_tags = self::get_subtotal_merge_tag_string( $form, $field, $filter_tags );
			$field['origCalculationFormula'] = $field['calculationFormula'];
			$field['calculationFormula'] = str_replace( self::$merge_tag, $subtotal_merge_tags, $field['calculationFormula'] );

		}

		return $form;
	}

	function maybe_replace_subtotal_merge_tag_submission( $form ) {
		return $this->maybe_replace_subtotal_merge_tag( $form, true );
	}

	/**
	 * Get all the pricing fields on the form, get their corresponding merge tags and aggregate them into a formula that
	 * will yeild the form's subtotal.
	 *
	 * @param mixed $form
	 */
	static function get_subtotal_merge_tag_string( $form, $current_field, $filter_tags = false ) {

		$pricing_fields = self::get_pricing_fields( $form );
		$product_tag_groups = array();

		foreach( $pricing_fields['products'] as $product ) {

			$product_field = rgar( $product, 'product' );
			$option_fields = rgar( $product, 'options' );
			$quantity_field = rgar( $product, 'quantity' );

			// do not include current field in subtotal
			if( $product_field['id'] == $current_field['id'] )
				continue;

			$product_tags = GFCommon::get_field_merge_tags( $product_field );
			$quantity_tag = 1;

			// if a single product type, only get the "price" merge tag
			if( in_array( GFFormsModel::get_input_type( $product_field ), array( 'singleproduct', 'calculation', 'hiddenproduct' ) ) ) {

				// single products provide quantity merge tag
				if( empty( $quantity_field ) && ! rgar( $product_field, 'disableQuantity' ) )
					$quantity_tag = $product_tags[2]['tag'];

				$product_tags = array( $product_tags[1] );
			}

			// if quantity field is provided for product, get merge tag
			if( ! empty( $quantity_field ) ) {
				$quantity_tag = GFCommon::get_field_merge_tags( $quantity_field );
				$quantity_tag = $quantity_tag[0]['tag'];
			}

			if( $filter_tags && ! self::has_valid_quantity( $quantity_tag ) )
				continue;

			$product_tags = wp_list_pluck( $product_tags, 'tag' );
			$option_tags = array();

			foreach( $option_fields as $option_field ) {

				if( is_array( $option_field['inputs'] ) ) {

					$choice_number = 1;
					$inputs = $option_field->inputs;

					foreach( $inputs as &$input ) {

						//hack to skip numbers ending in 0. so that 5.1 doesn't conflict with 5.10
						if( $choice_number % 10 == 0 )
							$choice_number++;

						$input['id'] = $option_field['id'] . '.' . $choice_number++;

					}

					$option_field->inputs = $inputs;

				}

				$new_options_tags = GFCommon::get_field_merge_tags( $option_field );
				if( ! is_array( $new_options_tags ) )
					continue;

				if( GFFormsModel::get_input_type( $option_field ) == 'checkbox' )
					array_shift( $new_options_tags );

				$option_tags = array_merge( $option_tags, $new_options_tags );
			}

			$option_tags = wp_list_pluck( $option_tags, 'tag' );

			$product_tag_groups[] = '( ( ' . implode( ' + ', array_merge( $product_tags, $option_tags ) ) . ' ) * ' . $quantity_tag . ' )';

		}

		$shipping_tag = 0;
		/* Shipping should not be included in subtotal, correct?
		if( rgar( $pricing_fields, 'shipping' ) ) {
			$shipping_tag = GFCommon::get_field_merge_tags( rgars( $pricing_fields, 'shipping/0' ) );
			$shipping_tag = $shipping_tag[0]['tag'];
		}*/

		$pricing_tag_string = '( ( ' . implode( ' + ', $product_tag_groups ) . ' ) + ' . $shipping_tag . ' )';

		return $pricing_tag_string;
	}

	/**
	 * Get all pricing fields from a given form object grouped by product and shipping with options nested under their
	 * respective products.
	 *
	 * @param mixed $form
	 */
	static function get_pricing_fields( $form ) {

		$product_fields = array();

		foreach( $form["fields"] as $field ) {

			if( $field["type"] != 'product' )
				continue;

			$option_fields = GFCommon::get_product_fields_by_type($form, array("option"), $field['id'] );

			// can only have 1 quantity field
			$quantity_field = GFCommon::get_product_fields_by_type( $form, array("quantity"), $field['id'] );
			$quantity_field = rgar( $quantity_field, 0 );

			$product_fields[] = array(
				'product' => $field,
				'options' => $option_fields,
				'quantity' => $quantity_field
			);

		}

		$shipping_field = GFCommon::get_fields_by_type($form, array("shipping"));

		return array( "products" => $product_fields, "shipping" => $shipping_field );
	}

	static function has_valid_quantity( $quantity_tag ) {

		if( is_numeric( $quantity_tag ) ) {

			$qty_value = $quantity_tag;

		} else {

			// extract qty input ID from the merge tag
			preg_match_all( '/{[^{]*?:(\d+(\.\d+)?)(:(.*?))?}/mi', $quantity_tag, $matches, PREG_SET_ORDER );
			$qty_input_id = rgars( $matches, '0/1' );
			$qty_value = rgpost( 'input_' . str_replace( '.', '_', $qty_input_id ) );

		}

		return floatval( $qty_value ) > 0;
	}

	function add_merge_tags( $form ) {

		$label = __('Subtotal', 'gravityforms');

		?>

		<script type="text/javascript">

			// for the future (not yet supported for calc field)
			gform.addFilter("gform_merge_tags", "gwcs_add_merge_tags");
			function gwcs_add_merge_tags( mergeTags, elementId, hideAllFields, excludeFieldTypes, isPrepop, option ) {
				mergeTags["pricing"].tags.push({ tag: '<?php echo self::$merge_tag; ?>', label: '<?php echo $label; ?>' });
				return mergeTags;
			}

			// hacky, but only temporary
			jQuery(document).ready(function($){

				var calcMergeTagSelect = $('#field_calculation_formula_variable_select');
				calcMergeTagSelect.find('optgroup').eq(0).append( '<option value="<?php echo self::$merge_tag; ?>"><?php echo $label; ?></option>' );

			});

		</script>

		<?php
		//return the form object from the php hook
		return $form;
	}

	static function has_subtotal_merge_tag( $field ) {

		// check if form is passed
		if( isset( $field['fields'] ) && is_array( $field['fields'] ) ) {

			$form = $field;
			foreach( $form['fields'] as $field ) {
				if( self::has_subtotal_merge_tag( $field ) )
					return true;
			}

		} else {

			if( isset( $field['calculationFormula'] ) && strpos( $field['calculationFormula'], self::$merge_tag ) !== false )
				return true;

		}

		return false;
	}

}

new GWCalcSubtotal();
