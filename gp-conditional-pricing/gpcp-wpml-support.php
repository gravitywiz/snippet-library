<?php
/**
 * Gravity Perks // Conditional Pricing // Add Support for Choice-based Rules with WPML Translations
 * https://gravitywiz.com/documentation/gravity-forms-conditional-pricing/
 *
 * Experimental Snippet 🧪
 */
add_filter( 'gpcp_pricing_logic', function( $pricing_logic, $form ) {

	if ( isset( $GLOBALS['wpml_gfml_tm_api'] ) ) {
		/**
		 * GPCP fetches the pricing logic at several points during the page load. We need to translate our pricing
		 * rules *before* WPML has translated the form. Otherwise, choice-based conditions will not return a
		 * translation match since the original choice has already been translated. This is relevant to the
		 * Gravity_Forms_Multilingual::translate_conditional_logic() call below.
		 */
		static $wpml_pricing_logic;
		if ( isset( $wpml_pricing_logic[ $form['id'] ] ) ) {
			return $wpml_pricing_logic[ $form['id'] ];
		}
		foreach ( $pricing_logic as &$field_pricing_logic ) {
			/**
			 * WPML's translate_conditional_logic() method only translates the "fields" and "notifications" section
			 * and it needs the "fields" section to look up fields for conditional logic so... we use "notifications".
			 */
			$form['notifications'] = $field_pricing_logic;
			$form                  = $GLOBALS['wpml_gfml_tm_api']->translate_conditional_logic( $form );
			$field_pricing_logic   = $form['notifications'];
		}
		$wpml_pricing_logic[ $form['id'] ] = $pricing_logic;
	}

	return $pricing_logic;
}, 10, 2 );
