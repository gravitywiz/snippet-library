<?php
/**
 * Gravity Wiz // Gravity Forms // Default Form List to Active Forms
 *
 * Sets Form List view to Active Forms by Default.
 *
 * @version 0.1
 * @author  David Smith <david@gravitywiz.com>
 * @license GPL-2.0+
 * @link    https://gravitywiz.com
 *
 * Plugin Name: Gravity Forms Default Form List to Active
 * Plugin URI: http://gravitywiz.com
 * Description: Sets Form List view to Active Forms by Default.
 * Author: Gravity Wiz
 * Version: 0.1
 * Author URI: http://gravitywiz.com
 *
 */
add_action( 'init', function() {
	if ( ! class_exists( 'GFForms' ) ) {
		return;
	}
	if ( GFForms::get_page() === 'form_list' ) {

		$params = array();

		if ( ! isset( $_GET['sort'] ) ) {
			$params = array(
				'sort'    => 'id',
				'dir'     => 'desc',
				'orderby' => 'id',
				'order'   => 'desc',
			);
		}

		if ( ! isset( $_GET['filter'] ) ) {
			$params['filter'] = 'active';
		}

		if ( ! empty( $params ) ) {
			wp_redirect( add_query_arg( $params ) );
			exit;
		}
	}

} );
