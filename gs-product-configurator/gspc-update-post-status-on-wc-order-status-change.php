<?php
/**
 * Gravity Wiz // GS Product Configurator // Update Post Status on WooCommerce Order Status Change
 * https://gravitywiz.com/documentation/gravity-shop-product-configurator/
 *
 * This snippet will update the post status of the post created by Gravity Forms Advanced Post Creation when the
 * WooCommerce order status is changed.
 *
 * Possible statuses are: publish, future, draft, pending, private, trash, wc-active, wc-switched, wc-expired,
 * wc-pending-cancel, wc-pending, wc-processing, wc-on-hold, wc-completed, wc-cancelled, wc-refunded, wc-failed.
 *
 *  Instructions:
 *    1. Install per https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 *    2. Configure the snippet based on inline instructions.
 */

class GSPC_Update_Post_Status_On_WC_Order_Status_Change {
	private $_args;

	public function __construct( $args = array() ) {
		$this->_args = wp_parse_args( $args, array(
			'form_id'     => false,
			'post_status' => 'draft',
		) );

		if ( ! empty( $this->_args['post_status'] ) ) {
			// Change `woocommerce_order_status_cancelled` to the WooCommerce order status you want to trigger the post status update.
			add_action( 'woocommerce_order_status_cancelled', array( $this, 'update_post_status' ) );
		}

	}

	/**
	 * Update the post status.
	 *
	 * @param  int  $subscription_id  The subscription ID.
	 */
	function update_post_status( $subscription_id ) {
		$order = wc_get_order( $subscription_id );

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		$order_items = $order->get_items();

		foreach ( $order_items as $order_item ) {
			$item = \GS_Product_Configurator\WC_Order_Item::from( $order_item );

			$entry_ids = $item->get_entry_ids();

			if ( empty( $entry_ids ) ) {
				continue;
			}

			foreach ( $entry_ids as $entry_id ) {
				if ( ! GFAPI::entry_exists( $entry_id ) ) {
					continue;
				}

				if ( ! $this->is_applicable_form( $entry_id ) ) {
					continue;
				}

				$posts_data = maybe_unserialize( gform_get_meta( $entry_id, 'gravityformsadvancedpostcreation_post_id' ) );

				if ( ! $posts_data ) {
					continue;
				}

				foreach ( $posts_data as $post_data ) {
					$post_id = $post_data['post_id'] ?? null;
					if ( $post_id ) {
						wp_update_post( array(
							'ID'          => $post_id,
							'post_status' => $this->_args['post_status'],
						) );
					}
				}
			}
		}
	}

	/**
	 * Check if the form is applicable.
	 *
	 * @param  int  $entry_id  The entry ID.
	 *
	 * @return bool
	 */
	public function is_applicable_form( $entry_id ) {
		if ( empty( $this->_args['form_id'] ) ) {
			return true;
		}

		$entry   = GFAPI::get_entry( $entry_id );
		$form_id = rgar( $entry, 'form_id' );

		return (int) $form_id === (int) $this->_args['form_id'];
	}
}

new GSPC_Update_Post_Status_On_WC_Order_Status_Change( array(
	'form_id'     => 123, // Add `form_id` when you want to target a specific form.
	'post_status' => 'wc-expired', // Add the post status you want to update the post to.
) );
