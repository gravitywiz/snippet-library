<?php
/**
 * Gravity Wiz // Gravity Forms // Dashboard Widget Forms
 * https://gravitywiz.com/
 *
 * Display a Gravity Forms form as a dashboard widget.
 *
 * Plugin Name:  Gravity Forms — Dashboard Widget Forms
 * Plugin URI:   https://gravitywiz.com/
 * Description:  Add a Gravity Form as a dashboard widget.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */
class GF_Dashboard_Widget_Forms {

	public function __construct( $widget_args, $form_args = array() ) {

		$this->_widget_args = wp_parse_args( $widget_args, array(
			'id'          => false,
			'form_id'     => false,
			'widget_name' => false,
		) );

		$this->_form_args = wp_parse_args( $form_args, array(
			'display_title'      => false,
			'display_decription' => false,
			'display_inactive'   => false,
			'field_values'       => null,
			'ajax'               => true,
		) );

		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );

	}

	public function add_dashboard_widget() {
		wp_add_dashboard_widget( $this->_widget_args['id'], $this->_widget_args['widget_name'], array( $this, 'display_widget_content' ) );
	}

	public function display_widget_content() {
		gravity_form( $this->_widget_args['form_id'], $this->_form_args['display_title'], $this->_form_args['display_description'], $this->_form_args['display_inactive'], $this->_form_args['field_values'], $this->_form_args['ajax'] );
	}

}

if ( class_exists( 'GF_Dashboard_Widget_Forms' ) ) {
	new GF_Dashboard_Widget_Forms( array(
		'id'          => 'my-gf-dashboard-form',
		'widget_name' => 'My Gravity Forms Dashboard Form',
		'form_id'     => 123,
	) );
}
