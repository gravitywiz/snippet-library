/**
 * Gravity Perks // Nested Forms // Populate Parent Form ID in Child Form
 * https://gravitywiz.com/documentation/gravity-forms-nested-forms/
 */
 add_filter( 'gpnf_jquery_ui_dependencies', function( $deps ) {

    $deps[] = 'my-custom-script-handle';

    return $deps;
} );
