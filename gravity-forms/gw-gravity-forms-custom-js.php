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
class GF_Custom_JS extends GFAddOn {

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

		add_filter( 'gform_form_settings_menu', array( $this, 'add_form_settings_menu_item' ), 10, 2 );

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
		$subview = rgget( 'subview' );
		if ( $subview && rgget( 'subview' ) !== 'settings' ) {
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

	public function add_form_settings_menu_item( $tabs, $form_id ) {

		$tabs[] = array(
			'name'           => __( 'Custom Code' ),
			'label'          => __( 'Custom Code' ),
			'query'          => array( 'fid' => null ),
			'capabilities'   => array( 'administrator' ),
			'icon'           => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="60.42 118.04 391.16 275.95"> <g>  <path d="m354.3 358.91 97.281-102.91-97.281-102.91-22.016 21.504 76.801 81.406-76.801 81.406z"></path>  <path d="m157.7 153.09-97.281 102.91 97.281 102.91 22.016-21.504-76.801-81.406 76.801-81.406z"></path>  <path d="m220.34 389.4 40.973-271.36 30.375 4.5859-40.973 271.36z"></path> </g></svg>',
		);

		return $tabs;
	}

	public function form_settings_fields( $form ) {
		return array(
			// Save and Continue settings as copied from gravity-forms/form_settings.php
			array(
				'title'  => __( 'General Settings', 'gp-advanced-save-and-continue' ),
				'fields' => array(
					array(
						'name'          => 'save_and_continue_enabled',
						'type'          => 'toggle',
						'tooltip'       => $this->generate_settings_tooltip(
							__( 'Save and Continue Later', 'gp-advanced-save-and-continue' ),
							__( 'Enable this setting to allow users to save their form progress and continue it at a later time.', 'gp-advanced-save-and-continue' )
						),
						'label'         => __( 'Enable Save and Continue', 'gp-advanced-save-and-continue' ),
						// this value should map to the standard save and continue setting.
						'default_value' => rgars( $form, 'save/enabled', false ),
					),
					array(
						'name'       => 'save_and_continue_warning_html',
						'type'       => 'html',
						'html'       => sprintf(
							'<div class="alert warning"><p>%s</p></div>',
							__( 'This feature stores potentially private and sensitive data on this server and protects it with a unique link which is displayed to the user on the page in plain, unencrypted text. The link is similar to a password so it&#039;s strongly advisable to ensure that the page enforces a secure connection (HTTPS) before activating this setting.</p><p>When this setting is activated two confirmations and one notification are automatically generated and can be modified in their respective editors. When this setting is deactivated the confirmations and the notification will be deleted automatically and any modifications will be lost.', 'gp-advanced-save-and-continue' )
						),
						'dependency' => array(
							'live'   => true,
							'fields' => array(
								array(
									'field' => 'save_and_continue_enabled',
									'value' => true,
								),
							),
						),
					),
					array(
						'name'          => 'save_and_continue_button_text',
						'type'          => 'text',
						'default_value' => 'Save and Continue Later',
						'label'         => __( 'Link Text', 'gp-advanced-save-and-continue' ),
						'tooltip'       => $this->generate_settings_tooltip(
							__( 'Save and Continue Button Text', 'gp-advanced-save-and-continue' ),
							__( 'Customize the text displayed in the "Save and "Continue" button.', 'gp-advanced-save-and-continue' )
						),
						'dependency'    => array(
							'live'   => true,
							'fields' => array(
								array(
									'field' => 'save_and_continue_enabled',
									'value' => true,
								),
							),
						),
					),
				),
			),
			array(
				'title'      => __( 'Advanced Settings', 'gp-advanced-save-and-continue' ),
				'dependency' => array(
					'live'   => true,
					'fields' => array(
						array(
							'field' => 'save_and_continue_enabled',
							'value' => true,
						),
					),
				),
				'fields'     => array(
					array(
						'name'          => 'auto_save_and_load_enabled',
						'type'          => 'toggle',
						'label'         => __( 'Enable Auto Save and Load', 'gp-advanced-save-and-continue' ),
						'tooltip'       => $this->generate_settings_tooltip(
							__( 'Enable Auto Save and Load', 'gp-advanced-save-and-continue' ),
							__( 'For more granular control, this setting can be filtered to enable/disable <a href="https://gravitywiz.com/documentation/gpasc_should_auto_save/" target="blank" rel="noopener noreferrer">auto-save</a> or <a href="https://gravitywiz.com/documentation/gpasc_should_auto_load/" target="blank" rel="noopener noreferrer">auto-load</a> independently.', 'gp-advanced-save-and-continue' )
						),
						'default_value' => false,
					),
					array(
						'name'          => 'draft_management_enabled',
						'type'          => 'toggle',
						'label'         => __( 'Enable Draft Management', 'gp-advanced-save-and-continue' ),
						'tooltip'       => $this->generate_settings_tooltip(
							__( 'Enable Draft Management', 'gp-advanced-save-and-continue' ),
							__( 'Enable this setting to allow users to manage their own drafts. Additional configuration required in the "Draft Management Settings" below.', 'gp-advanced-save-and-continue' )
						),
						'default_value' => false,
					),
				),
			),
			array(
				'title'      => __( 'Auto Save and Load Settings', 'gp-advanced-save-and-continue' ),
				'fields'     => array(
					// ----------------------------
					// The "Resuming Draft Message" is hardcoded for now and will be included as a setting in a future release.
					// ----------------------------
					array(
						'label'   => __( 'Visitor Prompt', 'gp-advanced-save-and-continue' ),
						'type'    => 'html',
						'tooltip' => $this->generate_settings_tooltip(
							__( 'Visitor Prompt', 'gp-advanced-save-and-continue' ),
							__( 'Unauthenticated users will be prompted via a modal to confirm if their progress should be saved automatically. The following settings control the content of that modal.', 'gp-advanced-save-and-continue' )
						),
						'fields'  => array(
							array(
								'label'         => __( 'Prompt Title', 'gp-advanced-save-and-continue' ),
								'type'          => 'text',
								'default_value' => $this->settings->default_visitor_prompt_title,
								'name'          => 'visitor_prompt_title',
								'tooltip'       => $this->generate_settings_tooltip(
									__( 'Visitor Prompt: Title', 'gp-advanced-save-and-continue' ),
									__( 'Customize the prompt title displayed to visitors.', 'gp-advanced-save-and-continue' )
								),
							),
							array(
								'label'         => __( 'Prompt Description', 'gp-advanced-save-and-continue' ),
								'type'          => 'textarea',
								'default_value' => $this->settings->default_visitor_prompt_description,
								'name'          => 'visitor_prompt_description',
								'tooltip'       => $this->generate_settings_tooltip(
									__( 'Visitor Prompt: Description', 'gp-advanced-save-and-continue' ),
									__( 'Customize the prompt description displayed to visitors.', 'gp-advanced-save-and-continue' )
								),
							),
							array(
								'label'         => __( 'Accept Button Label', 'gp-advanced-save-and-continue' ),
								'type'          => 'text',
								'default_value' => $this->settings->default_visitor_prompt_accept_button_label,
								'name'          => 'visitor_prompt_accept_button_label',
								'tooltip'       => $this->generate_settings_tooltip(
									__( 'Visitor Prompt: Accept Button Label', 'gp-advanced-save-and-continue' ),
									__( 'Customize the button label displayed to visitors to accept automatically saving their progress.', 'gp-advanced-save-and-continue' )
								),
							),
							array(
								'label'         => __( 'Decline Button Label', 'gp-advanced-save-and-continue' ),
								'type'          => 'text',
								'default_value' => $this->settings->default_visitor_prompt_decline_button_label,
								'name'          => 'visitor_prompt_decline_button_label',
								'tooltip'       => $this->generate_settings_tooltip(
									__( 'Visitor Prompt: Accept Button Label', 'gp-advanced-save-and-continue' ),
									__( 'Customize the button label displayed to visitors to decline automatically saving their progress.', 'gp-advanced-save-and-continue' )
								),
							),
						),
					),
					array(
						'label'         => __( 'Hide Save and Continue Link', 'gp-advanced-save-and-continue' ),
						'name'          => 'hide_save_and_continue_link',
						'default_value' => '0',
						'type'          => 'toggle',
						'tooltip'       => $this->generate_settings_tooltip(
							__( 'Save and Continue Link', 'gp-advanced-save-and-continue' ),
							__( 'Enable this setting to hide the Save and Continue link displayed in the form footer. This allows you to rely exclusively on automatic save-and-continue.', 'gp-advanced-save-and-continue' )
						),
					),
					array(
						'name'          => 'inline_save_and_continue_confirmation_enabled',
						'type'          => 'toggle',
						'label'         => __( 'Display Save and Continue Confirmation Inline', 'gp-advanced-save-and-continue' ),
						'default_value' => '0',
						'tooltip'       => $this->generate_settings_tooltip(
							__( 'Inline Confirmation', 'gp-advanced-save-and-continue' ),
							__( 'Enable this setting to display the Save and Continue confirmation inline rather than on a new page.', 'gp-advanced-save-and-continue' )
						),
						'dependency'    => array(
							'live'   => true,
							'fields' => array(
								array(
									'field' => 'save_and_continue_enabled',
									'value' => true,
								),
							),
						),
					),
				),
				'dependency' => array(
					'live'   => true,
					'fields' => array(
						array(
							'field' => 'auto_save_and_load_enabled',
							'value' => true,
						),
						array(
							'field' => 'save_and_continue_enabled',
							'value' => true,
						),
					),
				),
			),
			array(
				'title'      => __( 'Draft Management Settings', 'gp-advanced-save-and-continue' ),
				'fields'     => array(
					array(
						'label'         => __( 'Display Available Drafts Above Form', 'gp-advanced-save-and-continue' ),
						'name'          => 'display_available_drafts_above_form',
						'default_value' => true,
						'type'          => 'toggle',
						'tooltip'       => $this->generate_settings_tooltip(
							__( 'Display Drafts Above Form', 'gp-advanced-save-and-continue' ),
							__( 'Display a list of a user\'s Save and Continue drafts above the form. If there are no drafts, nothing will be displayed.', 'gp-advanced-save-and-continue' )
						),
					),
					array(
						'label'   => __( 'Shortcode', 'gp-advanced-save-and-continue' ),
						'name'    => 'copy_short_code',
						'type'    => 'html',
						'html'    => $this->generate_shortcode_copier_html( $form ),
						'tooltip' => $this->generate_settings_tooltip(
							__( 'Display Drafts Above Form', 'gp-advanced-save-and-continue' ),
							__( 'Display a list of the current user\'s Save and Continue drafts above the form. If there are no drafts, nothing will be displayed.', 'gp-advanced-save-and-continue' )
						),
					),
				),
				'dependency' => array(
					'live'   => true,
					'fields' => array(
						array(
							'field' => 'draft_management_enabled',
							'value' => true,
						),
						array(
							'field' => 'save_and_continue_enabled',
							'value' => true,
						),
					),
				),
			),
		);
	}

}

GFAddOn::register( 'GF_Custom_JS' );

function gw_custom_js() {
	return GF_Custom_JS::get_instance();
}

gw_custom_js();
