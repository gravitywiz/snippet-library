<?php
/**
 * Gravity Perks // GP eCommerce Fields // Deduct Deposit from Order Summary
 * https://gravitywiz.com/documentation/gravity-forms-ecommerce-fields/
 *
 * Instruction Video: https://www.loom.com/share/303f0d636c964efcb89478ead9e5e3cb
 *
 * This snippet uses a Product field to create a deposit field and deducts the deposit from Order Summary.
 * To use the snippet, you'll have to update the Form ID and the deposit field ID within the snippet.
 */
class GW_Deduct_Deposit {

	private $_args = array();

	public function __construct( $args ) {

		$this->_args = wp_parse_args( $args, array(
			'form_id'          => false,
			'deposit_field_id' => false,
		) );

		// GPECF inits on priority 15 and we must wait for it to bind its function so we can remove it.
		add_action( 'init', array( $this, 'init' ), 16 );

	}

	public function init() {

		if ( ! class_exists( 'GFForms' ) || ! function_exists( 'gp_ecommerce_fields' ) ) {
			return;
		}

		remove_action( 'gform_product_info', array( gp_ecommerce_fields(), 'add_ecommerce_fields_to_order' ), 9 );

		add_action( 'gform_product_info', array( $this, 'deduct_deposit' ), 9, 3 );
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ) );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ) );

	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || (int) $form_id === (int) $this->_args['form_id'];
	}

	public function load_form_script( $form ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ) );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ) );
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				window.<?php echo __CLASS__; ?> = function( args ) {

					var self = this;

					self.formId = args.formId;
					self.depositFieldId = args.depositFieldId;

					self.init = function() {
						gform.addFilter( 'gform_product_total', function( total, formId ) {
							if ( formId == self.formId ) {
								// If deposit is disabled or hidden, return total as it is.
								if ( $( 'input[name="input_' + self.depositFieldId + '.2"]' ).is( ':disabled' ) ) {
									return total;
								}
								
								var depositPrice    = $( 'input[name="input_' + self.depositFieldId + '.2"]' ).val();
								var depositQuantity = $( 'input[name="input_' + self.depositFieldId + '.3"]' ).val();

								depositValue = gformToNumber(depositPrice) * depositQuantity;

								// since the depositValue (product field) would have been added to the total,
								// it must first be removed to get base value and then discount applied (second substract).
								total = total - depositValue - depositValue;
							}
							return total;
						} );
					};

					self.init();

				}

			} )( jQuery );

		</script>

		<?php
	}

	public function add_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$args = array(
			'formId'         => $this->_args['form_id'],
			'depositFieldId' => $this->_args['deposit_field_id'],
		);

		$script = 'new ' . __CLASS__ . '( ' . json_encode( $args ) . ' );';
		$slug   = implode( '_', array( strtolower( __CLASS__ ), $this->_args['form_id'] ) );

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function deduct_deposit( $order, $form, $entry ) {

		// If we've already deducted deposits, return the order as is.
		if ( rgar( $order, 'depositsDeducted' ) ) {
			return $order;
			// Make sure we're processing this function only for the current instance of this class.
		} elseif ( (int) $form['id'] !== (int) $this->_args['form_id'] ) {
			return gp_ecommerce_fields()->add_ecommerce_fields_to_order( $order, $form, $entry );
		}

		$deposit =& $order['products'][ $this->_args['deposit_field_id'] ];

		// Run this first so calculations are reprocessed before we convert deposit to a negative number.
		$order = gp_ecommerce_fields()->add_ecommerce_fields_to_order( $order, $form, $entry );

		// Convert deposit to a negative number so it is deducted from the total.
		$deposit['price'] = GFCommon::to_money( GFCommon::to_number( $deposit['price'], $entry['currency'] ) * $deposit['quantity'] * - 1, $entry['currency'] );

		// Quantity is factored into price above.
		$deposit['quantity'] = 1;

		// Set the discount flag so GP eCommerce Fields knows this is a deposit.
		$deposit['isDiscount'] = true;

		// Indicate that this order has been processed for deposits.
		$order['depositsDeducted'] = true;

		return $order;

	}

}

# Configuration

new GW_Deduct_Deposit( array(
	'form_id'          => 123, // Update "123" to the ID of your form.
	'deposit_field_id' => 4,   // Update the "4" to your deposit field ID.
) );
