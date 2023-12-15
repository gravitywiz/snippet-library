<?php
/**
 * Plugin Name:  GPLC + GPPA Integration
 * Plugin URI:   http://gravitywiz.com/documentation/gravity-forms-limit-choices/
 * Description:  Provides the ability to set limits on dynamic choices populated by Populate Anything.
 * Author:       Gravity Wiz
 * Version:      0.2
 * Author URI:   http://gravitywiz.com
 */
class GPLC_GPPA_Integration {

	private static $instance;

	public static function get_instance() {

		if ( ! self::$instance ) {
			self::$instance = new GPLC_GPPA_Integration();
		}

		return self::$instance;
	}

	private function __construct() {

		// do version check in the init to make sure if GF is going to be loaded, it is already loaded
		add_action( 'init', array( $this, 'init' ) );

	}

	public function init() {

		if ( is_callable( 'gp_limit_choices' ) ) {

			add_filter( 'gform_pre_render', array( $this, 'enable_choice_limits' ), 9 );
			add_filter( 'gform_pre_process', array( $this, 'enable_choice_limits' ) );
			add_filter( 'gppa_input_choice', array( $this, 'set_choice_limit' ), 10, 4 );
			add_action( 'admin_print_footer_scripts', array( $this, 'output_editor_script' ) );

		}

	}

	public function enable_choice_limits( $form ) {

		foreach ( $form['fields'] as $field ) {
			if ( $this->are_limits_enabled( $field ) ) {
				$field->{gp_limit_choices()->key( 'enableLimits' )} = true;
			}
		}

		return $form;
	}

	public function set_choice_limit( $choice, $field, $object, $objects ) {
		if ( $this->are_limits_enabled( $field ) ) {
			$choice['limit'] = gp_populate_anything()->process_template( $field, 'limit', $object, 'choices', $objects );
		}
		return $choice;
	}

	public function apply_choice_limits( $choices, $field ) {
		if ( $this->are_limits_enabled( $field ) ) {
			$choices = gp_limit_choices()->apply_choice_limits( $choices, $field, GFAPI::get_form( $field->formId ) );
		}
		return $choices;
	}

	public function output_editor_script() {
		if ( ! is_callable( 'GFCommon::is_form_editor' ) || ! GFCommon::is_form_editor() ) {
			return;
		}
		?>
		<script>
			window.gform.addFilter( 'gppa_template_rows', function ( templateRows, field, populate ) {

				if ( populate !== 'choices' ) {
					return templateRows
				}

				templateRows.push( {
					id: 'limit',
					label: 'Limit',
				} );

				return templateRows;
			} );
		</script>
		<?php
	}

	public function are_limits_enabled( $field ) {
		return rgars( $field, 'gppa-choices-templates/limit' );
	}

}

function gplc_gppa_integration() {
	return GPLC_GPPA_Integration::get_instance();
}

gplc_gppa_integration();
