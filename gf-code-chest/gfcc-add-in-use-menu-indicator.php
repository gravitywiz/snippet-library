<?php
/**
 * Gravity Forms // Code Chest // Display Indicator on Menu Item if Code Chest Has Code
 * https://gravitywiz.com/gravity-forms-code-chest/
 */
add_filter( 'gform_form_settings_menu', function( $menu_items, $form_id ) {

	$form = GFAPI::get_form( $form_id );

	// Check if there is code in the JS or CSS settings using Code Chest methods
    $has_js_code = ! empty( gwiz_gf_code_chest()->get_custom_js( $form ) );
    $has_css_code = ! empty( gwiz_gf_code_chest()->get_custom_css( $form ) );

    // If there is code in either setting, append the ✔ symbol to the Code Chest menu item
    if ( $has_js_code || $has_css_code ) {
        foreach ( $menu_items as &$menu_item ) {
            if ( $menu_item['name'] === 'gf-code-chest' ) {
                $menu_item['label'] .= ' ✔';
                break;
            }
        }
    }

    return $menu_items;
}, 16, 2 );
