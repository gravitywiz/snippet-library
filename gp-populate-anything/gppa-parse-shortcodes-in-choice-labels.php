<?php
/**
 * Gravity Perks // Populate Anything // Parse Shortcodes in Choice Labels
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 */
add_filter( 'gform_field_content', 'do_shortcode' );
add_filter( 'gppa_hydrate_input_html', 'do_shortcode' );
