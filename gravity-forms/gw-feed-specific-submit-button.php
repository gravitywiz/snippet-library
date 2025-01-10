<?php
/**
 * Gravity Wiz // Gravity Forms // Feed-specific Submit Button
 * https://gravitywiz.com/gravity-forms-feed-specific-submit-button/
 *
 * Instruction Video: https://www.loom.com/share/c75fcfa9e9d2453f839dd483b25147a8
 *
 * Change the label of the submit button depending on which payment feed will be used to process the order. This can help
 * set user expectation when conditionally redirecting to Stripe Checkout. Currently, this plugin is limited to Stripe.
 *
 * Usage:
 *
 * 1. Install and activate the plugin.
 * 2. Create a payment feed.
 * 3. Specify the submit button label if this feed is activated: https://gwiz.io/2MblCb0
 *
 * Whenever this feed is set as the active feed, the submit button label will automatically be set to your custom label.
 *
 * Plugin Name:  Gravity Forms - Feed-specific Submit Button
 * Plugin URI:   https://gravitywiz.com/gravity-forms-feed-specific-submit-button/
 * Description:  Change the label of the submit button depending on which payment feed will be used to process the order.
 * Author:       Gravity Wiz.
 * Version:      1.3
 * Author URI:   https://gravitywiz.com
 */
class GW_Feed_Specific_Submit_Button {

	private static $instance = null;

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

		add_filter( 'gform_gravityformsstripe_feed_settings_fields', array( $this, 'add_submit_button_setting' ) );
		add_filter( 'gform_gravityformsstripe_frontend_feed', array( $this, 'add_submit_button_setting_to_frontend_feed' ), 10, 3 );
		add_filter( 'gform_pre_render', array( $this, 'load_form_script' ), 10, 2 );

	}

	public function add_submit_button_setting( $settings ) {

		$submit_button_setting = array(
			'name'    => 'submitButtonLabel',
			'label'   => esc_html__( 'Submit Button Label' ),
			'type'    => 'text',
			'tooltip' => '<h6>' . esc_html__( 'Submit Button Label' ) . '</h6>' . esc_html__( 'Specify a custom label for the submit button that will be used if this feed will be used to process the payment.' ),
		);

		$stripe   = GFStripe::get_instance();
		$settings = $stripe->add_field_after( 'conditionalLogic', $submit_button_setting, $settings );

		return $settings;
	}

	public function add_submit_button_setting_to_frontend_feed( $feed, $form, $raw_feed ) {
		$feed['submitButtonLabel'] = rgars( $raw_feed, 'meta/submitButtonLabel', rgars( $form, 'button/text' ) );
		return $feed;
	}

	public function load_form_script( $form, $is_ajax_enabled ) {

		if ( $this->is_applicable_form( $form ) && ! has_action( 'wp_footer', array( $this, 'output_script' ) ) ) {
			add_action( 'wp_footer', array( $this, 'output_script' ), 99 );
			add_action( 'gform_preview_footer', array( $this, 'output_script' ), 99 );
		}

		return $form;
	}

	public function output_script() {
		?>

		<script type="text/javascript">

			( function( $ ) {

				gform.addAction( 'gform_frontend_feeds_evaluated', function( feeds, formId ) {

					var $submitButton = $( '#gform_submit_button_{0}'.gformFormat( formId ) ),
						originalLabel = $submitButton.data( 'default-label' );

					if( originalLabel ) {
						$submitButton.val( originalLabel );
					}

					for( var i = 0; i < feeds.length; i++ ) {
						if( feeds[i].isActivated ) {
							$submitButton
								.data( 'default-label', $submitButton.val() )
								.val( feeds[i].submitButtonLabel );
							break;
						}
					}

				}, 11 );

			} )( jQuery );

		</script>

		<?php
	}

	public function is_applicable_form( $form ) {
		/* @var GFStripe $stripe */
		$stripe = GFStripe::get_instance();
		return $stripe->has_frontend_feeds( $form );
	}

}

function gw_feed_specific_submit_button() {
	return GW_Feed_Specific_Submit_Button::get_instance();
}

gw_feed_specific_submit_button();
