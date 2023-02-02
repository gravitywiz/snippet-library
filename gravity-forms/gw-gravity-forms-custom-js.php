<?php
/**
 * Gravity Wiz // Gravity Forms // Custom Javascript
 *
 * Include custom Javascript with your form.
 *
 * @version  1.6.1
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/
 *
 * Plugin Name:  Gravity Forms Custom Javascript
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Include custom Javascript with your form.
 * Author:       Gravity Wiz
 * Version:      1.6.1
 * Author URI:   http://gravitywiz.com
 *
 * Usage:
 *
 * 1. Install and activate the plugin.
 * 2. Go to the form settings.
 * 3. Copy and paste your form-specific custom Javascript in the "Custom Javascript" setting.
 *
 * Whenever this feed is set as the active feed, the submit button label will automatically be set to your custom label.
 */
class GF_Custom_JS {

	private static $instance = null;

	private $scripts = array();

	public static function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	private function __construct() {

		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if( ! class_exists( 'GFForms' ) ) {
			return;
		}

		if ( current_user_can( 'administrator' ) ) {
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_editor_script' ) );
			if ( version_compare( GFForms::$version, '2.5', '>=' ) ) {
				add_filter( 'gform_form_settings_fields', array( $this, 'add_custom_js_setting' ), 10, 2 );
			} else {
				add_filter( 'gform_form_settings', array( $this, 'add_legacy_custom_js_setting' ), 10, 2 );
			}
			add_filter( 'gform_pre_form_settings_save', array( $this, 'save_custom_js_setting' ), 10, 2 );
			add_filter( 'gform_noconflict_scripts', array( $this, 'noconflict_scripts' ) );
			add_filter( 'gform_noconflict_styles', array( $this, 'noconflict_styles' ) );
		}

		add_filter( 'gform_register_init_scripts', array( $this, 'register_init_script' ), 99 );

	}

	public function enqueue_editor_script() {

		if ( GFForms::get_page() !== 'form_settings' ) {
			return;
		}

		$editor_settings['codeEditor'] = wp_enqueue_code_editor( array( 'type' => 'text/javascript' ) );
		wp_localize_script( 'jquery', 'editor_settings', $editor_settings );

		wp_enqueue_script( 'wp-theme-plugin-editor' );
		wp_enqueue_style( 'wp-codemirror' );

	}

	public function noconflict_scripts( $scripts = array() ) {
		$scripts[] = 'code-editor';
		$scripts[] = 'jshint';
		$scripts[] = 'jsonlint';
		$scripts[] = 'wp-theme-plugin-editor';
		return $scripts;
	}

	public function noconflict_styles( $scripts = array() ) {
		$scripts[] = 'code-editor';
		$scripts[] = 'wp-codemirror';
		return $scripts;
	}

	public function add_custom_js_setting( $form_settings, $form ) {
		if ( rgget( 'subview' ) !== 'settings' ) {
			return $form_settings;
		}
		$form_settings['Custom Code'] = array(
			'title'  => esc_html__( 'Custom Code' ),
			'fields' => array(
				array(
					'name'     => 'custom_js',
					'type'     => 'editor_js',
					'label'    => __( 'Custom Javascript' ),
					'tooltip'  => gform_tooltip( 'gf_custom_js', '', true ),
					'callback' => function ( $setting ) use ( $form ) {
						return $this->render_custom_js_setting( $form );
					},
				),
			),
		);
		return $form_settings;
	}

	/**
	 * @param $setting
	 *
	 * @return mixed
	 */
	public function render_custom_js_setting( $form ) {
		$settings = $this->add_legacy_custom_js_setting( array(), $form );
		return $settings[ __( 'Custom Javascript' ) ]['custom_js'];
	}

	public function add_legacy_custom_js_setting( $settings, $form ) {

		// GF 2.5 may fire `gform_form_settings` before `save_custom_js_setting`
		$custom_js = rgar( $form, 'customJS' );
		$post_js   = esc_html( rgpost( 'custom_js' ) );
		// Always favor posted JS if it's available
		$custom_js = ( $post_js ) ? $post_js : $custom_js;

		$settings[ __( 'Custom Javascript' ) ] = array(
			'custom_js' => sprintf(
				'<tr id="custom_js_setting" class="child_setting_row">
					<td colspan="2">
						<p>%s<br>%s</p>
						<textarea id="custom_js" name="custom_js" spellcheck="false"
							style="width:100%%;height:14rem;">%s</textarea>
					</td>
				</td>
				<script>
					jQuery( document ).ready( function( $ ) {
  						wp.codeEditor.initialize( $( "#custom_js" ), editor_settings );
					} );
				</script>
				<style type="text/css">
					.CodeMirror-wrap { border: 1px solid #e1e1e1; }
				</style>',
				__( 'Include any custom Javascript that you would like to output wherever this form is rendered.' ),
				__( 'Use <code>GFFORMID</code> to automatically set the current form ID when the code is rendered.' ),
				$custom_js
			),
		);

		return $settings;
	}

	public function save_custom_js_setting( $form ) {
		$form['customJS'] = esc_html( rgpost( 'custom_js' ) );
		return $form;
	}

	public function register_init_script( $form ) {

		if ( ! $this->is_applicable_form( $form ) ) {
			return;
		}

		$allowed_entities = array(
			'&#039;' => '\'',
			'&quot;' => '"',
		);

		$script = html_entity_decode( str_replace( array_keys( $allowed_entities ), $allowed_entities, $this->get_custom_js( $form ) ) );
		$script = str_replace( 'GFFORMID', $form['id'], $script );
		$script = '( function( $ ) { ' . $script . ' } )( jQuery );';

		$slug = "gf_custom_js_{$form['id']}";

		GFFormDisplay::add_init_script( $form['id'], $slug, GFFormDisplay::ON_PAGE_RENDER, $script );

	}

	public function get_custom_js( $form ) {
		return rgar( $form, 'customJS' );
	}

	public function is_applicable_form( $form ) {
		$js = $this->get_custom_js( $form );
		return ! empty( $js );
	}

}

function gw_custom_js() {
	return GF_Custom_JS::get_instance();
}

gw_custom_js();
