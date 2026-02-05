<?php
/**
 * Gravity Forms Credit Card Fee Field
 *
 * Adds a new field type to Gravity Forms that automatically calculates and adds credit card processing fees
 * to orders. The fee is calculated based on a configurable fixed fee + percentage rate.
 * Ensure the form has a total or subtotal field for accurate calculations.
 */
// Ensure this runs after all plugins are loaded
add_action( 'plugins_loaded', function() {
	// Configuration constants - Edit these values as needed
	if ( ! defined( 'CREDIT_CARD_FEE_FIXED' ) ) {
		define( 'CREDIT_CARD_FEE_FIXED', 0.30 ); // Fixed fee (e.g., $0.30)
	}

	if ( ! defined( 'CREDIT_CARD_FEE_PERCENTAGE' ) ) {
		define( 'CREDIT_CARD_FEE_PERCENTAGE', 0.033 ); // Percentage rate (e.g., 3.3%)
	}

	/**
	 * Add Credit Card Fee Field Class, but ensure GP Ecommerce Fields is active.
	 */
	if ( ! class_exists( 'GF_Field_Credit_Card_Fee' ) && class_exists( 'GF_Field_Subtotal' ) ) {

	class GF_Field_Credit_Card_Fee extends GF_Field_Subtotal {

		public $type = 'credit_card_fee';

		public function get_form_editor_field_title() {
			return esc_html__( 'Credit Card Fee', 'gp-ecommerce-fields' );
		}

		public function get_form_editor_field_settings() {
			return array(
				'label_setting',
				'description_setting',
				'css_class_setting',
				'admin_label_setting',
				'label_placement_setting',
				'conditional_logic_field_setting',
			);
		}

		public function get_form_editor_inline_script_on_page_render() {
			$set_default_values = sprintf( 'function SetDefaultValues_%1$s( field ) {
				field.label = "%2$s";
				field.%1$sProductsType = "all";
				field.%1$sProducts = [];
				return field;
			};', $this->type, $this->get_form_editor_field_title() );

			return $set_default_values;
		}

		public static function add_credit_card_fees( $order, $form, $entry ) {
			$fee_fields = array();

			foreach ( $form['fields'] as $field ) {
				if ( $field->type == 'credit_card_fee' && ! GFFormsModel::is_field_hidden( $form, $field, array(), $entry ) ) {
					$fee_fields[] = $field;
				}
			}

			if ( empty( $fee_fields ) ) {
				return $order;
			}

			foreach ( $fee_fields as $fee_field ) {
				$subtotal = GF_Field_Subtotal::get_subtotal( $order );

				// Calculate credit card fee: ( subtotal + fixed_fee ) / ( 1 - percentage_rate ) - subtotal
				$fixed_fee       = CREDIT_CARD_FEE_FIXED;
				$percentage_rate = CREDIT_CARD_FEE_PERCENTAGE;

				if ( $percentage_rate >= 1 ) {
					$percentage_rate = $percentage_rate / 100; // Convert to decimal if provided as percentage
				}

				// Prevent division by zero - cap percentage rate at 99.9%
				if ( $percentage_rate >= 1 ) {
					$percentage_rate = 0.999;
				}

				$fee = ( ( $subtotal + $fixed_fee ) / ( 1 - $percentage_rate ) ) - $subtotal;
				$fee = max( 0, $fee ); // Ensure fee is never negative

				$order['products'][ $fee_field->id ] = array(
					'name'            => $fee_field->get_field_label( true, false ),
					'price'           => $fee,
					'quantity'        => 1,
					'isCreditCardFee' => true,
				);
			}

			return $order;
		}

		public static function add_order_summary_fee_items( $order_summary, $form, $entry, $order ) {
			foreach ( $order['products'] as $product ) {
				if ( ! rgar( $product, 'isCreditCardFee' ) ) {
					continue;
				}

				$order_summary['fees'][] = array(
					'name'  => $product['name'],
					'price' => $product['price'],
				);
			}

			return $order_summary;
		}

		public function get_field_input( $form, $value = '', $entry = null ) {
			$form_id         = $form['id'];
			$is_entry_detail = $this->is_entry_detail();
			$is_form_editor  = $this->is_form_editor();

			$id      = (int) $this->id;
			$html_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

			if ( $is_entry_detail ) {
				return ''; // field should not be displayed on entry detail
			} else {
				return $this->get_input_markup( $form_id, $id, $html_id );
			}
		}

		public function get_input_markup( $form_id, $field_id, $html_id ) {
			return "
				<div class='ginput_container'>
					<span class='ginput_{$this->type} ginput_product_price ginput_{$this->type}_{$form_id}_{$field_id}'
						style='" . $this->get_inline_price_styles() . "'>" . GFCommon::to_money( '0' ) . "</span>
					<input type='hidden' name='input_{$field_id}' id='{$html_id}' class='gform_hidden ginput_{$this->type}_input' 
						onchange='jQuery( this ).prev( \"span\" ).text( gformFormatMoney( this.value, true ) );' 
						data-fixed-fee='" . CREDIT_CARD_FEE_FIXED . "' 
						data-percentage-rate='" . CREDIT_CARD_FEE_PERCENTAGE . "' />
				</div>";
		}

		public function get_field_label( $force_frontend_label, $value ) {
			// Override GF_Field_SingleProduct::get_field_label() which includes markup that will not get escaped for our field.
			return GF_Field::get_field_label( $force_frontend_label, $value );
		}

		public function get_value_save_entry( $value, $form, $input_name, $entry_id, $entry ) {
			return $value;
		}

		public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
			return GFCommon::format_number( $value, 'currency', $currency );
		}
	}

	// Register the field
	GF_Fields::register( new GF_Field_Credit_Card_Fee() );
	}
}, 20 );

