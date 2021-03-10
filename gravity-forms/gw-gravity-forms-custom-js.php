<?php
/**
 * Gravity Wiz // Gravity Forms // Custom Javascript
 *
 * Include custom Javascript with your form.
 *
 * @version  1.5
 * @author   David Smith <david@gravitywiz.com>
 * @license  GPL-2.0+
 * @link     http://gravitywiz.com/
 *
 * Plugin Name:  Gravity Forms Custom Javascript
 * Plugin URI:   http://gravitywiz.com/
 * Description:  Include custom Javascript with your form.
 * Author:       Gravity Wiz
 * Version:      1.5
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
			add_filter( 'gform_form_settings', array( $this, 'add_custom_js_setting' ), 10, 2 );
			add_filter( 'gform_pre_form_settings_save', array( $this, 'save_custom_js_setting' ), 10, 2 );
			add_filter( 'gform_noconflict_scripts', array( $this, 'noconflict_scripts' ) );
			add_filter( 'gform_noconflict_styles', array( $this, 'noconflict_styles' ) );
		}

		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );

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

	public function add_custom_js_setting( $settings, $form ) {

		// GF 2.5 may fire `gform_form_settings` before `save_custom_js_setting`
		$custom_js = rgar( $form, 'customJS' );
		$post_js   = esc_html( rgpost( 'customJS' ) );
		if ( $post_js && $post_js !== $custom_js ) {
			$custom_js = $post_js;
		}
		$settings[ __( 'Custom Javascript' ) ] = array(
			'custom_js' => sprintf(
				'<tr id="custom_js_setting" class="child_setting_row">
					<td colspan="2">
						<p style="margin-top:-1rem;">%s</p>
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
				$custom_js
			),
		);

		return $settings;
	}

	public function save_custom_js_setting( $form ) {
		$form['customJS'] = esc_html( rgpost( 'custom_js' ) );
		return $form;
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			$this->queue_script( $form['id'], $this->get_custom_js( $form ) );
			add_action( 'wp_footer', array( $this, 'output_script' ), 99 );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ), 99 );
		}

		return $form;
	}

	public function queue_script( $form_id, $script ) {
		$this->scripts[ $form_id ] = $script;
	}

	public function get_script_queue() {
		return $this->scripts;
	}

	public function output_script() {

		$allowed_entities = array(
			'&#039;' => '\'',
			'&quot;' => '"',
		);

		?>

		<script type="text/javascript">

			( function( $ ) {

				$( document ).bind( 'gform_post_render', function() {

					<?php foreach( $this->get_script_queue() as $script ):
					echo html_entity_decode( str_replace( array_keys( $allowed_entities ), $allowed_entities, $script ) );
				endforeach; ?>

				} );

			} )( jQuery );

		</script>

		<?php
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
