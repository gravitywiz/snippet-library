<?php
/**
 * Gravity Perks // GP eCommerce Fields // Tax Inclusive Order Summary Markup
 * https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * Displays the order summary with tax inclusive calculations.
 *
 * Plugin Name:  GP eCommerce Fields - Tax Inclusive Order Summary Markup
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 * Description:  Displays the order summary with tax inclusive calculations.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
class GW_Tax_Inclusive_Order_Summary {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'            => false,
			'tax_label'          => '',
			'tax_rate'           => 0,
			'apply_to_all_forms' => false,
		) );

		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {

		if ( ! function_exists( 'gp_ecommerce_fields' ) ) {
			return;
		}

		add_filter( 'gpecf_order_summary', array( $this, 'insert_custom_tax' ), 10, 3 );
		add_filter( 'gpecf_order_sumary_markup', array( $this, 'get_custom_order_summary' ), 10, 6 );

	}

	function insert_custom_tax( $order_summary, $form, $entry ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return $order_summary;
		}

		$tax_incl['tax_incl'] = array(
			array(
				'name'     => $this->_args['tax_label'],
				'price'    => 0,
				'quantity' => 1,
				'class'    => 'incltax',
			),
		);
		array_splice( $order_summary, 1, 0, $tax_incl );
		return $order_summary;
	}

	function get_custom_order_summary( $markup, $order, $form, $entry, $order_summary, $labels ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return $markup;
		}
		ob_start();
		?>

		<table class="gpecf-order-summary" cellspacing="0" width="100%" style="<?php gp_ecommerce_fields()->style( '.order-summary' ); ?>">
			<thead>
			<tr>
				<th scope="col" style="<?php gp_ecommerce_fields()->style( '.order-summary/thead/th.column-1' ); ?>"><?php echo $labels['product']; ?></th>
				<th scope="col" style="<?php gp_ecommerce_fields()->style( '.order-summary/thead/th.column-2' ); ?>"><?php echo $labels['quantity']; ?></th>
				<th scope="col" style="<?php gp_ecommerce_fields()->style( '.order-summary/thead/th.column-3' ); ?>"><?php echo $labels['unit_price']; ?></th>
				<th scope="col" style="<?php gp_ecommerce_fields()->style( '.order-summary/thead/th.column-4' ); ?>"><?php echo $labels['price']; ?></th>
			</tr>
			</thead>
			<tbody>
			<?php
			$tax            = 0;
			$total_excl_tax = 0;

			foreach ( $order['products'] as $product ) :
				if ( empty( $product['name'] ) || gp_ecommerce_fields()->is_ecommerce_product( $product ) ) {
					continue;
				}
				?>
				<tr style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr' ); ?>">
					<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr/td.column-1' ); ?>">
						<div style="<?php gp_ecommerce_fields()->style( '.order-summary/.custom-product' ); ?>">
							<?php echo esc_html( $product['name'] ); ?>
						</div>
						<ul style="<?php gp_ecommerce_fields()->style( '.order-summary/.product-options' ); ?>">
							<?php
							$price = GFCommon::to_number( $product['price'] );
							if ( is_array( rgar( $product, 'options' ) ) ) :
								foreach ( $product['options'] as $index => $option ) :
									$price += GFCommon::to_number( $option['price'] );
									$class  = $index == count( $product['options'] ) - 1 ? '.last-child' : '';
									?>
									<li style="<?php gp_ecommerce_fields()->style( ".order-summary/.product-options/li{$class}" ); ?>"><?php echo $option['option_label']; ?></li>
									<?php
								endforeach;
							endif;
							$price_excl_tax  = ( 100 * $price ) / ( 100 + $this->_args['tax_rate'] );
							$field_total     = floatval( $product['quantity'] ) * $price_excl_tax;
							$tax            += ( ( $price * $this->_args['tax_rate'] ) / ( 100 + $this->_args['tax_rate'] ) ) * floatval( $product['quantity'] );
							$total_excl_tax += $field_total;
							?>
						</ul>
					</td>
					<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr/td.column-2' ); ?>"><?php echo esc_html( $product['quantity'] ); ?></td>
					<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr/td.column-3' ); ?>"><?php echo GFCommon::to_money( $price_excl_tax, $entry['currency'] ); ?></td>
					<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr/td.column-4' ); ?>"><?php echo GFCommon::to_money( $field_total, $entry['currency'] ); ?></td>
				</tr>
				<?php
			endforeach;
			?>
			</tbody>
			<tfoot style="<?php gp_ecommerce_fields()->style( '.order-summary/tfoot' ); ?>">
			<?php foreach ( gp_ecommerce_fields()->get_order_summary( $order, $form, $entry ) as $index => $group ) : ?>
				<?php
				foreach ( $group as $item ) :
					$class = rgar( $item, 'class' ) ? '.' . rgar( $item, 'class' ) : '';
					?>
					<tr style="<?php gp_ecommerce_fields()->style( '.order-summary/tfoot/tr' . $class ); ?>">
						<?php if ( $index === 0 ) : ?>
							<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tfoot/tr/td.empty' ); ?>" colspan="2" rowspan="<?php echo gp_ecommerce_fields()->get_order_summary_item_count( $order_summary ); ?>"></td>
						<?php endif; ?>
						<td style="<?php gp_ecommerce_fields()->style( ".order-summary/tfoot/{$class}/td.column-3" ); ?>"><?php echo $item['name']; ?></td>
						<td style="<?php gp_ecommerce_fields()->style( ".order-summary/tfoot/{$class}/td.column-4" ); ?>">
							<?php
							if ( $item['name'] == 'Subtotal' || $item['class'] == 'subtotal' ) {
								$item['price'] = $total_excl_tax;
								echo GFCommon::to_money( $item['price'], $entry['currency'] );
							} elseif ( $item['class'] == 'incltax' ) {
								echo GFCommon::to_money( $tax, $entry['currency'] );
							} else {
								echo GFCommon::to_money( $item['price'], $entry['currency'] );
							}
							?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php endforeach; ?>
			</tfoot>
		</table>

		<?php
		return ob_get_clean();
	}

	function is_applicable_form( $form ) {

		if ( $this->_args['apply_to_all_forms'] == true ) {
			return true;
		}

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

}

# Configuration

new GW_Tax_Inclusive_Order_Summary( array(
	// ID of the form to use this customized order summary markup on.
	'form_id'            => 123,
	// Custom tax label to show on the order summary.
	'tax_label'          => 'Sale Tax (20%)',
	// Tax percentage amount.
	'tax_rate'           => 20,
	// Set this to true to apply this customized order summary markup to all forms.
	'apply_to_all_forms' => false,
) );
