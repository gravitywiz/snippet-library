<?php
/**
 * Gravity Perks // GP Address Autocomplete // Always Use Short Name for State/Province/Region
 * https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 *
 * Forces GP Address Autocomplete to use the short name (e.g. "TO" instead of "Città Metropolitana di Torino")
 * for state, province, or region components on ALL forms and ALL address fields.
 *
 * This snippet works automatically with any International Address field powered by GP Address Autocomplete.
 * No configuration needed.
 *
 * Plugin Name:  GP Address Autocomplete - Always Use Short Name
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-address-autocomplete/
 * Description:  Forces GP Address Autocomplete to use the short name (e.g. "TO" instead of "Città Metropolitana di Torino") for state, province, or region components.
 * Author:       Gravity Wiz
 * Version:      1.0
 * Author URI:   http://gravitywiz.com
 */
class GP_Address_Autocomplete_Always_Short_Name {

	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );
		add_action( 'gform_register_init_scripts', array( $this, 'add_init_script' ), 10, 2 );
	}

	public function load_form_script( $form, $is_ajax_enabled ) {
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

				/**
				 * Automatically replace long state/province/region names with their short code equivalents
				 * for ALL GP Address Autocomplete fields on ALL forms.
				 */
				window.gpaaAlwaysUseShortName = function() {
					// Apply the filter to ALL address autocomplete fields
					gform.addFilter('gpaa_values', function(values, place) {
						if (!place || !place.address_components) {
							return values;
						}

						// Find the address component that matches the current state/province long name
						for (var i = 0; i < place.address_components.length; i++) {
							var component = place.address_components[i];
							// If the component's long name matches the current state/province value,
							// replace it with the short name
							if (component.long_name === values.stateProvince) {
								values.stateProvince = component.short_name;
								break;
							}
						}
						return values;
					});
				};

				// Initialize after GP Address Autocomplete is ready
				if (typeof gform !== 'undefined' && typeof gform.addFilter !== 'undefined') {
					window.gpaaAlwaysUseShortName();
				} else {
					// Wait for Gravity Forms core to be fully loaded
					$(document).on('gform_post_render', function() {
						if (typeof gform !== 'undefined' && typeof gform.addFilter !== 'undefined') {
							window.gpaaAlwaysUseShortName();
						}
					});
				}

			} )( jQuery );
		</script>
		<?php
	}

	public function add_init_script( $form ) {
		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$script = 'if (typeof window.gpaaAlwaysUseShortName === "function") { window.gpaaAlwaysUseShortName(); }';
		$slug   = 'gpaa_always_short_name';

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );
	}

	public function is_applicable_form( $form ) {
		// Always return true to apply to ALL forms
		return true;
	}
}

// Initialize the class for ALL forms (no arguments needed)
new GP_Address_Autocomplete_Always_Short_Name();
