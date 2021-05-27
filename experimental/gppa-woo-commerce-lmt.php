<?php
/**
 * This snippet enables LMT usage in WooCommerce cart pages.
 * See: https://secure.helpscout.net/conversation/1520295750/24633?folderId=3808239
 */
add_action( 'wp', function() {
	if ( ! function_exists('WC' ) || ( ! is_cart() && ! is_checkout() ) ) return;
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		add_filter( 'gform_pre_render', function( $form ) use ($cart_item) {
			$gravity_form_data = $cart_item['_gravity_form_data'];
			$form_meta = RGFormsModel::get_form_meta( $gravity_form_data['id'] );
			foreach ( $form_meta['fields'] as $field_index => $field ) {
				if ( ! $field['choices'] ) {
					continue;
				}

				foreach ( $field['choices'] as $choice_index => $choice ) {
					$choice['text'] = gp_populate_anything()->live_merge_tags->replace_live_merge_tags( $choice['text'], $form_meta, $cart_item['_gravity_form_lead'] );

					$form_meta['fields'][ $field_index ]->choices[ $choice_index ] = $choice;
				}
			}
			return $form;
		});
	}
});
