<?php
/**
 * Gravity Wiz // Gravity Forms // Check-In
 *
 * "Check-in" for Gravity Forms products.
 *
 * @version  0.3
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/...
 *
 * Plugin Name:  Gravity Forms Check-in
 * Plugin URI:   http://gravitywiz.com/
 * Description:  "Check-in" for Gravity Forms products.
 * Author:       Gravity Wiz
 * Version:      0.3
 * Author URI:   http://gravitywiz.com
 */
class GW_Check_In {

	public function __construct( $args = array() ) {

		// set our default arguments, parse against the provided arguments, and store for use throughout the class
		$this->_args = wp_parse_args( $args, array(
			'form_id'           => false,
			'name_field_id'     => false,
			'product_field_ids' => array(),
			'labels'            => array(
				'check_in_title' => __( 'Check-in' ),
			),
		) );

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( ! is_callable( 'gp_post_content_merge_tags' ) ) {
			return;
		}

		// carry on
		$this->maybe_process_check_in();

		add_filter( 'gform_entry_post_save', array( $this, 'populate_check_in_url_field' ), 11 );

		add_filter( 'gform_entry_meta', array( $this, 'custom_entry_meta' ), 10, 2 );
		add_filter( 'gform_entries_field_value', array( $this, 'checked_in_products_entry_meta_value' ), 10, 4 );

	}

	public function maybe_process_check_in() {

		if ( $this->is_check_in_request() ) {
			$entry = gp_post_content_merge_tags()->get_entry();
			if ( $entry && $this->is_applicable_form( $entry['form_id'] ) ) {
				$this->output_check_in_markup( $entry );
				die();
			}
		}

	}

	public function is_check_in_request() {
		return rgget( 'gwci' );
	}

	public function output_check_in_markup( $entry ) {

		if ( ! current_user_can( $this->_args['check_in_role'] ) ) {
			die( 'You do not have permission to check-in.' );
		}

		if ( rgpost( 'nonce' ) ) {

			if ( ! wp_verify_nonce( rgpost( 'nonce' ), 'gwci_check_in' ) ) {
				die( 'Invalid nonce.' );
			}

			foreach ( rgpost( 'products' ) as $product_id ) {
				$this->check_in_product( rgpost( 'entry_id' ), $product_id );
			}
		}

		?>

		<!doctype html>

		<html lang="en">
		<head>
			<meta charset="utf-8">
			<title><?php echo $this->_args['labels']['check_in_title']; ?></title>
			<style>
				body {
					background-color: #f1f1f1;
					font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
					padding: 3rem;
					font-size: 3rem;
				}
				.success {
					border: 0.1rem solid #ddd;
					background-color: #fff;
					padding: 3rem;
					border-radius: 0 0.4rem 0.4rem 0;
					border-left: 0.4rem solid rgba(70, 180, 79, 1.000);
				}
				.title h1 {
					margin: 0 0 4rem;
				}
				.title .descriptor {
					font-size: 75%;
					display: block;
					font-weight: normal;
				}
				.products ul {
					list-style: none;
					padding-left: 0;
					margin: 4rem 0;
				}
				.products li label {
					border: 0.1rem solid #ddd;
					padding: 3rem;
					border-radius: 1rem;
					margin-bottom: 2rem;
					background-color: #fff;
					display: block;
					cursor: pointer;
				}
				.products input:disabled + span {
					opacity: 0.5;
				}
				.products input:checked + span {
					font-weight: bold;
				}
				.products input {
					width: 3rem;
					height: 3rem;
					vertical-align: middle;
					margin: 0 1rem 0 0;
				}
				input[type="submit"] {
					background-color: rgba( 59, 153, 252, 1.0 );
					color: #fff;
					padding: 4rem;
					border: 0;
					border-radius: 0.4rem;
					font-size: 3rem;
					cursor: pointer;
					-webkit-appearance: none;
					-moz-appearance: none;
					appearance: none;
				}
				input[type="submit"]:disabled {
					background-color: rgba( 0, 0, 0, 0.25 );
					opacity: 0.5;
					cursor: not-allowed;
				}
				input[type="submit"]:not(:disabled):hover {
					opacity: 0.9;
				}
			</style>
		</head>

		<body>

		<form action="" method="post">

			<?php wp_nonce_field( 'gwci_check_in', 'nonce' ); ?>

			<div class="title">
				<h1>
					<span class="descriptor">Check-in for</span>
					<span class="name"><?php echo rgar( $entry, $this->_args['name_field_id'] . '.3' ); ?> <?php echo rgar( $entry, $this->_args['name_field_id'] . '.6' ); ?></span>
				</h1>
			</div>

			<div class="notices">
				<?php
				if ( rgpost( 'products' ) ) :
					foreach ( rgpost( 'products' ) as $product_id ) :
						$text = $this->get_product_display_label( $product_id, $entry );
						if ( ! $text ) {
							continue;
						}
						?>
					<div class="success">
						<?php echo rgar( $entry, $this->_args['name_field_id'] . '.3' ); ?> was checked in for <em><?php echo $text; ?></em>.
					</div>
									<?php
				endforeach;
endif;
				?>
			</div>

			<div class="products">
				<ul>
					<?php
					foreach ( $this->_args['product_field_ids'] as $product_field_id ) {

						$text = $this->get_product_display_label( $product_field_id, $entry );
						if ( ! $text ) {
							continue;
						}

						$disabled = $this->is_product_checked_in( $product_field_id, $entry['id'] ) ? 'disabled="disabled"' : '';

						printf( '
							<li>
								<label for="product_%1$d">
									<input type="checkbox" name="products[]" id="product_%1$d" value="%1$d" %3$s>
									<span>%2$s</span>
								</label>
							</li>',
							$product_field_id, $text, $disabled
						);

					}
					?>
				</ul>
			</div>

			<div class="adminstrative">
				<input type="hidden" name="entry_id" value="<?php echo $entry['id']; ?>" />
			</div>

			<div class="actions">
				<input type="submit" value="Check-in for Selected Products" disabled="disabled">
			</div>

		</form>

		<?php wp_print_scripts( array( 'jquery' ) ); ?>

		<script type="text/javascript">

			( function( $ ) {

				$( '.products input[type="checkbox"]' ).click( function() {
					var $submit = $( 'input[type="submit"]' );
					$submit.prop( 'disabled', $( '.products input[type="checkbox"]:checked' ).length <= 0 );
				} );

			} )( jQuery );

		</script>

		</body>
		</html>

		<?php
	}

