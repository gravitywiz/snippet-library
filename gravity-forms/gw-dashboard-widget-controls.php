<?php
/**
 * Gravity Wiz // Gravity Forms // Dashboard Widget Controls
 * https://gravitywiz.com/
 *
 * Plugin Name:  Gravity Forms — Dashboard Widget Controls
 * Plugin URI:   https://github.com/gravitywiz/snippet-library/blob/master/gravity-forms/gw-dashboard-widget-controls.php
 * Description:  Select which forms you would like to display in the Gravity Forms Dashboard widget.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   https://gravitywiz.com
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'gform_loaded', 'gw_dashboard_widget_controls_bootstrap', 5 );

function gw_dashboard_widget_controls_bootstrap() {
	if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
		return;
	}

	class GW_Dashboard_Widget_Controls extends GFAddOn {

		protected $_version                    = '0.1';
		protected $_min_gravityforms_version   = '2.4';
		protected $_slug                       = 'gw-dashboard-widget-controls';
		protected $_path                       = 'gw-dashboard-widget-controls.php';
		protected $_full_path                  = __FILE__;
		protected $_url                        = 'https://github.com/gravitywiz/snippet-library/blob/master/gravity-forms/gw-dashboard-widget-controls.php';
		protected $_title                      = 'Dashboard Widget Controls';
		protected $_short_title                = 'Dashboard Widget';
		protected $_capabilities_settings_page = 'gravityforms_edit_settings';

		private static $_instance = null;

		public static function get_instance() {
			if ( self::$_instance == null ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

		public function init_admin() {
			parent::init_admin();
			add_filter( 'gform_form_summary', array( $this, 'manage_dashboard_forms' ) );
		}

		/**
		 * Handles including/excluding forms from the widget.
		 *
		 * @param $form_summary
		 *
		 * @return array
		 */
		public function manage_dashboard_forms( $form_summary ) {
			$form_ids = $this->get_plugin_setting( 'forms' );
			$behavior = $this->get_plugin_setting( 'behavior' );

			if ( empty( $form_ids ) || ! is_array( $form_ids ) ) {
				return $form_summary;
			}

			/* Behavior defaults to exclude. */
			if ( ! $behavior ) {
				$behavior = 'exclude';
			}

			if ( $behavior === 'include' ) {
				foreach ( $form_summary as &$item ) {
					if ( ! in_array( $item['id'], $form_ids ) ) {
						$item = null;
					}
				}
			} else {
				foreach ( $form_summary as &$item ) {
					if ( in_array( $item['id'], $form_ids ) ) {
						$item = null;
					}
				}
			}

			return array_filter( $form_summary );
		}

		/**
		 * Configures the settings which should be rendered on the add-on settings tab.
		 *
		 * @return array
		 */
		public function plugin_settings_fields() {
			$form_choices = array_map( function ( $form ) {
				return array(
					'label' => $form['title'],
					'value' => $form['id'],
				);
			}, GFAPI::get_forms( true, false, 'title' ) );

			return array(
				array(
					'title'  => esc_html__( 'Dashboard Widget', 'gravityforms' ),
					'fields' => array(
						array(
							'name'          => 'behavior',
							'label'         => esc_html__( 'Behavior', 'gravityforms' ),
							'type'          => 'radio',
							'default_value' => 'exclude',
							'choices'       => array(
								array(
									'label'   => esc_html__( 'Exclude', 'gravityforms' ),
									'value'   => 'exclude',
									'tooltip' => esc_html__( 'Include all forms by default and exclude the selected forms.', 'gravityforms' ),
								),
								array(
									'label'   => esc_html__( 'Include', 'gravityforms' ),
									'value'   => 'include',
									'tooltip' => esc_html__( 'Exclude all forms by default and include the selected forms.', 'gravityforms' ),
								),
							),
						),
						array(
							'name'        => 'forms[]',
							'description' => esc_html__( 'Choose the forms you wish to exclude/include from the Dashboard Widget. Forms without entries will never be included.', 'gravityforms' ),
							'label'       => esc_html__( 'Forms', 'gravityforms' ),
							'type'        => 'select',
							'enhanced_ui' => true,
							'multiple'    => true,
							'choices'     => $form_choices,
						),
					),
				),
			);
		}
	}

	GFAddOn::register( 'GW_Dashboard_Widget_Controls' );
}
