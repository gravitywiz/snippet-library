<?php
/**
 * Gravity Perks // Populate Anything // Add Custom Template Row for Color Picker
 * https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * 
 * Instructions Video: https://www.loom.com/share/c062a781a86242de9f1ebb92492a408c
 *
 * Adds "Color Picker" template row to the Populate Anything interface and processes its value (with Jet Sloth's Color Picker plugin).
 *
 * Plugin Name:  GP Populate Anything — Color Picker Choice Template
 * Plugin URI:   https://gravitywiz.com/documentation/gravity-forms-populate-anything/
 * Description:  Adds a custom template row and value processing for "Color Picker" in Populate Anything.
 * Author:       Gravity Wiz
 * Version:      1.0
 */
class GPPA_Color_Picker_Template {

	public function __construct() {
		add_filter( 'gppa_input_choice', array( $this, 'add_colorpicker_to_choice' ), 10, 4 );
		add_action( 'gform_editor_js', array( $this, 'add_colorpicker_choice_template' ), 1 );
	}

	public function add_colorpicker_to_choice( $choice, $field, $object, $objects ) {
		$templates = rgar( $field, 'gppa-choices-templates', array() );

		if ( rgar( $templates, 'colorPicker_color' ) ) {
			$choice['colorPicker_color'] = gp_populate_anything()->process_template( $field, 'colorPicker_color', $object, 'choices', $objects );
		}

		return $choice;
	}

	public function add_colorpicker_choice_template() {
		?>
		<script type="text/javascript">
			window.gform.addFilter( 'gppa_template_rows', function ( templateRows, field, populate ) {
				if ( populate !== 'choices' ) {
					return templateRows;
				}

				templateRows.push( {
					id: 'colorPicker_color',
					label: '<?php echo esc_js( __( 'Color Picker', 'gp-populate-anything' ) ); ?>',
					required: false,
				} );

				return templateRows;
			} );
		</script>
		<?php
	}
}

new GPPA_Color_Picker_Template();
