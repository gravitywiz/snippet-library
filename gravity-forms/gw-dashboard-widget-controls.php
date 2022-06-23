<?php
/**
 * Gravity Wiz // Gravity Forms // Dashboard Widget Controls
 * https://gravitywiz.com/
 *
 * Plugin Name:  Gravity Forms — Dashboard Widget Controls
 * Plugin URI:   https://github.com/gravitywiz/snippet-library/blob/master/gravity-forms/gw-dashboard-widget-controls.php
 * Description:  Select which forms you would like to display in the Gravity Forms Dashboard widget.
 * Author:       Gravity Wiz
 * Version:      0.3
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

		protected $_version                    = '0.2';
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

		public function pre_init() {
			parent::pre_init();

			if ( $this->get_plugin_setting( 'filter_admin_bar_forms' ) ) {
				add_filter( 'get_user_metadata', array( $this, 'filter_recent_forms' ), 10, 5 ); // init isn't early enough here.
			}
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
			$form_ids         = $this->get_plugin_setting( 'forms' );
			$behavior         = $this->get_plugin_setting( 'behavior' );

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
		 * Filter user meta for recently accessed forms to only include the forms that are to be included (or excluded).
		 */
		public function filter_recent_forms( $meta_value, $object_id, $meta_key, $single, $meta_type ) {
			if ( $meta_key !== 'gform_recent_forms' ) {
				return $meta_value;
			}

			/* Prevent filtering while we get the most recent forms. */
			remove_filter( 'get_user_metadata', array( $this, 'filter_recent_forms' ));

			$recent_form_ids = GFFormsModel::get_recent_forms();

			/* Re-add filter. */
			add_filter( 'get_user_metadata', array( $this, 'filter_recent_forms' ), 10, 5 );

			$specified_form_ids = $this->get_plugin_setting( 'forms' );
			$behavior           = $this->get_plugin_setting( 'behavior' );

			if ( empty( $specified_form_ids ) || ! is_array( $specified_form_ids ) ) {
				return $recent_form_ids;
			}

			/* Behavior defaults to exclude. */
			if ( ! $behavior ) {
				$behavior = 'exclude';
			}

			if ( $behavior === 'include' ) {
				return array( $specified_form_ids );
			} else {
				foreach ( $recent_form_ids as $index => $recent_form_id ) {
					if ( in_array( $recent_form_id, $specified_form_ids ) ) {
						unset( $recent_form_ids[ $index ] );
					}
				}
			}

			return array( $recent_form_ids );
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
						array(
							'label'   => esc_html__( 'Admin Bar', 'gravityforms' ),
							'type'    => 'checkbox',
							'choices' => array(
								array(
									'label' => esc_html__( 'Apply to "Forms" Menu in Admin Bar', 'gravityforms' ),
									'name'  => 'filter_admin_bar_forms',
								),
							),
						),
					),
				),
			);
		}
	}

	GFAddOn::register( 'GW_Dashboard_Widget_Controls' );
}
