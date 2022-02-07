<?php
/**
 * Gravity Wiz // WooCommerce Gravity Forms Product Add-on // Remove Fields From Product Description
 * https://gravitywiz.com
 *
 * WooCommerce Gravity Forms Add-on: Add support for removing a field from the product description in the cart.
 *
 * Plugin Name:  WooCommerce Gravity Forms Product Add-on - Remove Fields From Product Description
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Add support for removing a field from the product description in the cart.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */
class WCGFPA_Remove_Field_From_Product_Description {

	public function __construct() {

		add_action( 'gform_field_advanced_settings', array( $this, 'field_settings_ui' ), 10, 2 );
		add_action( 'gform_editor_js', array( $this, 'field_settings_js' ) );

		add_filter( 'woocommerce_get_item_data', array( $this, 'modify_item_data' ), 11, 2 );
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'delete_order_item_meta' ), 11, 2 );

	}

	public function modify_item_data( $other_data, $cart_item ) {

		$form_id = rgars( $cart_item, '_gravity_form_data/id' );
		if ( ! $form_id ) {
			return $other_data;
		}

		$form = GFFormsModel::get_form_meta( $form_id );

		foreach ( $form['fields'] as $field ) {

			if ( ! rgar( $field, 'wgfrfEnable' ) ) {
				continue;
			}

			// reindex array for next loop
			$other_data = array_values( $other_data );

			for ( $i = count( $other_data ) - 1; $i >= 0; $i-- ) {
				if ( $other_data[ $i ]['name'] == GFCommon::get_label( $field ) ) {
					unset( $other_data[ $i ] );
				}
			}
		}

		return $other_data;
	}

	public function delete_order_item_meta( $item_id, $cart_item ) {

		$form_id = rgars( $cart_item, '_gravity_form_data/id' );
		if ( ! $form_id ) {
			return;
		}

		$form = GFFormsModel::get_form_meta( $form_id );

		foreach ( $form['fields'] as $field ) {

			if ( ! rgar( $field, 'wgfrfEnable' ) ) {
				continue;
			}

			woocommerce_delete_order_item_meta( $item_id, GFCommon::get_label( $field ) );

		}

	}

	public function field_settings_ui( $position ) {

		if ( $position != 450 ) {
			return;
		}

		?>

		<li class="wgfrf-enable-setting field_setting">
			<input type="checkbox" id="wgfrf-enable" value="1" onclick="SetFieldProperty( 'wgfrfEnable', this.checked )">
			<label class="inline" for="wgfrf-enable">
				<?php _e( 'Remove This Field From WooCommerce Cart Item Description' ); ?>
			</label>
		</li>

		<?php
	}

	public function field_settings_js() {
		?>

		<script type="text/javascript">
			(function($) {

				$(document).bind('gform_load_field_settings', function(event, field, form) {
					$("#wgfrf-enable").attr( 'checked', field.wgfrfEnable == true );
				});
				
				for( inputType in fieldSettings ) {
					if( fieldSettings.hasOwnProperty( inputType ) )
						fieldSettings[inputType] += ', .wgfrf-enable-setting';
				}

			})(jQuery);
		</script>

		<?php
	}

}

new WCGFPA_Remove_Field_From_Product_Description();