// Add credit card fees to product info for proper total calculation
add_filter( 'gform_product_info', 'add_credit_card_fee_to_product_info', 10, 3 );

function add_credit_card_fee_to_product_info( $product_info, $form, $entry ) {
	// Find credit card fee fields
	$fee_fields = array();
	foreach ( $form['fields'] as $field ) {
		if ( $field->type == 'credit_card_fee' && ! GFFormsModel::is_field_hidden( $form, $field, array(), $entry ) ) {
			$fee_fields[] = $field;
		}
	}

	if ( empty( $fee_fields ) ) {
		return $product_info;
	}

	foreach ( $fee_fields as $fee_field ) {
		// Calculate subtotal from current product info
		$subtotal = 0;
		foreach ( $product_info['products'] as $product ) {
			$subtotal += floatval( $product['price'] ) * floatval( $product['quantity'] );
		}

		// Calculate credit card fee
		$fixed_fee       = CREDIT_CARD_FEE_FIXED;
		$percentage_rate = CREDIT_CARD_FEE_PERCENTAGE;

		if ( $percentage_rate >= 1 ) {
			$percentage_rate = $percentage_rate / 100;
		}

		// Prevent division by zero - cap percentage rate at 99.9%
		if ( $percentage_rate >= 1 ) {
			$percentage_rate = 0.999;
		}

		$fee = ( ( $subtotal + $fixed_fee ) / ( 1 - $percentage_rate ) ) - $subtotal;
		$fee = max( 0, $fee );

		// Add fee as a product to product info
		$product_info['products'][ $fee_field->id ] = array(
			'name'     => $fee_field->get_field_label( true, false ),
			'price'    => $fee,
			'quantity' => 1,
		);
	}

	return $product_info;
}

// Add JavaScript for frontend calculations
add_action( 'gform_enqueue_scripts', 'enqueue_credit_card_fee_script', 10, 2 );

function enqueue_credit_card_fee_script( $form, $is_ajax ) {
	if ( ! has_credit_card_fee_field( $form ) ) {
		return;
	}

	wp_add_inline_script( 'gform_gravityforms', get_credit_card_fee_script( $form['id'] ) );
}

function has_credit_card_fee_field( $form ) {
	if ( ! is_array( $form['fields'] ) ) {
		return false;
	}

	foreach ( $form['fields'] as $field ) {
		if ( $field->type == 'credit_card_fee' ) {
			return true;
		}
	}

	return false;
}

function get_credit_card_fee_script( $form_id ) {
	$fixed_fee       = CREDIT_CARD_FEE_FIXED;
	$percentage_rate = CREDIT_CARD_FEE_PERCENTAGE;

	return "
	(function($) {
		// Calculate credit card fee based on subtotal
		function calculateCreditCardFee(subtotal, fixedFee, percentageRate) {
			if (percentageRate >= 1) {
				percentageRate = percentageRate / 100; // Convert to decimal if provided as percentage
			}
			
			// Prevent division by zero - cap percentage rate at 99.9%
			if (percentageRate >= 1) {
				percentageRate = 0.999;
			}
			
			var fee = ((subtotal + fixedFee) / (1 - percentageRate)) - subtotal;
			return Math.max(0, fee); // Ensure fee is never negative
		}

		// Update credit card fee fields
		function updateCreditCardFees() {
			var formId = {$form_id};
			var subtotal = 0;
			
			// Calculate subtotal (excluding taxes, discounts, fees)
			$('.ginput_subtotal_input, input[id*=\"_subtotal\"]').each(function() {
				var fieldValue = parseFloat($(this).val()) || 0;
				subtotal += fieldValue;
			});

			// If no subtotal field, calculate from products
			if (subtotal === 0) {
				var products = window.gformGetProduct ? window.gformGetProduct(formId) : [];
				if (products && products.length) {
					$.each(products, function(i, product) {
						if (product && product.price) {
							subtotal += parseFloat(product.price) * (parseFloat(product.quantity) || 1);
						}
					});
				}
			}

			// Update each credit card fee field
			$('.ginput_credit_card_fee_input').each(function() {
				var \$field = $(this);
				var fixedFee = parseFloat(\$field.data('fixed-fee')) || {$fixed_fee};
				var percentageRate = parseFloat(\$field.data('percentage-rate')) || {$percentage_rate};
				
				var fee = calculateCreditCardFee(subtotal, fixedFee, percentageRate);
				fee = Math.round(fee * 100) / 100; // Round to 2 decimal places
				
				if (String(\$field.val()) !== String(fee)) {
					\$field.val(fee).trigger('change');
				}
			});
		}

		// Bind to form events
		$(document).on('gform_post_render', function(event, formId) {
			if (formId == {$form_id}) {
				// Initial calculation
				updateCreditCardFees();
				
				// Bind to price changes
				$(document).on('gform_price_change', function() {
					setTimeout(updateCreditCardFees, 100);
				});
				
				// Bind to product changes
				$('body').on('change', '.gfield_product input, .gfield_product select', function() {
					setTimeout(updateCreditCardFees, 100);
				});
				
				// Bind to quantity changes
				$('body').on('change input', '.ginput_quantity', function() {
					setTimeout(updateCreditCardFees, 100);
				});
			}
		});

		// Add filter for product total calculation to include credit card fees
		if (window.gform && window.gform.addFilter) {
			gform.addFilter('gform_product_total', function(total, formId) {
				if (formId == {$form_id}) {
					$('.ginput_credit_card_fee_input').each(function() {
						var fee = parseFloat($(this).val()) || 0;
						total += fee;
					});
				}
				return total;
			}, 60); // Higher priority than tax calculations
		}
		
	})(jQuery);
	";
}
