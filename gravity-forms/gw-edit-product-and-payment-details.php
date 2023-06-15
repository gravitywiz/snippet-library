<?php
/**
 * Gravity Wiz // Gravity Forms // Edit Products & Payment Details
 *
 * Edit products (and payment details) via the Gravity Forms Edit Entry view.
 *
 * @version   1.3
 * @author    David Smith <david@gravitywiz.com>
 * @license   GPL-2.0+
 * @link      http://gravitywiz.com/
 *
 * Plugin Name:  Gravity Forms Edit Products
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Edit products (and payment details) via the Gravity Forms Edit Entry view.
 * Author:       Gravity Wiz
 * Version:      1.3
 * Author URI:   http://gravitywiz.com
 */
class GW_Edit_Products {

	private static $instance = null;

	public static function get_instance( $args = array() ) {
		if ( null == self::$instance ) {
			self::$instance = new self( $args );
		}
		return self::$instance;
	}

	private function __construct( $args ) {

		// make sure we're running the required minimum version of Gravity Forms
		if ( ! property_exists( 'GFCommon', 'version' ) || ! version_compare( GFCommon::$version, '1.8', '>=' ) ) {
			return;
		}

		// time for hooks
		add_filter( 'gform_field_input', array( $this, 'display_product_edit_mode' ), 10, 5 );
		add_filter( 'gform_after_update_entry', array( $this, 'save_product_edits' ), 10, 2 );

		// edit payment status
		add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'handle_payment_details_meta_box' ), 10, 3 );
		add_action( 'gform_payment_details', array( $this, 'maybe_render_payment_details_edit_form' ), 10, 2 );

	}

	public function display_product_edit_mode( $input, $field, $value, $entry_id, $form_id ) {

		if ( ! $this->is_entry_detail() || ! GFCommon::is_product_field( $field['type'] ) || $field->type == 'total' ) {
			return $input;
		}

		//$orig_type = $field->type;
		$field->type = 'GWEP';
		$input       = $this->get_field_input( $field, $value, $entry_id, $form_id );
		//$field->type = $orig_type;

		return $input;
	}

	public function get_field_input( $field, $value, $entry_id, $form_id ) {

		remove_filter( 'gform_field_input', array( $this, 'display_product_edit_mode' ) );

		$input = GFCommon::get_field_input( $field, $value, $entry_id, $form_id, GFAPI::get_form( $form_id ) );

		add_filter( 'gform_field_input', array( $this, 'display_product_edit_mode' ), 10, 5 );

		return $input;
	}

	public function save_product_edits( $form, $entry_id ) {

		if ( ! $this->is_entry_detail() ) {
			return;
		}

		$has_product_field = false;

		foreach ( $form['fields'] as &$field ) {
			if ( GFCommon::is_product_field( $field['type'] ) ) {
				$has_product_field = true;
				$field->origType   = $field->type;
				$field->type       = 'GWEP';
			}
		}

		if ( $has_product_field ) {

			$entry = GFAPI::get_entry( $entry_id );
			GFFormsModel::save_lead( $form, $entry );

			// set in GFCommon::get_product_fields_by_type(); reset.
			global $_product_fields;
			$_product_fields = array();

			$this->clear_product_cache( $entry_id );

		}

		// reset original field type
		foreach ( $form['fields'] as &$field ) {
			if ( $field->origType ) {
				$field->type = $field->origType;
			}
		}

		if ( $has_product_field ) {
			foreach ( $form['fields'] as &$field ) {
				if ( $field->type == 'subtotal' ) {
					$order            = GFCommon::get_product_fields( $form, $entry, false );
					$exclude_products = array();

					if ( $field->subtotalProductsType === 'exclude' ) {
						$exclude_products = $field->subtotalProducts;
					} elseif ( $field->subtotalProductsType === 'include' ) {
						$exclude_products = array_diff( array_keys( $order['products'] ), $field->subtotalProducts );
					}

					$subtotal = GF_Field_Subtotal::get_subtotal( $order, $exclude_products );

					GFAPI::update_entry_field( $entry['id'], $field->id, $subtotal );
				} elseif ( $field->type === 'discount' ) {
					$order = GFCommon::get_product_fields( $form, $entry, false );
					$discount = rgars( $order, "products/{$field->id}/price" );

					GFAPI::update_entry_field( $entry['id'], $field->id, $discount );
				} elseif ( $field->type == 'total' ) {
					// calculate the total once product fields have been restored to their original types
					$total = GFCommon::get_order_total( $form, $entry );
					GFAPI::update_entry_field( $entry['id'], $field->id, $total );
				}
			}
		}

	}

	public function clear_product_cache( $entry_id ) {

		gform_delete_meta( $entry_id, 'gform_product_info_1_1' );
		gform_delete_meta( $entry_id, 'gform_product_info__1' );
		gform_delete_meta( $entry_id, 'gform_product_info_1_' );
		gform_delete_meta( $entry_id, 'gform_product_info__' );

	}

	public function is_entry_detail() {
		return in_array( GFForms::get_page(), array( 'entry_detail', 'entry_detail_edit' ) );
	}

	public function is_entry_detail_edit() {
		return GFForms::get_page() == 'entry_detail_edit';
	}

	public function handle_payment_details_meta_box( $meta_boxes, $entry, $form ) {

		$entry = $this->save_payment_details( $entry );

		if ( ! isset( $meta_boxes['payment'] ) && ( $this->is_entry_detail_edit() || ! empty( $entry['payment_status'] ) ) ) {
			$meta_boxes['payment'] = array(
				'title'    => $entry['transaction_type'] == 2 ? esc_html__( 'Subscription Details', 'gravityforms' ) : esc_html__( 'Payment Details', 'gravityforms' ),
				'callback' => array( 'GFEntryDetail', 'meta_box_payment_details' ),
				'context'  => 'side',
			);
		}

		return $meta_boxes;
	}

	public function maybe_render_payment_details_edit_form( $form_id, $entry ) {

		if ( ! $this->is_entry_detail_edit() ) {
			return;
		}

		?>

		<style type="text/css">

			.gf_payment_detail { display: none; }

			.gwep-payment-detail { overflow: hidden; padding: 0 0 10px; }
			.gwep-payment-detail:last-child { padding-bottom: 0; }
			.gwep-payment-detail input,
			.gwep-payment-detail select { width: 100px; float: right; }

		</style>

		<div class="gwep-payment-detail">
			<label for="gwep-payment-status"><?php _e( 'Payment Status' ); ?></label>
			<input id="gwep-payment-status" name="payment_status" list="payment-stati" value="<?php echo rgar( $entry, 'payment_status' ); ?>" placeholder="i.e. Paid" />
			<datalist id="payment-stati">
				<option value="<?php _e( 'Paid' ); ?>">
				<option value="<?php _e( 'Processing' ); ?>">
				<option value="<?php _e( 'Active' ); ?>">
				<option value="<?php _e( 'Cancelled' ); ?>">
				<option value="<?php _e( 'Failed' ); ?>">
				<option value="<?php _e( 'Voided' ); ?>">
			</datalist>
		</div>

		<div class="gwep-payment-detail">
			<label for="gwep-payment-date"><?php _e( 'Payment Date' ); ?></label>
			<input id="gwep-payment-date" name="payment_date" type="text" value="<?php echo rgar( $entry, 'payment_date' ); ?>" placeholder="i.e. <?php echo date( 'Y-m-d' ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date ?>" />
		</div>

		<div class="gwep-payment-detail">
			<label for="gwep-payment-amount"><?php _e( 'Payment Amount' ); ?></label>
			<input id="gwep-payment-amount" name="payment_amount" type="text" value="<?php echo rgar( $entry, 'payment_amount' ); ?>" placeholder="i.e. <?php echo GFCommon::to_money( 0, $entry['currency'] ); ?>" />
		</div>

		<div class="gwep-payment-detail">
			<label for="gwep-transaction-id"><?php _e( 'Transaction ID' ); ?></label>
			<input id="gwep-transaction-id" name="transaction_id" type="text" value="<?php echo rgar( $entry, 'transaction_id' ); ?>" placeholder="i.e. 123ABC" />
		</div>

		<div class="gwep-payment-detail">
			<label for="gwep-transaction-type"><?php _e( 'Transaction Type' ); ?></label>
			<select id="gwep-transaction-type" name="transaction_type">
				<option value="" ><?php _e( 'None' ); ?></option>
				<option value="1" <?php selected( $entry['transaction_type'], 1 ); ?>><?php _e( 'Payment' ); ?></option>
				<option value="2" <?php selected( $entry['transaction_type'], 2 ); ?>><?php _e( 'Subscription' ); ?></option>
			</select>
		</div>

		<div class="gwep-payment-detail">
			<label for="gwep-payment-method"><?php _e( 'Payment Method' ); ?></label>
			<select id="gwep-payment-method" name="payment_method">
				<option value=""><?php _e( 'None' ); ?></option>
				<option value="<?php _e( 'Check' ); ?>" <?php selected( $entry['payment_method'], __( 'Check' ) ); ?>><?php _e( 'Check' ); ?></option>
				<?php
				foreach ( GFAddOn::get_registered_addons() as $addon ) :
					$addon = call_user_func( array( $addon, 'get_instance' ) );
					if ( $addon instanceof GFPaymentAddOn ) :
						?>
						<option value="<?php echo $addon->get_short_title(); ?>" <?php selected( $entry['payment_method'], $addon->get_short_title() ); ?>><?php echo $addon->get_short_title(); ?></option>
					<?php endif; ?>
				<?php endforeach; ?>
			</select>
		</div>

		<?php
	}

	public function save_payment_details( $entry ) {

		if ( ! $this->is_entry_detail() || rgpost( 'action' ) != 'update' ) {
			return $entry;
		}

		$keys = array( 'payment_status', 'payment_date', 'payment_amount', 'transaction_id', 'transaction_type', 'payment_method' );

		foreach ( $keys as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$entry[ $key ] = $_POST[ $key ];
			}
		}

		GFAPI::update_entry( $entry );

		GFEntryDetail::set_current_entry( $entry );

		return $entry;
	}

}

function gw_edit_products( $args = array() ) {
	return GW_Edit_Products::get_instance( $args );
}

# Configuration

gw_edit_products();
