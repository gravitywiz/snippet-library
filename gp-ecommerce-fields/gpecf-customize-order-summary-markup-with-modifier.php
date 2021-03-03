<?php
add_filter( 'gpecf_order_sumary_markup', 'get_custom_order_summary_markup', 10, 8 );
function get_custom_order_summary_markup( $markup, $order, $form, $entry, $order_summary, $labels, $is_inline, $modifiers ) {
	if ( ! in_array( 'my_custom_modifier', $modifiers ) ) {
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
		<?php foreach ( $order['products'] as $product ):
			if ( empty( $product['name'] ) || gp_ecommerce_fields()->is_ecommerce_product( $product ) ) {
				continue;
			}
			?>
			<tr style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr' ); ?>">
				<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr/td.column-1' ); ?>">
					<div style="<?php gp_ecommerce_fields()->style( '.order-summary/.product-name' ); ?>">
						<?php echo esc_html( $product['name'] ); ?>
					</div>
					<ul style="<?php gp_ecommerce_fields()->style( '.order-summary/.product-options' ); ?>">
						<?php
						$price = GFCommon::to_number( $product['price'] );
						if ( is_array( rgar( $product, 'options' ) ) ):
							foreach ( $product['options'] as $index => $option ):
								$price += GFCommon::to_number( $option['price'] );
								$class = $index == count( $product['options'] ) - 1 ? '.last-child' : '';
								?>
								<li style="<?php gp_ecommerce_fields()->style( ".order-summary/.product-options/li{$class}" ); ?>"><?php echo $option['option_label'] ?></li>
							<?php
							endforeach;
						endif;
						$field_total = floatval( $product['quantity'] ) * $price;
						?>
					</ul>
				</td>
				<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr/td.column-2' ); ?>"><?php echo esc_html( $product['quantity'] ); ?></td>
				<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr/td.column-3' ); ?>"><?php echo GFCommon::to_money( $price, $entry['currency'] ) ?></td>
				<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tbody/tr/td.column-4' ); ?>"><?php echo GFCommon::to_money( $field_total, $entry['currency'] ) ?></td>
			</tr>
		<?php
		endforeach;
		?>
		</tbody>
		<tfoot style="<?php gp_ecommerce_fields()->style( '.order-summary/tfoot' ); ?>">
		<?php foreach( gp_ecommerce_fields()->get_order_summary( $order, $form, $entry ) as $index => $group ): ?>
			<?php foreach( $group as $item ):
				$class = rgar( $item, 'class' ) ? '.' . rgar( $item, 'class' ) : '';
				?>
				<tr style="<?php gp_ecommerce_fields()->style( '.order-summary/tfoot/tr' . $class ); ?>">
					<?php if( $index === 0 ): ?>
						<td style="<?php gp_ecommerce_fields()->style( '.order-summary/tfoot/tr/td.empty' ); ?>" colspan="2" rowspan="<?php echo gp_ecommerce_fields()->get_order_summary_item_count( $order_summary ); ?>"></td>
					<?php endif; ?>
					<td style="<?php gp_ecommerce_fields()->style( ".order-summary/tfoot/{$class}/td.column-3" ); ?>"><?php echo $item['name']; ?></td>
					<td style="<?php gp_ecommerce_fields()->style( ".order-summary/tfoot/{$class}/td.column-4" ); ?>"><?php echo GFCommon::to_money( $item['price'], $entry['currency'] ) ?></td>
				</tr>
			<?php endforeach; ?>
		<?php endforeach; ?>
		</tfoot>
	</table>

	<?php
	return ob_get_clean();
}