	public function get_product_display_label( $product_id, $entry ) {

		$field       = GFAPI::get_field( $entry['form_id'], $product_id );
		$field_label = $field->get_field_label( false, '' );

		if ( ! is_array( $field->choices ) ) {
			$text = $field_label;
		} else {
			$choice = $this->get_product_choice( $product_id, $entry );
			if ( ! $choice ) {
				return false;
			}
			$text = sprintf( '%s (%s)', $choice['text'], $field_label );
		}

		return $text;
	}

	public function check_in_product( $entry_id, $product_id ) {
		gform_add_meta( $entry_id, 'gwci_checked_in_product', $product_id );
	}

	public function is_product_checked_in( $product_id, $entry_id ) {
		global $wpdb;

		$result = $wpdb->get_var(
			$wpdb->prepare( "SELECT count( id ) FROM {$wpdb->prefix}gf_entry_meta WHERE entry_id = %d AND meta_key = 'gwci_checked_in_product' AND meta_value = %d", $entry_id, $product_id )
		);

		return $result > 0;
	}

	public function is_applicable_form( $form ) {

		$form_id = isset( $form['id'] ) ? $form['id'] : $form;

		return empty( $this->_args['form_id'] ) || $form_id == $this->_args['form_id'];
	}

	public function get_check_in_url( $entry_id ) {

		$pretty_id = gform_get_meta( $entry_id, 'gppcmt_pretty_id' );

		return add_query_arg( array(
			'gwci' => 1,
			'eid'  => $pretty_id,
		), get_site_url() );
	}

	public function get_product_choice( $product_id, $entry ) {

		if ( ! $this->is_applicable_form( $entry['form_id'] ) ) {
			return false;
		}

		$form  = GFAPI::get_form( $entry['form_id'] );
		$field = GFFormsModel::get_field( $form, $product_id );

		if ( is_array( $field->choices ) ) {
			$value   = explode( '|', rgar( $entry, $product_id ) );
			$value   = array_shift( $value );
			$choices = $field->choices;
			foreach ( $choices as $index => $choice ) {
				if ( $choice['value'] == $value ) {
					return $choice;
				}
			}
		}

		return false;
	}

	public function custom_entry_meta( $entry_meta, $form_id ) {

		$entry_meta['gwci_checked_in_products'] = array(
			'label'                      => 'Checked-in Products',
			'is_numeric'                 => false,
			'update_entry_meta_callback' => null,
			'is_default_column'          => true,
		);

		return $entry_meta;
	}

	public function checked_in_products_entry_meta_value( $value, $form_id, $field_id, $entry ) {

		if ( $field_id != 'gwci_checked_in_products' ) {
			return $value;
		}

		$value = array();

		foreach ( $this->_args['product_field_ids'] as $product_id ) {

			$field       = GFAPI::get_field( $form_id, $product_id );
			$field_label = $field->get_field_label( false, '' );

			if ( ! is_array( $field->choices ) ) {
				$text = $field_label;
			} else {
				$choice = $this->get_product_choice( $product_id, $entry );
				if ( ! $choice ) {
					continue;
				}
				$text = sprintf( '%s (%s)', $choice['text'], $field_label );
			}

			$is_checked_in = $this->is_product_checked_in( $product_id, $entry['id'] );
			$icon          = $is_checked_in ? '&#10003;' : '&#10007;';
			$class         = $is_checked_in ? 'checked-in' : 'not-checked-in';

			$value[] = "<li class=\"{$class}\">{$icon} {$text}</li>";

		}

		$value[] = '<li><a href="' . $this->get_check_in_url( $entry['id'] ) . '">Manage Check-ins</a>';

		return '<ul>' . implode( "\n", $value ) . '</ul>';
	}

	public function populate_check_in_url_field( $entry ) {

		if ( ! $this->is_applicable_form( $entry['form_id'] ) ) {
			return $entry;
		}

		$field_id           = $this->_args['url_field_id'];
		$entry[ $field_id ] = $this->get_check_in_url( $entry['id'] );
		GFAPI::update_entry_field( $entry['id'], $field_id, $entry[ $field_id ] );

		return $entry;
	}

}

# Configuration

new GW_Check_In( array(
	'form_id'           => 12,
	'name_field_id'     => 5,
	'product_field_ids' => array( 1, 3 ),
	'url_field_id'      => 4,
	'check_in_role'     => 'administrator',
	'labels'            => array(
		'check_in_title' => 'Product Check-in',
	),
) );
