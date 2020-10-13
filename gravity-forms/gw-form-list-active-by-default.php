<?php
/**
 * Gravity Wiz // Gravity Forms // Default Form List to Active Forms
 * http://gravitywiz.com/
 */
add_action( 'init', function() {

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
