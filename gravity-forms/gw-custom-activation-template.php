<?php
/**
* Gravity Forms Custom Activation Template
* http://gravitywiz.com/customizing-gravity-forms-user-registration-activation-page
*/
add_action( 'wp', 'custom_maybe_activate_user', 9 );
function custom_maybe_activate_user() {

	$template_path    = STYLESHEETPATH . '/gfur-activate-template/activate.php';
	$is_activate_page = isset( $_GET['page'] ) && $_GET['page'] === 'gf_activation';
	$is_activate_page = $is_activate_page || isset( $_GET['gfur_activation'] ); // WP 5.5 Compatibility

	if ( ! file_exists( $template_path ) || ! $is_activate_page ) {
		return;
	}

	require_once( $template_path );

	exit();
}
