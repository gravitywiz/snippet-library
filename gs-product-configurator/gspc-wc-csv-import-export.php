<?php
/**
 * Gravity Shop // Product Configurator // WooCommerce CSV Import/Export
 * https://gravitywiz.com/documentation/gs-product-configurator/
 *
 * Adds a "GSPC Product Form ID" column to WooCommerce product CSV exports and imports.
 * The exported value stores both form ID and title (e.g. "12:T-Shirt Builder"). On
 * import, the form is matched by ID first with the title as a fallback, useful when
 * importing products to a different site where form IDs may not match.
 *
 * Instructions:
 *
 * 1. Install this snippet by following the instructions here:
 *    https://gravitywiz.com/documentation/how-do-i-install-a-snippet/
 */
class GSPC_WC_CSV_Import_Export {

	private static $instance = null;

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		if ( ! function_exists( 'gs_product_configurator' ) ) {
			return;
		}

		// Export columns
		add_filter( 'woocommerce_product_export_column_names', array( $this, 'add_column' ) );
		add_filter( 'woocommerce_product_export_product_default_columns', array( $this, 'add_column' ) );
		add_filter( 'woocommerce_product_export_product_column_gspc_product_form_id', array( $this, 'export_value' ), 10, 2 );

		// Import columns
		add_filter( 'woocommerce_csv_product_import_mapping_options', array( $this, 'add_column' ) );
		add_filter( 'woocommerce_csv_product_import_mapping_default_columns', array( $this, 'default_mapping' ) );
		add_filter( 'woocommerce_product_import_inserted_product_object', array( $this, 'import_value' ), 10, 2 );
	}

	public function add_column( $columns ) {
		$columns['gspc_product_form_id'] = 'GSPC Product Form ID';
		return $columns;
	}

	public function default_mapping( $mappings ) {
		$mappings['GSPC Product Form ID'] = 'gspc_product_form_id';
		$mappings['gspc_product_form_id'] = 'gspc_product_form_id';
		return $mappings;
	}

	public function export_value( $value, $product ) {
		$form_id = gspc_get_product_form_id( $product );
		if ( ! $form_id ) {
			return '';
		}

		$form  = GFAPI::get_form( $form_id );
		$title = $form ? rgar( $form, 'title', '' ) : '';

		return $title !== '' ? $form_id . ':' . $title : (string) $form_id;
	}

	public function import_value( $product, $data ) {
		if ( ! isset( $data['gspc_product_form_id'] ) ) {
			return $product;
		}

		$wc_admin = gs_product_configurator()->wc_admin;
		$raw      = trim( (string) $data['gspc_product_form_id'] );

		if ( $raw === '' ) {
			$wc_admin->update_product_form_attachment( $product, 0 );
			return $product;
		}

		$form_id = 0;
		$title   = '';

		if ( preg_match( '/^(\d+):(.+)$/', $raw, $m ) ) {
			$form_id = (int) $m[1];
			$title   = trim( $m[2] );
		} elseif ( ctype_digit( $raw ) ) {
			$form_id = (int) $raw;
		} else {
			$title = $raw;
		}

		$resolved_id = $this->resolve_form_id( $form_id, $title );
		if ( ! $resolved_id ) {
			return $product;
		}
		$wc_admin->update_product_form_attachment( $product, $resolved_id );

		return $product;
	}

	private function resolve_form_id( $form_id, $title ) {
		if ( $form_id && GFAPI::get_form( $form_id ) ) {
			return $form_id;
		}

		if ( $title === '' ) {
			return 0;
		}

		$matched_id = 0;
		foreach ( GFAPI::get_forms() as $form ) {
			if ( strcasecmp( (string) rgar( $form, 'title' ), $title ) !== 0 ) {
				continue;
			}

			if ( $matched_id ) {
				return 0;
			}

			$matched_id = (int) rgar( $form, 'id' );
		}

		return $matched_id;
	}

}

function gspc_wc_csv_import_export() {
	return GSPC_WC_CSV_Import_Export::get_instance();
}

gspc_wc_csv_import_export();
