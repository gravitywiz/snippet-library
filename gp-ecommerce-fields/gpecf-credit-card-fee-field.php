<?php
/**
 * Gravity Forms Credit Card Fee Field
 *
 * Adds a new field type to Gravity Forms that automatically calculates and adds credit card processing fees
 * to orders. The fee is calculated based on a configurable fixed fee + percentage rate.
 * Ensure the form has a total or subtotal field for accurate calculations.
 */
// Ensure this runs after all plugins are loaded.
add_action(
	'plugins_loaded',
	function() {
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
					'credit_card_fee_settings',
				);
			}

			public function get_form_editor_inline_script_on_page_render() {
				$set_default_values = sprintf(
					'function SetDefaultValues_%1$s( field ) {
						field.label = "%2$s";
						field.%1$sProductsType = "all";
						field.%1$sProducts = [];
						field.creditCardFeeFixed = "0.30";
						field.creditCardFeePercentage = "0.033";
						return field;
					};',
					$this->type,
					$this->get_form_editor_field_title()
				);

				return $set_default_values . $this->get_form_editor_field_settings_js();
			}

			public function get_form_editor_field_settings_js() {
				return "
				// Add Credit Card Fee Settings
				fieldSettings.credit_card_fee += ', .credit_card_fee_settings';
				
				// Bind to show/hide credit card fee settings
				jQuery(document).bind('gform_load_field_settings', function(event, field, form) {
					jQuery('#credit_card_fee_fixed').val(field.creditCardFeeFixed ? field.creditCardFeeFixed : '0.30');
					jQuery('#credit_card_fee_percentage').val(field.creditCardFeePercentage ? field.creditCardFeePercentage : '0.033');
				});
				";
			}

			public static function add_credit_card_fees( $order, $form, $entry ) {
				$fee_fields = array();

				foreach ( $form['fields'] as $field ) {
					if ( 'credit_card_fee' === $field->type && ! GFFormsModel::is_field_hidden( $form, $field, array(), $entry ) ) {
						$fee_fields[] = $field;
					}
				}

				if ( empty( $fee_fields ) ) {
					return $order;
				}

				foreach ( $fee_fields as $fee_field ) {
					$subtotal = GF_Field_Subtotal::get_subtotal( $order );

					// Get field-specific settings with defaults.
					$fixed_fee       = isset( $fee_field->creditCardFeeFixed ) ? floatval( $fee_field->creditCardFeeFixed ) : 0.30;
					$percentage_rate = isset( $fee_field->creditCardFeePercentage ) ? floatval( $fee_field->creditCardFeePercentage ) : 0.033;

					if ( $percentage_rate >= 1 ) {
						$percentage_rate = $percentage_rate / 100; // Convert to decimal if provided as percentage.
					}

					// Prevent division by zero - cap percentage rate at 99.9%.
					if ( $percentage_rate >= 1 ) {
						$percentage_rate = 0.999;
					}

					$fee = ( ( $subtotal + $fixed_fee ) / ( 1 - $percentage_rate ) ) - $subtotal;
					$fee = max( 0, $fee ); // Ensure fee is never negative.

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
				$html_id = $is_entry_detail || $is_form_editor || 0 === $form_id ? "input_$id" : 'input_' . $form_id . "_$id";

				if ( $is_entry_detail ) {
					return ''; // Field should not be displayed on entry detail.
				} else {
					return $this->get_input_markup( $form_id, $id, $html_id );
				}
			}

			public function get_input_markup( $form_id, $field_id, $html_id ) {
				$fixed_fee       = isset( $this->creditCardFeeFixed ) ? floatval( $this->creditCardFeeFixed ) : 0.30;
				$percentage_rate = isset( $this->creditCardFeePercentage ) ? floatval( $this->creditCardFeePercentage ) : 0.033;
				
				return "
					<div class='ginput_container'>
						<span class='ginput_{$this->type} ginput_product_price ginput_{$this->type}_{$form_id}_{$field_id}'
							style='" . $this->get_inline_price_styles() . "'>" . GFCommon::to_money( '0' ) . "</span>
						<input type='hidden' name='input_{$field_id}' id='{$html_id}' class='gform_hidden ginput_{$this->type}_input' 
							onchange='jQuery( this ).prev( \"span\" ).text( gformFormatMoney( this.value, true ) );' 
							data-fixed-fee='{$fixed_fee}' 
							data-percentage-rate='{$percentage_rate}' />
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

		// Register the field.
		GF_Fields::register( new GF_Field_Credit_Card_Fee() );
		
		// Add field settings UI.
		add_action( 'gform_field_standard_settings', 'add_credit_card_fee_settings', 10, 2 );
		}

		/**
		 * Add field settings for Credit Card Fee configuration.
		 */
			function add_credit_card_fee_settings( $position, $form_id ) {
			if ( 25 === $position ) {
				?>
				<li class="credit_card_fee_settings field_setting" style="display:none;">
					<label for="credit_card_fee_fixed" class="section_label">
						<?php esc_html_e( 'Credit Card Fee Settings', 'gp-ecommerce-fields' ); ?>
					</label>
					
					<label for="credit_card_fee_fixed">
						<?php esc_html_e( 'Fixed Fee ($)', 'gp-ecommerce-fields' ); ?>
					</label>
					<input type="text" id="credit_card_fee_fixed" class="field_setting" 
						   onkeyup="SetFieldProperty('creditCardFeeFixed', this.value);" 
						   placeholder="0.30" />
					<br/>
					
					<label for="credit_card_fee_percentage">
						<?php esc_html_e( 'Percentage Rate (e.g., 0.033 for 3.3%)', 'gp-ecommerce-fields' ); ?>
					</label>
					<input type="text" id="credit_card_fee_percentage" class="field_setting" 
						   onkeyup="SetFieldProperty('creditCardFeePercentage', this.value);" 
						   placeholder="0.033" />
				</li>
				<?php
			}
		}
		// Add credit card fees to product info for proper total calculation.
		add_filter( 'gform_product_info', 'add_credit_card_fee_to_product_info', 10, 3 );

		function add_credit_card_fee_to_product_info( $product_info, $form, $entry ) {
			// Find credit card fee fields.
			$fee_fields = array();
			foreach ( $form['fields'] as $field ) {
				if ( 'credit_card_fee' === $field->type && ! GFFormsModel::is_field_hidden( $form, $field, array(), $entry ) ) {
					$fee_fields[] = $field;
				}
			}

			if ( empty( $fee_fields ) ) {
				return $product_info;
			}

			foreach ( $fee_fields as $fee_field ) {
				// Calculate subtotal from current product info.
				$subtotal = 0;
				foreach ( $product_info['products'] as $product ) {
					$subtotal += floatval( $product['price'] ) * floatval( $product['quantity'] );
				}

				// Get field-specific settings with defaults.
				$fixed_fee       = isset( $fee_field->creditCardFeeFixed ) ? floatval( $fee_field->creditCardFeeFixed ) : 0.30;
				$percentage_rate = isset( $fee_field->creditCardFeePercentage ) ? floatval( $fee_field->creditCardFeePercentage ) : 0.033;

				if ( $percentage_rate >= 1 ) {
					$percentage_rate = $percentage_rate / 100;
				}

				// Prevent division by zero - cap percentage rate at 99.9%.
				if ( $percentage_rate >= 1 ) {
					$percentage_rate = 0.999;
				}

				$fee = ( ( $subtotal + $fixed_fee ) / ( 1 - $percentage_rate ) ) - $subtotal;
				$fee = max( 0, $fee );

				// Add fee as a product to product info.
				$product_info['products'][ $fee_field->id ] = array(
					'name'     => $fee_field->get_field_label( true, false ),
					'price'    => $fee,
					'quantity' => 1,
				);
			}

			return $product_info;
		}

		// Add JavaScript for frontend calculations.
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
				if ( 'credit_card_fee' === $field->type ) {
					return true;
				}
			}

			return false;
		}

		function get_credit_card_fee_script( $form_id ) {
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
						var fixedFee = parseFloat(\$field.data('fixed-fee')) || 0.30;
						var percentageRate = parseFloat(\$field.data('percentage-rate')) || 0.033;
						
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

	},
	20
);
